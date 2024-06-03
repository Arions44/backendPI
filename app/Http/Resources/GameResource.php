<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		return [
            'message' => $this->message,
            'opt_1' => $this->opt_1,
            'opt_2' => $this->opt_2,
            'opt_3' => $this->opt_3,
            'opt_4' => $this->opt_4,
            'right_opt' => $this->right_opt,
        ];
	}
}
