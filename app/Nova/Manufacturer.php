<?php

namespace App\Nova;


use App\Traits\Nova\CommonMetaDataTrait;
use DigitalCreative\ColumnToggler\ColumnTogglerTrait;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Wame\TelInput\TelInput;

class Manufacturer extends Resource
{
    use ColumnTogglerTrait, CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Manufacturer>
     */
    public static $model = \App\Models\Manufacturer::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'id' => 'desc',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Name'), 'name')
                ->required(),

            BelongsTo::make(__('User'), 'user'),

            Text::make(__('COC number'), 'coc_number'),

            Text::make(__('Vat number'), 'vat_number'),

            Text::make(__('IBAN'), 'iban'),

            Text::make(__('Contact name'), 'contact_name_1'),

            Text::make(__('Contact name 2'), 'contact_name_2'),

            TelInput::make(__('Phone'), 'phone_1')
                ->sortable(),

            Email::make(__('Email'), 'email')
                ->sortable(),

            Email::make(__('Billing email'), 'billing_email')
                ->sortable(),

            Textarea::make(__('Comments'), 'comments'),

            BelongsTo::make(__('Country'), 'country')
                ->sortable(),

            BelongsTo::make(__('Language'), 'language')
                ->hideFromIndex()
                ->sortable(),

            BelongsTo::make(__('Currency'), 'currency')
                ->hideFromIndex()
                ->sortable(),

            new Panel(__('Address'), $this->addressFields()),

            HasMany::make(__('Line items'), 'orderQueues', OrderQueue::class),

            HasMany::make(__('Shipments'), 'shipments', ManufacturerShipment::class),

            HasMany::make(__('Costs'), 'costs', ManufacturerCost::class),

            new Panel(__('History'), $this->commonMetaData(false, false, false, false)),
        ];
    }

    /**
     * Get the fields displayed by the resource on index page.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fieldsForIndex(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Name'), 'name')
                ->sortable(),

            BelongsTo::make(__('City'), 'city')
                ->onlyOnIndex()
                ->sortable(),

            BelongsTo::make(__('Country'), 'country')
                ->sortable(),

            TelInput::make(__('Phone'), 'phone_1')
                ->sortable(),

            Email::make(__('Email'), 'email')
                ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [

        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * @return array
     */
    protected function addressFields(): array
    {
        return [
            Text::make(__('Address line 1'), 'address_line1')
                ->required()
                ->sortable(),

            Text::make(__('Address line 2'), 'address_line2')
                ->required()
                ->sortable(),

            Text::make(__('Postal code'), 'postal_code')
                ->required()
                ->sortable(),

            BelongsTo::make(__('City'))
                ->showCreateRelationButton()
                ->sortable(),

            BelongsTo::make(__('State'))
                ->showCreateRelationButton()
                ->hideFromIndex()
                ->sortable(),
        ];
    }
}
