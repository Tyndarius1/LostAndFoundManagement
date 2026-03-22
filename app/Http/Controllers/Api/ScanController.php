<?php

namespace App\Http\Controllers\Api;

use App\Models\FoundItem;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\FoundItemResource;

class ScanController extends Controller
{
    use ApiResponse;

    public function show(string $referenceCode)
    {
        $foundItem = FoundItem::with(['staff', 'category'])
            ->where('reference_code', $referenceCode)
            ->first();

        if (! $foundItem) {
            return $this->errorResponse(
                'QR code record not found.',
                null,
                404
            );
        }

        return $this->successResponse(
            'Scanned item retrieved successfully.',
            new FoundItemResource($foundItem)
        );
    }
}
