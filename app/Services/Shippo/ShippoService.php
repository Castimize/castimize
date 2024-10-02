<?php

namespace App\Services\Shippo;

use Shippo_Address;

class ShippoService
{
    public function validateAddress(array $address)
    {
        //[
        //            'name' => $request->name,
        //            'company' => $request->company,
        //            'street1' => $request->address_1,
        //            'city' => $request->city,
        //            'state' => $request->state,
        //            'zip' => $request->postal_code,
        //            'country' => $request->country,
        //            'email' => $request->email,
        //            'validate' => true,
        //        ]
        $fromAddress = Shippo_Address::create($address);
    }
}
