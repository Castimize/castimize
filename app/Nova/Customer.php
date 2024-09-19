<?php

namespace App\Nova;

use App\Nova\Filters\ShowDeleted;
use App\Traits\Nova\CommonMetaDataTrait;
use Devloops\PhoneNumber\PhoneNumber;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Saumini\Count\RelationshipCount;
use Wame\TelInput\TelInput;

class Customer extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Customer>
     */
    public static $model = \App\Models\Customer::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'last_name' => 'asc',
        'first_name' => 'asc',
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

            BelongsTo::make(__('Country'), 'country')
                ->sortable(),

            Text::make(__('Name'), function () {
                return sprintf('%s %s', $this->first_name, $this->last_name);
            })->onlyOnIndex(),

            BelongsTo::make(__('User'), 'user')
                ->sortable(),

            Text::make(__('First name'), 'first_name')
                ->hideFromIndex(),
            Text::make(__('Last name'), 'last_name')
                ->hideFromIndex(),

            Email::make(__('Email'), 'email')
                ->sortable(),

            TelInput::make(__('Phone'), 'phone')
                ->hideFromIndex(),

            RelationshipCount::make(__('Orders'), 'orders')
                ->onlyOnIndex()
                ->sortable(),

            Textarea::make(__('Comments'), 'comments')
                ->hideFromIndex(),
            Text::make(__('IP address'), 'visitor')
                ->readonly()
                ->sizeOnDetail('w-1/3')
                ->onlyOnDetail(),
            Text::make(__('Platform'), 'device_platform')
                ->readonly()
                ->sizeOnDetail('w-1/3')
                ->onlyOnDetail(),
            Text::make(__('Type'), 'device_type')
                ->readonly()
                ->sizeOnDetail('w-1/3')
                ->onlyOnDetail(),
            DateTime::make(__('Last active'), 'last_active')
                ->sortable()
                ->onlyOnDetail(),

            MorphToMany::make(__('Addresses'), 'addresses')
                ->fields(function ($request, $relatedModel) {
                    return [
                        Boolean::make(__('Default billing'), 'default_billing')
                            ->sortable(),
                        Boolean::make(__('Default shipping'), 'default_shipping')
                            ->sortable(),
                        Text::make(__('Contact name'), 'contact_name'),
                        TelInput::make(__('Phone'), 'phone'),
                        Email::make(__('Email'), 'email'),
                    ];
                })
                ->showCreateRelationButton(),

            HasMany::make(__('Orders'), 'orders')
                ->hideFromIndex(),

            HasMany::make(__('Models'), 'models')
                ->hideFromIndex(),

//            HasMany::make(__('Invoices'), 'invoices')
//                ->hideFromIndex(),
//
//            HasMany::make(__('Complaints'), 'complaints')
//                ->hideFromIndex(),

            new Panel(__('History'), $this->commonMetaData(true, false, false, false)),
        ];
    }

    // Overwrite the indexQuery to include relationship count
    public static function indexQuery(NovaRequest $request, $query)
    {
        // Give relationship name as alias else Laravel will name it as comments_count
        return $query->withCount('orders as orders');
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
            new ShowDeleted(),
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
}
