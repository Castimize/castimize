<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalculatedPriceResource extends JsonResource
{
    private $requestData;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $request->toArray();
        $data['argument_1'] = 10.00;
        $data['argument_6']['p3d_estimated_price'] = 10.00;

        return $data;
    }

    public function setRequestData(Request $request): static
    {
        $this->requestData = $request;
        return $this;
    }
}
