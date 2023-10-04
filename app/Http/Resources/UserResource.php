<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'verified_status' => $this->phone_number_verified ? true : false,
            'address' => $this->address,
            'birth_date' => Carbon::parse($this->birth_date)->format('d M Y'),
            'token' => $this->token,
            'role' => $this->role,
        ];
    }
}
