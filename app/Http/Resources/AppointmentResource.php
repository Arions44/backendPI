<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		return [
            'student_id' => $this->student_id,
            'tutor_id' => $this->tutor_id,
            'time' => $this->time,
            'description' => $this->description,
        ];
	}
}
