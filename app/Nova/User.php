<?php

namespace App\Nova;


use App\Traits\Nova\CommonMetaDataTrait;
use DateTimeZone;
use Gldrenthe89\NovaStringGeneratorField\NovaGeneratePassword;
use Gldrenthe89\NovaStringGeneratorField\NovaGenerateString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules;
use Jeffbeltran\SanctumTokens\SanctumTokens;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Sereny\NovaPermissions\Nova\Permission;
use Sereny\NovaPermissions\Nova\Role;

class User extends Resource
{
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function title()
    {
        return sprintf('%s (%s %s)', $this->username, $this->first_name, $this->last_name);
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'email',
        'username',
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
     * @param NovaRequest $request
     * @param $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        /**
         * @var $user \App\Models\User
         */
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $query->where('id', '>', 1);
        }
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$sort), reset(static::$sort));
        }

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $timezones = DateTimeZone::listIdentifiers();
        return [
            ID::make()->sortable(),

            Number::make(__('Wordpress ID'), 'wp_id')
                ->hideFromIndex(),

            Avatar::make(__('Avatar'), 'avatar')
                ->maxWidth(50)
                ->disk(env('FILESYSTEM_DISK'))
                ->path('admin/users'),

            Text::make(__('Role'), function () {
                return $this->getRoleNames()->first();
            })->exceptOnForms(),

            Text::make(__('Name'), function () {
                return sprintf('%s %s', $this->first_name, $this->last_name);
            })->exceptOnForms(),

            Text::make(__('First name'), 'first_name')
                ->sortable()
                ->required()
                ->rules('max:255')
                ->onlyOnForms(),

            Text::make(__('Last name'), 'last_name')
                ->sortable()
                ->required()
                ->rules('max:255')
                ->onlyOnForms(),

            NovaGenerateString::make(__('Username'), 'username')
                ->length(12)
                ->excludeRules(['symbols']),

            Text::make(__('Email'))
                ->sortable()
                ->required()
                ->rules('email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            NovaGeneratePassword::make(__('Password'), 'password')
                ->onlyOnForms()
                ->length(24)
                ->required()
                ->updateRules('nullable'),

            Select::make(__('Timezone'), 'timezone')
                ->options(array_combine($timezones, $timezones))
                ->default(config('app.timezone')),

            SanctumTokens::make(),

            MorphToMany::make(__('Roles'), 'roles', Role::class),
            MorphToMany::make(__('Permissions'), 'permissions', Permission::class),

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
}
