<?php

namespace App\Http\Controllers\Api;

use App\Models\FoundItem;
use App\Models\LostItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FoundItemResource;
use App\Http\Resources\LostItemResource;
use App\Http\Requests\LostItem\StoreLostItemRequest;
use App\Http\Requests\LostItem\UpdateLostItemRequest;
use App\Traits\HandlesUploads;

class LostItemController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function index(Request $request)
    {
        $user = $request->user();

        $query = LostItem::with(['user', 'category'])->latest();

        if ($user->role === 'user') {
            $query->where('user_id', $user->id);
        }

        $lostItems = $query->get();

        return $this->successResponse(
            'Lost items retrieved successfully.',
            LostItemResource::collection($lostItems)
        );
    }

    public function store(StoreLostItemRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), 'lost-items');
        }

        $data['user_id'] = $request->user()->id;
        $data['status'] = 'pending';

        $lostItem = LostItem::create($data);
        $lostItem->load(['user', 'category']);

        return $this->successResponse(
            'Lost item created successfully.',
            new LostItemResource($lostItem),
            201
        );
    }

    public function show(Request $request, LostItem $lostItem)
    {
        $user = $request->user();

        if ($user->role === 'user' && $lostItem->user_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to view this lost item.',
                null,
                403
            );
        }

        $lostItem->load(['user', 'category']);

        return $this->successResponse(
            'Lost item retrieved successfully.',
            new LostItemResource($lostItem)
        );
    }

    public function matches(Request $request, LostItem $lostItem)
    {
        $user = $request->user();

        if ($user->role === 'user' && $lostItem->user_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to view matches for this lost item.',
                null,
                403
            );
        }

        $lostItem->load('category');

        $max = (int) $request->query('limit', 10);
        $max = max(1, min($max, 25));

        $found = FoundItem::query()
            ->with(['category', 'staff'])
            ->whereIn('status', ['available', 'under_review'])
            ->when($lostItem->category_id, fn ($q) => $q->where('category_id', $lostItem->category_id))
            ->latest()
            ->limit(200)
            ->get();

        $lostName = mb_strtolower((string) $lostItem->item_name);
        $lostColor = mb_strtolower((string) ($lostItem->color ?? ''));

        $scored = $found->map(function (FoundItem $fi) use ($request, $lostItem, $lostName, $lostColor) {
            $score = 0;

            if ($lostItem->category_id && $fi->category_id === $lostItem->category_id) {
                $score += 50;
            }

            if ($lostColor !== '' && $fi->color) {
                $fiColor = mb_strtolower((string) $fi->color);
                if ($fiColor === $lostColor) {
                    $score += 20;
                } elseif (str_contains($fiColor, $lostColor) || str_contains($lostColor, $fiColor)) {
                    $score += 10;
                }
            }

            $fiName = mb_strtolower((string) $fi->item_name);
            if ($lostName !== '' && $fiName !== '') {
                if ($fiName === $lostName) {
                    $score += 30;
                } elseif (str_contains($fiName, $lostName) || str_contains($lostName, $fiName)) {
                    $score += 20;
                } else {
                    $dist = levenshtein(mb_substr($lostName, 0, 80), mb_substr($fiName, 0, 80));
                    if ($dist <= 6) {
                        $score += 10;
                    }
                }
            }

            if ($lostItem->date_lost && $fi->date_found) {
                $days = abs($lostItem->date_lost->diffInDays($fi->date_found));
                if ($days <= 3) $score += 15;
                elseif ($days <= 14) $score += 8;
                elseif ($days <= 30) $score += 3;
            }

            $arr = (new FoundItemResource($fi))->toArray($request);
            $arr['match_score'] = $score;

            return $arr;
        })
            ->sortByDesc('match_score')
            ->values()
            ->take($max);

        return $this->successResponse(
            'Potential matches retrieved successfully.',
            [
                'lost_item_id' => $lostItem->id,
                'items' => $scored,
            ]
        );
    }

   

        public function update(UpdateLostItemRequest $request, LostItem $lostItem)
    {
        $user = $request->user();

        if ($user->role === 'user') {
            if ($lostItem->user_id !== $user->id) {
                return $this->errorResponse(
                    'Forbidden. You are not allowed to update this lost item.',
                    null,
                    403
                );
            }

            if ($lostItem->status !== 'pending') {
                return $this->errorResponse(
                    'Only pending lost items can be updated.',
                    null,
                    422
                );
            }
        }

        if ($user->role === 'staff') {
            return $this->errorResponse(
                'Staff are not allowed to update lost items.',
                null,
                403
            );
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->replaceImage(
                $request->file('image'),
                $lostItem->image_path,
                'lost-items'
            );
        }

        if ($user->role === 'user') {
            unset($data['status']);
        }

        $lostItem->update($data);

        return $this->successResponse(
            'Lost item updated successfully.',
            new LostItemResource($lostItem->fresh(['user', 'category']))
        );
    }



    public function destroy(Request $request, LostItem $lostItem)
{
    $user = $request->user();

    if ($user->role === 'user') {
        if ($lostItem->user_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to delete this lost item.',
                null,
                403
            );
        }

        if ($lostItem->status !== 'pending') {
            return $this->errorResponse(
                'Only pending lost items can be deleted.',
                null,
                422
            );
        }
    }

    if ($user->role === 'staff') {
        return $this->errorResponse(
            'Staff are not allowed to delete lost items.',
            null,
            403
        );
    }

    $this->deleteImage($lostItem->image_path);
    $lostItem->delete();

    return $this->successResponse('Lost item deleted successfully.');
}
}