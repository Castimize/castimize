<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wp_id' => $this->wp_id,
            'name' => $this->name,
            'link' => '&attribute_pa_p3d_material=' . $this->wp_id,
        ];
    }
}
