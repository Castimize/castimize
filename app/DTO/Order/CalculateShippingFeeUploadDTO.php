<?php

namespace App\DTO\Order;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

readonly class CalculateShippingFeeUploadDTO
{
    public function __construct(
        public float $modelBoxVolume,
        public int $quantity,
    ) {
    }

    public static function fromWpRequest(array $upload): CalculateShippingFeeUploadDTO
    {
        if (! array_key_exists('3dp_options', $upload)) {
            throw new UnprocessableEntityHttpException(__('Incorrect request'));
        }
        return new self(
            modelBoxVolume: $upload['3dp_options']['model_stats_raw']['model']['box_volume'],
            quantity: $upload['quantity'],
        );
    }

    public static function fromEtsyLine(array $line): CalculateShippingFeeUploadDTO
    {
        return new self(
            modelBoxVolume: $line['shop_listing_model']->model->model_box_volume,
            quantity: $line['transaction']->quantity,
        );
    }
}
