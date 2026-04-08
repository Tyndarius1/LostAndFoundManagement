<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authUser = $request->user();
        $canSeeInternal = $authUser && in_array($authUser->role, ['admin', 'staff'], true);
        $isOwner = $authUser && $this->claimant_id && $authUser->id === $this->claimant_id;

        return [
            'id' => $this->id,
            'claimant' => new UserResource($this->whenLoaded('claimant')),
            'found_item' => new FoundItemResource($this->whenLoaded('foundItem')),
            'approver' => new UserResource($this->whenLoaded('approver')),
            'proof_details' => $this->proof_details,
            'proof_image_url' => $this->proof_image_path
                ? asset('storage/' . $this->proof_image_path)
                : null,
            'staff_notes' => $canSeeInternal ? $this->staff_notes : null,
            'pickup_code' => ($canSeeInternal || $isOwner) ? $this->pickup_code : null,
            'pickup_code_expires_at' => ($canSeeInternal || $isOwner) ? $this->pickup_code_expires_at?->toDateTimeString() : null,
            'status' => $this->status,
            'approved_at' => $this->approved_at?->toDateTimeString(),
            'released_at' => $this->released_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}