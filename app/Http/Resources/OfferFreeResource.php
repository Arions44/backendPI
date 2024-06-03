<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferFreeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_business' => $this->business,
            'type' => $this->type,
            'name' => $this->name,
            'discount' => $this->discount,
            'description' => $this->description,
            'last_description' => $this->last_description,
            'image' => $this->image,
            'counter' => $this->counter,
            'category' => $this->category,
            'enyoys' => EnyoyResource::collection($this->whenLoaded('enyoys')),
            'deleted' => $this->deleted,
            'enjoyed' => $this->enjoyed,
        ];
    }
}
