<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'avatar' => sprintf('%s/%s', config('filesystems.disks.s3.url'), $this->avatar),
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'creator' => $this->creator?->name,
            'updated_at' => $this->updated_at,
            'editor' => $this->editor?->name,
            'deleted_at' => $this->deleted_at,
            'destroyer' => $this->destroyer?->name,
        ];
    }
}
