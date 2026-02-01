<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\DTO\Order\UploadDTO;
use App\Helpers\MonetaryAmount;

trait NeedsUploadDTO
{
    protected function createUploadDTO(array $overrides = []): UploadDTO
    {
        return new UploadDTO(
            wpId: $overrides['wpId'] ?? (string) fake()->numberBetween(10000, 99999),
            materialId: $overrides['materialId'] ?? 1,
            materialName: $overrides['materialName'] ?? '14k Yellow Gold Plated Brass',
            name: $overrides['name'] ?? 'Test Model',
            fileName: $overrides['fileName'] ?? 'test_model.stl',
            modelVolumeCc: $overrides['modelVolumeCc'] ?? 0.69,
            modelXLength: $overrides['modelXLength'] ?? 1.0,
            modelYLength: $overrides['modelYLength'] ?? 1.0,
            modelZLength: $overrides['modelZLength'] ?? 1.0,
            modelBoxVolume: $overrides['modelBoxVolume'] ?? 2.32,
            surfaceArea: $overrides['surfaceArea'] ?? 5.79,
            modelParts: $overrides['modelParts'] ?? 1,
            quantity: $overrides['quantity'] ?? 1,
            inCents: $overrides['inCents'] ?? false,
            subtotal: $overrides['subtotal'] ?? MonetaryAmount::fromFloat(50.00),
            subtotalTax: $overrides['subtotalTax'] ?? MonetaryAmount::fromFloat(10.50),
            total: $overrides['total'] ?? MonetaryAmount::fromFloat(50.00),
            totalTax: $overrides['totalTax'] ?? MonetaryAmount::fromFloat(10.50),
            metaData: $overrides['metaData'] ?? null,
            customerLeadTime: $overrides['customerLeadTime'] ?? 13,
        );
    }

    /**
     * @return UploadDTO[]
     */
    protected function createUploadDTOCollection(int $count = 1, array $overrides = []): array
    {
        $uploads = [];
        for ($i = 0; $i < $count; $i++) {
            $uploads[] = $this->createUploadDTO(array_merge($overrides, [
                'wpId' => $overrides['wpId'] ?? (string) fake()->numberBetween(10000, 99999),
            ]));
        }

        return $uploads;
    }
}
