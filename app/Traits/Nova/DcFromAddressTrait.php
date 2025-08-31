<?php

namespace App\Traits\Nova;

use App\Nova\Settings\Shipping\DcSettings;
use Laravel\Nova\Fields\Text;

trait DcFromAddressTrait
{
    protected function fromAddressFields()
    {
        $dcSettings = (new DcSettings);

        return [
            Text::make(__('Name'), 'from_address_name')
                ->default($dcSettings->name),

            Text::make(__('Company'), 'from_address_company')
                ->default($dcSettings->company),

            Text::make(__('Address 1'), 'from_address_address_line1')
                ->default($dcSettings->addressLine1),

            Text::make(__('Address 2'), 'from_address_address_line2')
                ->default($dcSettings->addressLine2),

            Text::make(__('Postal code'), 'from_address_postal_code')
                ->default($dcSettings->postalCode),

            Text::make(__('City'), 'from_address_city')
                ->default($dcSettings->city),

            Text::make(__('State'), 'from_address_state')
                ->default($dcSettings->state),

            Text::make(__('Country'), 'from_address_country')
                ->default($dcSettings->country),

            Text::make(__('Phone'), 'from_address_phone')
                ->default($dcSettings->phone),

            Text::make(__('Email'), 'from_address_email')
                ->default($dcSettings->email),
        ];
    }
}
