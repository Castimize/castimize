<?php

namespace App\Services\Shippo;

use Shippo_Address;

class ShippoService
{
    private $fromAddress = [];

    /**
     * @param array $address
     * @return static
     */
    public function setFromAddress(array $address): static
    {
        $this->fromAddress = $address;
        return $this;
    }

    /**
     * @return array
     */
    public function validateAddress(): array
    {
        $this->fromAddress['validate'] = true;
        $shippoAddress = Shippo_Address::create($this->fromAddress);

        $valid = $shippoAddress['validation_results']['is_valid'];
        $errorMessages = $shippoAddress['validation_results']['messages'];
        $addressChanged = false;

        if (
            $this->fromAddress['street1'] !== $shippoAddress['street1'] ||
            $this->fromAddress['street2'] !== $shippoAddress['street2'] ||
            $this->fromAddress['city'] !== $shippoAddress['city'] ||
            $this->fromAddress['state'] !== $shippoAddress['state'] ||
            $this->fromAddress['zip'] !== $shippoAddress['zip'] ||
            $this->fromAddress['country'] !== $shippoAddress['country']
        ) {
            $addressChanged = true;
            $this->fromAddress['street1'] = $shippoAddress['street1'];
            $this->fromAddress['street2'] = $shippoAddress['street2'];
            $this->fromAddress['city'] = $shippoAddress['city'];
            $this->fromAddress['state'] = $shippoAddress['state'];
            $this->fromAddress['zip'] = $shippoAddress['zip'];
            $this->fromAddress['country'] = $shippoAddress['country'];
        }

        return ['valid' => $valid, 'address' => $this->fromAddress, 'address_changed' => $addressChanged, 'messages' => $errorMessages];
    }

//    public function rates(User $user, Product $product)
//    {
//        // Grab the shipping address from the User model
//        $toAddress = $user->shippingAddress();
//
//        // Pass the PURCHASE flag.
//        $toAddress['object_purpose'] = 'PURCHASE';
//
//        // Get the shipment object
//        return Shippo_Shipment::create([
//            'object_purpose'=> 'PURCHASE',
//            'address_from'=> $this->fromAddress,
//            'address_to'=> $toAddress,
//            'parcel'=> $product->toArray(),
//            'insurance_amount'=> '30',
//            'insurance_currency'=> 'USD',
//            'async'=> false
//        ]);
//    }
//
//    /**
//     * Create the shipping label transaction
//     *
//     * @param $rateId -- object_id from rates_list
//     * @return Shippo_Transaction
//     */
//    public function createLabel($rateId)
//    {
//        return Shippo_Transaction::create([
//            'rate' => $rateId,
//            'label_file_type' => "PDF",
//            'async' => false
//        ]);
//    }
}
