<?php

namespace App\Http\Controllers\Api;

use App\Models\ClaimRequest;
use App\Models\FoundItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClaimRequestResource;
use App\Http\Requests\ClaimRequest\StoreClaimRequestRequest;
use App\Http\Requests\ClaimRequest\UpdateClaimRequestRequest;

class ClaimRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $query = ClaimRequest::with([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ])->latest();

        if ($user->role === 'user') {
            $query->where('claimant_id', $user->id);
        }

        if ($user->role === 'staff') {
            $query->whereHas('foundItem', function ($q) use ($user) {
                $q->where('staff_id', $user->id);
            });
        }

        $claimRequests = $query->get();

        return $this->successResponse(
            'Claim requests retrieved successfully.',
            ClaimRequestResource::collection($claimRequests)
        );
    }

    public function store(StoreClaimRequestRequest $request)
    {
        $user = $request->user();

        if ($user->role !== 'user') {
            return $this->errorResponse(
                'Only regular users can submit claim requests.',
                null,
                403
            );
        }

        $foundItem = FoundItem::findOrFail($request->validated('found_item_id'));

        if ($foundItem->status !== 'available') {
            return $this->errorResponse(
                'This found item is not available for claiming.',
                null,
                422
            );
        }

        $existingPendingOrApproved = ClaimRequest::where('claimant_id', $user->id)
            ->where('found_item_id', $foundItem->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existingPendingOrApproved) {
            return $this->errorResponse(
                'You already have an active claim request for this item.',
                null,
                422
            );
        }

        $data = $request->validated();
        $data['claimant_id'] = $user->id;
        $data['status'] = 'pending';

        if ($request->hasFile('proof_image')) {
            $data['proof_image_path'] = $request->file('proof_image')->store('claim-proofs', 'public');
        }

        unset($data['proof_image']);

        $claimRequest = ClaimRequest::create($data);
        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        return $this->successResponse(
            'Claim request submitted successfully.',
            new ClaimRequestResource($claimRequest),
            201
        );
    }

    public function show(Request $request, ClaimRequest $claimRequest)
    {
        $user = $request->user();

        if ($user->role === 'user' && $claimRequest->claimant_id !== $user->id) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to view this claim request.',
                null,
                403
            );
        }

        if (
            $user->role === 'staff' &&
            $claimRequest->foundItem->staff_id !== $user->id
        ) {
            return $this->errorResponse(
                'Forbidden. You are not allowed to view this claim request.',
                null,
                403
            );
        }

        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        return $this->successResponse(
            'Claim request retrieved successfully.',
            new ClaimRequestResource($claimRequest)
        );
    }

    public function update(UpdateClaimRequestRequest $request, ClaimRequest $claimRequest)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($user->role === 'user') {
            if ($claimRequest->claimant_id !== $user->id) {
                return $this->errorResponse(
                    'Forbidden. You are not allowed to update this claim request.',
                    null,
                    403
                );
            }

            if ($claimRequest->status !== 'pending') {
                return $this->errorResponse(
                    'Only pending claim requests can be updated.',
                    null,
                    422
                );
            }

            unset($data['status']);

            if ($request->hasFile('proof_image')) {
                $data['proof_image_path'] = $request->file('proof_image')->store('claim-proofs', 'public');
            }

            unset($data['proof_image']);

            $claimRequest->update($data);
        } elseif ($user->role === 'admin') {
            if ($request->hasFile('proof_image')) {
                $data['proof_image_path'] = $request->file('proof_image')->store('claim-proofs', 'public');
            }

            unset($data['proof_image']);

            if (isset($data['status'])) {
                if ($data['status'] === 'approved') {
                    $alreadyApproved = ClaimRequest::where('found_item_id', $claimRequest->found_item_id)
                        ->where('id', '!=', $claimRequest->id)
                        ->where('status', 'approved')
                        ->exists();

                    if ($alreadyApproved) {
                        return $this->errorResponse(
                            'Another claim request has already been approved for this item.',
                            null,
                            422
                        );
                    }

                    $data['approved_by'] = $user->id;
                    $data['approved_at'] = now();

                    $claimRequest->foundItem()->update([
                        'status' => 'under_review',
                    ]);
                }

                if ($data['status'] === 'rejected') {
                    $data['approved_by'] = $user->id;
                    $data['approved_at'] = null;
                }
            }

            $claimRequest->update($data);
        } else {
            return $this->errorResponse(
                'Forbidden. You are not allowed to update this claim request.',
                null,
                403
            );
        }

        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        return $this->successResponse(
            'Claim request updated successfully.',
            new ClaimRequestResource($claimRequest->fresh([
                'claimant',
                'foundItem.staff',
                'foundItem.category',
                'approver',
            ]))
        );
    }

    public function destroy(Request $request, ClaimRequest $claimRequest)
    {
        $user = $request->user();

        if ($user->role === 'user') {
            if ($claimRequest->claimant_id !== $user->id) {
                return $this->errorResponse(
                    'Forbidden. You are not allowed to delete this claim request.',
                    null,
                    403
                );
            }

            if ($claimRequest->status !== 'pending') {
                return $this->errorResponse(
                    'Only pending claim requests can be deleted.',
                    null,
                    422
                );
            }
        } elseif ($user->role !== 'admin') {
            return $this->errorResponse(
                'Forbidden. You are not allowed to delete this claim request.',
                null,
                403
            );
        }

        $claimRequest->delete();

        return $this->successResponse(
            'Claim request deleted successfully.'
        );
    }
}