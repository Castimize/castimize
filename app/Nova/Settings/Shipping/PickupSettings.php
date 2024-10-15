<?php

namespace App\Nova\Settings\Shipping;

use App\Services\Shippo\ShippoService;
use Devloops\NovaSystemSettings\Contracts\SystemSettings;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;


class PickupSettings extends SystemSettings
{
    public ?string $buildingType;
    public ?string $buildingLocationType;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function title(): string
    {
        return __('Pickup Settings');
    }

    public static function icon(): string
    {
        return 'cursor-click';
    }

    public static function name(): string
    {
        return 'pickup_settings';
    }

    public static function fields(): array
    {
        return [
            Select::make(__('Building type'), 'buildingType')
                ->options(ShippoService::BUILDING_TYPES)
                ->displayUsingLabels(),

            Select::make(__('Building location type'), 'buildingLocationType')
                ->options(ShippoService::BUILDING_LOCATION_TYPES)
                ->displayUsingLabels(),
        ];
    }
}
