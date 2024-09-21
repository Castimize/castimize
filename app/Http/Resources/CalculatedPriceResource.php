<?php

namespace App\Http\Resources;

use App\Models\Material;
use App\Models\Price;
use App\Services\Admin\CalculatePricesService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CalculatedPriceResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = Price::class;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total' => $this->calculated_total,
        ];
    }
}
