<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicLostItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'item_name' => $this->item_name,
            'description' => $this->description,
            'color' => $this->color,
            'last_seen_location' => $this->last_seen_location,
            'date_lost' => $this->date_lost?->format('Y-m-d'),
            'image_url' => $this->image_path
                ? asset('storage/'.$this->image_path)
                : null,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
