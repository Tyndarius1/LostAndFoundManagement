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
use App\Traits\HandlesUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClaimSubmittedMail;
use App\Mail\ClaimApprovedMail;
use App\Mail\ClaimRejectedMail;
use App\Mail\ItemReleasedMail;


class ClaimRequestController extends Controller
{
    use ApiResponse, HandlesUploads;

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
            $data['proof_image_path'] = $this->storeImage(
                $request->file('proof_image'),
                'claim-proofs'
            );        }

        unset($data['proof_image']);

        $claimRequest = ClaimRequest::create($data);
        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        Mail::to($claimRequest->claimant->email)->send(new ClaimSubmittedMail($claimRequest));
        
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
    
        if ($user->role !== 'user') {
            return $this->errorResponse(
                'Only users can update their own pending claim details here.',
                null,
                403
            );
        }
    
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
    
        $data = $request->validated();
    
        unset($data['status']);
    
        if ($request->hasFile('proof_image')) {
            $data['proof_image_path'] = $this->replaceImage(
                $request->file('proof_image'),
                $claimRequest->proof_image_path,
                'claim-proofs'
            );        }
    
        unset($data['proof_image']);
    
        $claimRequest->update($data);
    
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

    public function approve(Request $request, ClaimRequest $claimRequest)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return $this->errorResponse(
                'Only admins can approve claim requests.',
                null,
                403
            );
        }

        if ($claimRequest->status !== 'pending') {
            return $this->errorResponse(
                'Only pending claim requests can be approved.',
                null,
                422
            );
        }

        $foundItem = $claimRequest->foundItem;

        if ($foundItem->status !== 'available') {
            return $this->errorResponse(
                'This found item is not available for approval.',
                null,
                422
            );
        }

        $alreadyApproved = ClaimRequest::where('found_item_id', $claimRequest->found_item_id)
            ->where('status', 'approved')
            ->exists();

        if ($alreadyApproved) {
            return $this->errorResponse(
                'A claim request has already been approved for this item.',
                null,
                422
            );
        }

        DB::transaction(function () use ($claimRequest, $foundItem, $admin) {
            $claimRequest->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            ClaimRequest::where('found_item_id', $claimRequest->found_item_id)
                ->where('id', '!=', $claimRequest->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                ]);

            $foundItem->update([
                'status' => 'under_review',
            ]);
        });

        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        Mail::to($claimRequest->claimant->email)->send(new ClaimApprovedMail($claimRequest));

        return $this->successResponse(
            'Claim request approved successfully.',
            new ClaimRequestResource($claimRequest)
        );
    }

    public function reject(Request $request, ClaimRequest $claimRequest)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return $this->errorResponse(
                'Only admins can reject claim requests.',
                null,
                403
            );
        }

        if ($claimRequest->status !== 'pending') {
            return $this->errorResponse(
                'Only pending claim requests can be rejected.',
                null,
                422
            );
        }

        DB::transaction(function () use ($claimRequest, $admin) {
            $claimRequest->update([
                'status' => 'rejected',
                'approved_by' => $admin->id,
                'approved_at' => null,
            ]);
        });

        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        Mail::to($claimRequest->claimant->email)->send(new ClaimRejectedMail($claimRequest));

        return $this->successResponse(
            'Claim request rejected successfully.',
            new ClaimRequestResource($claimRequest)
        );
    }


    public function release(Request $request, ClaimRequest $claimRequest)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return $this->errorResponse(
                'Only admins can release approved claim requests.',
                null,
                403
            );
        }

        if ($claimRequest->status !== 'approved') {
            return $this->errorResponse(
                'Only approved claim requests can be released.',
                null,
                422
            );
        }

        $foundItem = $claimRequest->foundItem;

        DB::transaction(function () use ($claimRequest, $foundItem) {
            $claimRequest->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            $foundItem->update([
                'status' => 'claimed',
            ]);
        });

        $claimRequest->load([
            'claimant',
            'foundItem.staff',
            'foundItem.category',
            'approver',
        ]);

        Mail::to($claimRequest->claimant->email)->send(new ItemReleasedMail($claimRequest));

        return $this->successResponse(
            'Item released successfully.',
            new ClaimRequestResource($claimRequest)
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
        $this->deleteImage($claimRequest->proof_image_path);
        $claimRequest->delete();

        return $this->successResponse(
            'Claim request deleted successfully.'
        );
    }
}
