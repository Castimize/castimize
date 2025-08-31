<?php

namespace App\Nova;

use App\Enums\Admin\PaymentFeeTypesEnum;
use App\Enums\Admin\PaymentMethodsEnum;
use App\Traits\Nova\CommonMetaDataTrait;
use Castimize\ColumnToggler\ColumnTogglerTrait;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class PaymentFee extends Resource
{
//    use ColumnTogglerTrait;
    use CommonMetaDataTrait;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\PaymentFee>
     */
    public static $model = \App\Models\PaymentFee::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        $paymentMethodsOptions = PaymentMethodsEnum::options();

        return ! empty($this->payment_method) && array_key_exists($this->payment_method, $paymentMethodsOptions) ? $paymentMethodsOptions[$this->payment_method] : $this->id;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'payment_method',
        'currency_code',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $sort = [
        'payment_method' => 'asc',
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

            Select::make(__('Payment method'), 'payment_method')
                ->options(PaymentMethodsEnum::options())
                ->onlyOnForms()
                ->sortable(),

            Text::make(__('Payment method'), function () {
                return $this->payment_method ? PaymentMethodsEnum::options()[$this->payment_method] : $this->payment_method;
            })
                ->exceptOnForms()
                ->sortable(),

            Select::make(__('Type'), 'type')
                ->options(PaymentFeeTypesEnum::options())
                ->default(PaymentFeeTypesEnum::FIXED->value)
                ->sortable(),

            Select::make(__('Currency'), 'currency_code')->options(function () {
                return array_filter(\App\Models\Currency::pluck('name', 'code')->all());
            })
                ->default('USD')
                ->hideFromIndex()
                ->help(__('This currency will be used for below fee'))
                ->dependsOn(
                    ['type'],
                    function (Select $field, NovaRequest $request, FormData $formData) {
                        if ($formData->type === PaymentFeeTypesEnum::FIXED->value) {
                            $field->show()->required();
                        } else {
                            $field->hide()->required(false);
                        }
                    }
                ),

            \Laravel\Nova\Fields\Currency::make(__('Fee'), 'fee')
                ->min(0)
                ->step(0.01)
                ->locale(config('app.format_locale'))
                ->dependsOn(
                    ['currency_code', 'type'],
                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
                        if ($formData->type === PaymentFeeTypesEnum::FIXED->value) {
                            $field->show()->required();
                            $field->currency($formData->currency_code);
                        } else {
                            $field->hide()->required(false);
                        }
                    }
                )
                ->onlyOnForms(),

            Number::make(__('Fee'), 'fee')
                ->help(__('In percentage'))
                ->step(0.01)
                ->dependsOn(
                    ['type'],
                    function (Number $field, NovaRequest $request, FormData $formData) {
                        if ($formData->type === PaymentFeeTypesEnum::PERCENTAGE->value) {
                            $field->show()->required();
                        } else {
                            $field->hide()->required(false);
                        }
                    }
                )
                ->onlyOnForms(),

            Text::make(__('Fee'), function () {
                return $this->fee && $this->type === PaymentFeeTypesEnum::FIXED->value ? currencyFormatter((float) $this->fee, $this->currency_code) : $this->fee.'%';
            })
                ->exceptOnForms()
                ->sortable(),

            //            \Laravel\Nova\Fields\Currency::make(__('Minimum fee'), 'minimum_fee')
            //                ->min(0)
            //                ->step(0.01)
            //                ->locale(config('app.format_locale'))
            //                ->dependsOn(
            //                    ['currency_code', 'type'],
            //                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
            //                        if ($formData->type === PaymentFeeTypesEnum::FIXED->value) {
            //                            $field->show()->required(false);
            //                            $field->currency($formData->currency_code);
            //                        } else {
            //                            $field->hide()->required(false);
            //                        }
            //                    }
            //                )
            //                ->onlyOnForms(),
            //
            //            Number::make(__('Minimum fee'), 'minimum_fee')
            //                ->help(__('In percentage'))
            //                ->step(0.01)
            //                ->dependsOn(
            //                    ['type'],
            //                    function (Number $field, NovaRequest $request, FormData $formData) {
            //                        if ($formData->type === PaymentFeeTypesEnum::PERCENTAGE->value) {
            //                            $field->show()->required(false);
            //                        } else {
            //                            $field->hide()->required(false);
            //                        }
            //                    }
            //                )
            //                ->onlyOnForms(),
            //
            //            Text::make(__('Minimum fee'), function () {
            //                return $this->minimum_fee && $this->type === PaymentFeeTypesEnum::FIXED->value ?
            //                    currencyFormatter((float)$this->minimum_fee, $this->currency_code) :
            //                    $this->minimum_fee . ($this->minimum_fee > 0.00 ? '%' : '');
            //            })
            //                ->exceptOnForms()
            //                ->sortable(),
            //
            //            \Laravel\Nova\Fields\Currency::make(__('Maximum fee'), 'maximum_fee')
            //                ->min(0)
            //                ->step(0.01)
            //                ->locale(config('app.format_locale'))
            //                ->dependsOn(
            //                    ['currency_code', 'type'],
            //                    function (\Laravel\Nova\Fields\Currency $field, NovaRequest $request, FormData $formData) {
            //                        if ($formData->type === PaymentFeeTypesEnum::FIXED->value) {
            //                            $field->show()->required(false);
            //                            $field->currency($formData->currency_code);
            //                        } else {
            //                            $field->hide()->required(false);
            //                        }
            //                    }
            //                )
            //                ->onlyOnForms(),
            //
            //            Number::make(__('Maximum fee'), 'maximum_fee')
            //                ->help(__('In percentage'))
            //                ->step(0.01)
            //                ->dependsOn(
            //                    ['type'],
            //                    function (Number $field, NovaRequest $request, FormData $formData) {
            //                        if ($formData->type === PaymentFeeTypesEnum::PERCENTAGE->value) {
            //                            $field->show()->required(false);
            //                        } else {
            //                            $field->hide()->required(false);
            //                        }
            //                    }
            //                )
            //                ->onlyOnForms(),
            //
            //            Text::make(__('Maximum fee'), function () {
            //                return $this->maximum_fee && $this->type === PaymentFeeTypesEnum::FIXED->value ?
            //                    currencyFormatter((float)$this->maximum_fee, $this->currency_code) :
            //                    $this->maximum_fee . ($this->maximum_fee > 0.00 ? '%' : '');
            //            })
            //                ->exceptOnForms()
            //                ->sortable(),

            new Panel(__('History'), $this->commonMetaData(false, false, false, false)),
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
