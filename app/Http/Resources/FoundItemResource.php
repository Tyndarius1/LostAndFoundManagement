<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoundItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff' => new UserResource($this->whenLoaded('staff')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'item_name' => $this->item_name,
            'description' => $this->description,
            'color' => $this->color,
            'found_location' => $this->found_location,
            'date_found' => $this->date_found?->format('Y-m-d'),
            'image_url' => $this->image_path
                ? asset('storage/' . $this->image_path)
                : null,
            'qr_code_url' => $this->qr_code_path
                ? asset('storage/' . $this->qr_code_path)
                : null,
            'reference_code' => $this->reference_code,
            'storage_location' => $this->storage_location,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}