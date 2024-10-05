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
     * @return Shippo_Address
     */
    public function validateAddress(): Shippo_Address
    {
        $this->fromAddress['validate'] = true;
        return Shippo_Address::create($this->fromAddress);
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
