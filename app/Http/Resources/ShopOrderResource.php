<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'order_number' => $this->order_number,
            'shop_receipt_id' => $this->shop_receipt_id,
            'state' => $this->state,
        ];
    }
}
