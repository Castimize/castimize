<?php

namespace App\Nova;

use App\Nova\Filters\CustomerHasOrdersFilter;
use App\Traits\Nova\CommonMetaDataTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
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
        return sprintf('%s (%s)', $this->name, $this->wp_id);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'first_name',
        'last_name',
        'company',
        'email',
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
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->whereNotNull('first_name')
                    ->where('first_name', '!=', '');
            })
                ->where(function (Builder $query) {
                    $query->whereNotNull('last_name')
                        ->where('last_name', '!=', '');
                });
        })->withCount('orders');

        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Number::make(__('Wordpress ID'), 'wp_id')
                ->hideFromIndex(),

            BelongsTo::make(__('Country'), 'country')
                ->sortable(),

            Text::make(__('Name'), function () {
                return $this->name;
            })->exceptOnForms(),

            BelongsTo::make(__('User'), 'user')
                ->nullable()
                ->sortable(),

            Text::make(__('First name'), 'first_name')
                ->onlyOnForms(),

            Text::make(__('Last name'), 'last_name')
                ->onlyOnForms(),

            Text::make(__('Company'), 'company'),

            Email::make(__('Email'), 'email')
                ->sortable(),

            TelInput::make(__('Phone'), 'phone')
                ->hideFromIndex(),

            Text::make(__('Vat number'), 'vat_number')
                ->hideFromIndex(),

            Number::make(__('# Orders'), 'orders_count')
                ->onlyOnIndex()
                ->sortable(),

            Textarea::make(__('Comments'), 'comments')
                ->hideFromIndex(),

            Text::make(__('IP address'), 'visitor')
                ->readonly()
                ->onlyOnDetail(),

            Text::make(__('Platform'), 'device_platform')
                ->readonly()
                ->onlyOnDetail(),

            Text::make(__('Type'), 'device_type')
                ->readonly()
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

                        Text::make(__('Company'), 'company'),

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

            HasMany::make(__('Complaints'), 'complaints')
                ->hideFromIndex(),

            new Panel(__('History'), $this->commonMetaData(true, false, false, false)),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     * @throws Exception
     */
    public function filters(NovaRequest $request)
    {
        return [
            CustomerHasOrdersFilter::make(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
