<?php

namespace App\Nova\Settings\Shipping;

use App\Enums\Shippo\ShippoDistanceUnitsEnum;
use App\Enums\Shippo\ShippoMassUnitsEnum;
use App\Services\Shippo\ShippoService;
use Devloops\NovaSystemSettings\Contracts\SystemSettings;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;


class ParcelSettings extends SystemSettings
{
    public ?string $distanceUnit;
    public ?float $length;
    public ?float $width;
    public ?float $height;
    public ?string $massUnit;
    public ?float $weight;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function title(): string
    {
        return __('Parcel Settings');
    }

    public static function icon(): string
    {
        return 'newspaper';
    }

    public static function name(): string
    {
        return 'parcel_settings';
    }

    public static function fields(): array
    {
        return [
            Select::make(__('Distance unit'), 'distanceUnit')
                ->options(ShippoDistanceUnitsEnum::values())
                ->displayUsingLabels(),

            Text::make(__('Length'),'length'),

            Text::make(__('Width'), 'width'),

            Text::make(__('Height'), 'height'),

            Select::make(__('Mass unit'), 'massUnit')
                ->options(ShippoMassUnitsEnum::values())
                ->displayUsingLabels(),

            Text::make(__('Weight'), 'weight'),
        ];
    }
}
