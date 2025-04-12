<?php

namespace App\Nova\Settings\Shipping;

use App\Enums\Shippo\ShippoVatTypesEnum;
use App\Services\Shippo\ShippoService;
use Devloops\NovaSystemSettings\Contracts\SystemSettings;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;


class GeneralSettings extends SystemSettings
{
    public ?string $upsCarrierAccount;
    public ?string $eoriNumber;
    public ?string $eoriNumberGb;
    public ?string $taxNumber;
    public ?string $taxType;
    public ?string $contentsExplanation;
    public ?string $certifySigner;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function title(): string
    {
        return __('General Settings');
    }

    public static function icon(): string
    {
        return 'cog';
    }

    public static function name(): string
    {
        return 'general_settings';
    }

    public static function fields(): array
    {
        return [
            Text::make(__('UPS carrier account ID'), 'upsCarrierAccount'),

            Text::make(__('EORI number'), 'eoriNumber'),

            Text::make(__('EORI number GB'), 'eoriNumberGb'),

            Text::make(__('Tax number'), 'taxNumber'),

            Select::make(__('Tax type'), 'taxType')
                ->default('VAT')
                ->options(ShippoVatTypesEnum::values()),

            Text::make(__('Contents explanation'), 'contentsExplanation'),

            Text::make(__('Certify signer'), 'certifySigner'),
        ];
    }
}
