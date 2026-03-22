<?php

namespace App\Http\Controllers\Api;

use App\Models\FoundItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FoundItemResource;
use App\Http\Requests\FoundItem\StoreFoundItemRequest;
use App\Http\Requests\FoundItem\UpdateFoundItemRequest;
use App\Traits\HandlesQrCodes;
use App\Traits\HandlesUploads;
use Illuminate\Support\Str;

class FoundItemController extends Controller
{
    use ApiResponse, HandlesUploads, HandlesQrCodes;

    protected function generateReferenceCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (FoundItem::where('reference_code', $code)->exists());

        return $code;
    }

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
            $data['image_path'] = $this->storeImage($request->file('image'), 'found-items');
        }

        $data['staff_id'] = $request->user()->id;
        $data['reference_code'] = $this->generateReferenceCode();
        $data['status'] = 'available';

        $foundItem = FoundItem::create($data);

        $qrPath = $this->generateFoundItemQrCode($foundItem->reference_code);

        $foundItem->update([
            'qr_code_path' => $qrPath,
        ]);

        $foundItem->load(['staff', 'category']);

        return $this->successResponse(
            'Found item created successfully.',
            new FoundItemResource($foundItem->fresh(['staff', 'category'])),
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

    if ($user->role === 'staff') {
        if ($foundItem->staff_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to update this found item.',
                null,
                403
            );
        }

        if (! in_array($foundItem->status, ['available', 'under_review'])) {
            return $this->errorResponse(
                'This found item can no longer be updated.',
                null,
                422
            );
        }
    }

    $data = $request->validated();

    if ($request->hasFile('image')) {
        $data['image_path'] = $request->file('image')->store('found-items', 'public');
    }

    if ($user->role === 'staff' && isset($data['status'])) {
        if (! in_array($data['status'], ['available', 'under_review'])) {
            return $this->errorResponse(
                'Staff cannot set this status.',
                null,
                422
            );
        }
    }

    $foundItem->update($data);

    return $this->successResponse(
        'Found item updated successfully.',
        new FoundItemResource($foundItem->fresh(['staff', 'category']))
    );
}

    
public function destroy(Request $request, FoundItem $foundItem)
{
    $user = $request->user();

    if ($user->role === 'staff') {
        if ($foundItem->staff_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to delete this found item.',
                null,
                403
            );
        }

        if ($foundItem->status !== 'available') {
            return $this->errorResponse(
                'Only available found items can be deleted by staff.',
                null,
                422
            );
        }
    }
    $this->deleteImage($foundItem->image_path);
    $this->deleteQrCode($foundItem->qr_code_path);

    $foundItem->delete();

    return $this->successResponse('Found item deleted successfully.');
}

    public function regenerateQr(Request $request, FoundItem $foundItem)
    {
        $user = $request->user();

        if ($user->role === 'staff' && $foundItem->staff_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to regenerate QR for this item.',
                null,
                403
            );
        }

        $newQrPath = $this->replaceFoundItemQrCode(
            $foundItem->qr_code_path,
            $foundItem->reference_code
        );

        $foundItem->update([
            'qr_code_path' => $newQrPath,
        ]);

        return $this->successResponse(
            'QR code regenerated successfully.',
            new FoundItemResource($foundItem->fresh(['staff', 'category']))
        );
    }

public function archive(Request $request, FoundItem $foundItem)
{
    if (! in_array($foundItem->status, ['available', 'under_review'])) {
        return $this->errorResponse(
            'Only available or under review items can be archived.',
            null,
            422
        );
    }

    $foundItem->update([
        'status' => 'archived',
    ]);

    return $this->successResponse(
        'Found item archived successfully.',
        new FoundItemResource($foundItem->fresh(['staff', 'category']))
    );
}
}