<?php

namespace App\Http\Controllers\Api;

use App\Models\FoundItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FoundItemResource;
use App\Http\Requests\FoundItem\StoreFoundItemRequest;
use App\Http\Requests\FoundItem\UpdateFoundItemRequest;

class FoundItemController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $query = FoundItem::with(['staff', 'category'])->latest();

        if ($user->role === 'staff') {
            $query->where('staff_id', $user->id);
        }

        $foundItems = $query->get();

        return $this->successResponse(
            'Found items retrieved successfully.',
            FoundItemResource::collection($foundItems)
        );
    }

    public function store(StoreFoundItemRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('found-items', 'public');
        }

        $data['staff_id'] = $request->user()->id;
        $data['reference_code'] = $this->generateReferenceCode();
        $data['status'] = 'available';

        $foundItem = FoundItem::create($data);
        $foundItem->load(['staff', 'category']);

        return $this->successResponse(
            'Found item created successfully.',
            new FoundItemResource($foundItem),
            201
        );
    }

    public function show(Request $request, FoundItem $foundItem)
    {
        $user = $request->user();

        if ($user->role === 'staff' && $foundItem->staff_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to view this found item.',
                null,
                403
            );
        }

        $foundItem->load(['staff', 'category']);

        return $this->successResponse(
            'Found item retrieved successfully.',
            new FoundItemResource($foundItem)
        );
    }

    public function update(UpdateFoundItemRequest $request, FoundItem $foundItem)
    {
        $user = $request->user();

        if ($user->role === 'staff' && $foundItem->staff_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to update this found item.',
                null,
                403
            );
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('found-items', 'public');
        }

        $foundItem->update($data);
        $foundItem->load(['staff', 'category']);

        return $this->successResponse(
            'Found item updated successfully.',
            new FoundItemResource($foundItem->fresh(['staff', 'category']))
        );
    }

    public function destroy(Request $request, FoundItem $foundItem)
    {
        $user = $request->user();

        if ($user->role === 'staff' && $foundItem->staff_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to delete this found item.',
                null,
                403
            );
        }

        $foundItem->delete();

        return $this->successResponse(
            'Found item deleted successfully.'
        );
    }

    private function generateReferenceCode(): string
    {
        do {
            $referenceCode = 'FI-' . now()->format('Y') . '-' . strtoupper(str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT));
        } while (FoundItem::where('reference_code', $referenceCode)->exists());

        return $referenceCode;
    }
}