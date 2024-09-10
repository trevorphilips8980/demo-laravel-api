<?php

namespace App\Http\Resources;

use App\Services\UrlService;
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
            'id' => UrlService::encodeId($this->id),
            'name' => $this->name,
            'email' => $this->email,
            'role_name' => $this->role_name,
        ];
    }
}
