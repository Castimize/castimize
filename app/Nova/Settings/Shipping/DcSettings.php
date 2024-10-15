<?php

namespace App\Nova\Settings\Shipping;

use App\Models\Country;
use Devloops\NovaSystemSettings\Contracts\SystemSettings;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;


class DcSettings extends SystemSettings
{
    public ?string $name;
    public ?string $company;
    public ?string $addressLine1;
    public ?string $addressLine2;
    public ?string $postalCode;
    public ?string $city;
    public ?string $state;
    public ?string $country;
    public ?string $phone;
    public ?string $email;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function title(): string
    {
        return __('DC Settings');
    }

    public static function icon(): string
    {
        return 'office-building';
    }

    public static function name(): string
    {
        return 'dc_settings';
    }

    public static function fields(): array
    {
        return [
            Text::make(__('Name'), 'name'),

            Text::make(__('Company'), 'company'),

            Text::make(__('Address line 1'), 'addressLine1'),

            Text::make(__('Address line 2'), 'addressLine2'),

            Text::make(__('Postal Code'), 'postalCode'),

            Text::make(__('City'), 'city'),

            Text::make(__('State'), 'state'),

            Select::make(__('Country'), 'country')

                ->options(Country::all()->pluck('name', 'alpha2')->toArray()),

            Text::make(__('Phone'), 'phone'),

            Text::make(__('Email'), 'email'),
        ];
    }
}
