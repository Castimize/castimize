<?php

namespace App\Nova;

use App\Traits\Nova\CommonMetaDataTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Tomodo531\FilterableFilters\FilterableFilters;

class LogRequest extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\LogRequest>
     */
    public static $model = \App\Models\LogRequest::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return sprintf('%s-%s %s', $this->id, $this->type, $this->path_info);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'type',
        'path_info',
        'request_uri',
        'http_code',
        'method',
        'user_agent',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'id' => 'desc',
    ];

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

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

            Text::make(__('Type'), 'type')
                ->sortable()
                ->rules('max:255'),

            Text::make(__('Http code'), 'http_code')
                ->sortable()
                ->rules('max:255'),

            Text::make(__('Method'), 'method')
                ->sortable()
                ->rules('max:255'),

            Text::make(__('Path info'), 'path_info')
                ->sortable()
                ->rules('max:255'),

            Text::make(__('Request uri'), 'request_uri')
                ->hideFromIndex()
                ->rules('max:255'),

            Text::make(__('Remote address'), 'remote_address')
                ->rules('max:255'),

            Text::make(__('User agent'), 'user_agent')
                ->rules('max:255'),

            Code::make(__('Server'), 'server')
                ->json(),

            Code::make(__('Headers'), 'headers')
                ->json(),

            Code::make(__('Request'), 'request')
                ->json(),

            Code::make(__('Response'), 'response')
                ->json(),

            new Panel(__('History'), $this->commonMetaData(true, true, false, false)),
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
            FilterableFilters::make(\App\Models\LogRequest::class)
                ->fields([
                    'method',
                    'http_code',
                    'user_agent',
                ]),
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
