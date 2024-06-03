<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnyoyResource extends JsonResource
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
            'id_user' => $this->id_user,
            'id_offer' => $this->id_offer,
            'enjoyed' => $this->enjoyed,
            'enjoyed_at' => $this->enjoyed_at,
            'deleted' => $this->deleted,
        ];
    }
}
