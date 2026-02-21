<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Complaint extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Complaint>
     */
    public static $model = \App\Models\Complaint::class;

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
        'reason',
        'complaintReason.reason',
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
     * @var string[]
     */
    public static $with = [
        'customer',
        'complaintReason',
        'upload',
        'order',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Customer'), 'customer')
                ->sortable(),

            BelongsTo::make(__('Complaint reason'), 'complaintReason')
                ->hideFromIndex()
                ->sortable(),

            BelongsTo::make(__('Upload'), 'upload')
                ->sortable(),

            BelongsTo::make(__('Order'), 'order')
                ->hideFromIndex()
                ->sortable(),

            DateTime::make(__('Denied at'), 'deny_at')
                ->sortable(),

            DateTime::make(__('Reprint at'), 'reprint_at')
                ->sortable(),

            DateTime::make(__('Refund at'), 'refund_at')
                ->sortable(),

            Text::make(__('Reason'), 'reason')
                ->sortable(),

            Textarea::make(__('Description'), 'description')
                ->hideFromIndex(),

            Image::make(__('Image'), 'image')
                ->disk('r2_private')
                ->path('admin/complaints'),
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
     */
    public function filters(NovaRequest $request)
    {
        return [

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
