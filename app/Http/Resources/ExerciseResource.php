<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExerciseResource extends JsonResource
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
            'name' => $this->name,
            'improvement' => $this->improvement,
            'type' => $this->type,
            'explanation' => $this->explanation,
            'image' => $this->image,
            'audio' => $this->audio,
            'video' => $this->video,
        ];
	}
}
