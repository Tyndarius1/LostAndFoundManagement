<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicFoundItemResource;
use App\Http\Resources\PublicLostItemResource;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicBrowseController extends Controller
{
    use ApiResponse;

    public function lostItems(Request $request)
    {
        $query = LostItem::with('category')
            ->whereIn('status', ['pending', 'matched']);

        $this->applyLostItemFilters($query, $request);

        $lostItems = $query->latest()->paginate(
            $request->integer('per_page', 12)
        );

        return $this->successResponse(
            'Public lost items retrieved successfully.',
            [
                'items' => PublicLostItemResource::collection($lostItems->items()),
                'pagination' => [
                    'current_page' => $lostItems->currentPage(),
                    'last_page' => $lostItems->lastPage(),
                    'per_page' => $lostItems->perPage(),
                    'total' => $lostItems->total(),
                ],
            ]
        );
    }

    public function foundItems(Request $request)
    {
        $query = FoundItem::with('category')
            ->where('status', 'available');

        $this->applyFoundItemFilters($query, $request);

        $foundItems = $query->latest()->paginate(
            $request->integer('per_page', 12)
        );

        return $this->successResponse(
            'Public found items retrieved successfully.',
            [
                'items' => PublicFoundItemResource::collection($foundItems->items()),
                'pagination' => [
                    'current_page' => $foundItems->currentPage(),
                    'last_page' => $foundItems->lastPage(),
                    'per_page' => $foundItems->perPage(),
                    'total' => $foundItems->total(),
                ],
            ]
        );
    }

    public function foundItemByReference(string $referenceCode)
    {
        $foundItem = FoundItem::with('category')
            ->where('reference_code', $referenceCode)
            ->where('status', 'available')
            ->first();

        if (! $foundItem) {
            return $this->errorResponse(
                'Found item not found.',
                null,
                404
            );
        }

        return $this->successResponse(
            'Public found item retrieved successfully.',
            new PublicFoundItemResource($foundItem)
        );
    }

    private function applyLostItemFilters(Builder $query, Request $request): void
    {
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            if (in_array($status, ['pending', 'matched'])) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_lost', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_lost', '<=', $request->date('date_to'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('last_seen_location', 'like', "%{$search}%");
            });
        }
    }

    private function applyFoundItemFilters(Builder $query, Request $request): void
    {
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_found', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_found', '<=', $request->date('date_to'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('found_location', 'like', "%{$search}%")
                    ->orWhere('reference_code', 'like', "%{$search}%");
            });
        }
    }
}
