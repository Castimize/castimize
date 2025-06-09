<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopOwnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shops' => ShopResource::collection($this->shops),
            'vat_number' => $this->customer?->vat_number,
        ];
    }
}
