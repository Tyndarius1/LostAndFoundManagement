<?php

namespace App\Http\Controllers\Api;

use App\Models\LostItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\LostItemResource;
use App\Http\Requests\LostItem\StoreLostItemRequest;
use App\Http\Requests\LostItem\UpdateLostItemRequest;

class LostItemController extends Controller
{
    use ApiResponse;

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
            $data['image_path'] = $request->file('image')->store('lost-items', 'public');
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

    public function update(UpdateLostItemRequest $request, LostItem $lostItem)
    {
        $user = $request->user();

        if ($user->role === 'user' && $lostItem->user_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to update this lost item.',
                null,
                403
            );
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('lost-items', 'public');
        }

        if ($user->role === 'user') {
            unset($data['status']);
        }

        $lostItem->update($data);
        $lostItem->load(['user', 'category']);

        return $this->successResponse(
            'Lost item updated successfully.',
            new LostItemResource($lostItem->fresh(['user', 'category']))
        );
    }

    public function destroy(Request $request, LostItem $lostItem)
    {
        $user = $request->user();

        if ($user->role === 'user' && $lostItem->user_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to delete this lost item.',
                null,
                403
            );
        }

        $lostItem->delete();

        return $this->successResponse(
            'Lost item deleted successfully.'
        );
    }
}