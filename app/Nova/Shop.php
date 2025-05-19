<?php

namespace App\Nova;

use App\Nova\Actions\EtsyAuthorizationUrlAction;
use App\Nova\Actions\EtsySyncModelsAction;
use App\Traits\Nova\CommonMetaDataTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Shop extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Shop>
     */
    public static $model = \App\Models\Shop::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        return sprintf('%s - %s (%s)', $this->id, $this->shopOwner->customer->name, $this->shopOwner->customer->wp_id);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'shop',
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
            ID::make()
                ->sortable(),

            Text::make(__('Customer'), function () {
                return sprintf('%s (%s)', $this->shopOwner->customer->name, $this->shopOwner->customer->wp_id);
            }),

            BelongsTo::make(__('Shop owner'), 'shopOwner', ShopOwner::class)
                ->onlyOnForms(),

            Select::make(__('Shop'), 'shop')
                ->options(['etsy' => 'Etsy']),

            Boolean::make(__('Active'), 'active')
                ->sortable(),

            Text::make(__('Etsy Shop ID'), function () {
                return $this->shop_oauth['shop_id'] ?? '';
            }),

            Code::make(__('Oauth'), 'shop_oauth')->json()
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return $request->user()->isSuperAdmin();
                }),

            HasMany::make(__('Listings'), 'shopListingModels', ShopListingModel::class),

            HasMany::make(__('Orders'), 'shopOrders', ShopOrder::class),

            new Panel(__('History'), $this->commonMetaData()),
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
        return [];
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
        return [
            EtsyAuthorizationUrlAction::make(),
            EtsySyncModelsAction::make(),
        ];
    }
}
