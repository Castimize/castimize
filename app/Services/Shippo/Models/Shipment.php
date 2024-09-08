<?php

namespace App\Services\Shippo\Models;

class Shipment
{
    private $fromAddress;
    private $toAddress;
    private $parcel;

    /**
     * @param array $address
     * @return void
     */
    public function setFromAddress(array $address): void
    {
        $this->fromAddress = $address;
    }

    /**
     * @param array $address
     * @return void
     */
    public function setToAddress(array $address): void
    {
        $this->toAddress = $address;
    }

    public function setParcel(array $parcel): void
    {
        $this->parcel = $parcel;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return [
            'address_from' => $this->fromAddress,
            'address_to' => $this->toAddress,
            'parcels' => [
                $this->parcel
            ],
        ];
    }
}
