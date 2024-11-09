<?php

namespace Castimize\SelectManufacturerWithOverview;

use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class SelectManufacturerWithOverview extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'select-manufacturer-with-overview';

    protected bool $saveAsJSON = false;

    /**
     * Set the options.
     *
     * @param  array $options
     * @return $this
     */
    public function options(array $options)
    {
        if (is_callable($options)) {
            $options = call_user_func($options);
        }
        $options = collect($options ?: []);

        return $this->withMeta([
            'options' => $options->map(function ($option) {
                return ['label' => $option['label'], 'value' => $option['value'], 'group' => $option['group']];
            })->values()->all(),
        ]);
    }

    public function overviewHeaders(array $overviewHeaders)
    {
        return $this->withMeta(['overviewHeaders' => $overviewHeaders]);
    }

    public function shouldShowColumnBorders(bool $showColumnBorders = true)
    {
        return $this->withMeta(['shouldShowColumnBorders' => $showColumnBorders]);
    }

    public function shouldShowCheckboxes(bool $shouldShowCheckboxes = true)
    {
        return $this->withMeta(['shouldShowCheckboxes' => $shouldShowCheckboxes]);
    }

    protected function resolveAttribute($resource, $attribute)
    {
        $value = data_get($resource, str_replace('->', '.', $attribute));
        $saveAsJson = $this->shouldSaveAsJson($resource, $attribute);

        if ($value instanceof Collection) {
            return $value;
        }
        if ($saveAsJson) {
            return $value;
        }
        return is_array($value) || is_object($value) ? (array) $value : json_decode($value);
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        $value = $request->input($requestAttribute) ?: null;
        $saveAsJson = $this->shouldSaveAsJson($model, $attribute);

        $value = is_null($value) ? ($this->nullable ? $value : $value = []) : $value;
        $value = is_array($value) ? $value : explode(',', $value);
//        if (count($value) === 0) {
//            throw new \Exception(__('Please select PO\'s'));
//        }
        $model->{$attribute} = ($saveAsJson || is_null($value)) ? $value : json_encode($value);
    }

    private function shouldSaveAsJson($model, $attribute)
    {
        if (!empty($model) && !is_array($model) && method_exists($model, 'getCasts')) {
            $casts = $model->getCasts();
            $isCastedToArray = ($casts[$attribute] ?? null) === 'array';
            return $this->saveAsJSON || $isCastedToArray;
        }
        return false;
    }

    public function resolveForAction($request)
    {
        if (!is_null($this->value)) {
            return;
        }

        if ($defaultValue = $this->resolveDefaultValue($request)) {
            $this->value = $defaultValue;
        }
    }

    public function resolveDefaultValue(NovaRequest $request)
    {
        if (!is_null($this->value)) {
            return parent::resolveDefaultValue($request);
        }

        if ($request->isCreateOrAttachRequest() || $request->isActionRequest()) {
            if ($this->defaultCallback instanceof Closure) {
                $defaultValue = call_user_func($this->defaultCallback, $request);
            } else {
                $defaultValue = $this->defaultCallback;
            }

            if (is_null($defaultValue)) {
                return null;
            }

            $defaultValue = is_countable($defaultValue) ? collect($defaultValue) : collect([$defaultValue]);
            $defaultValue = $defaultValue->filter(function ($val) {
                if (empty($val)) {
                    return false;
                }
                if (is_object($val) && $class = get_class($val)) {
                    if ($class === 'Laravel\Nova\Support\UndefinedValue') {
                        return false;
                    }
                }
                return true;
            });

            if ($defaultValue->isEmpty()) {
                return null;
            }

            return $defaultValue;
        }

        return parent::resolveDefaultValue($request);
    }

    /**
     * Allows the field to save an actual JSON array to a SQL JSON column.
     *
     * @param bool $saveAsJSON
     * @return self
     **/
    public function saveAsJSON(bool $saveAsJSON = true)
    {
        $this->saveAsJSON = $saveAsJSON;
        return $this;
    }
}
