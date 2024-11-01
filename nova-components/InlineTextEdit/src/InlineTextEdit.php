<?php

namespace Castimize\InlineTextEdit;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class InlineTextEdit extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'inline-text-edit';

    protected function resolveAttribute($resource, $attribute)
    {
        $this->withMeta(['resourceId' => $resource->getKey()]);
        return parent::resolveAttribute($resource, $attribute);
    }

    public function resolve($resource, $attribute = null)
    {
        parent::resolve($resource, $attribute);

        /** @var NovaRequest */
        $novaRequest = app()->make(NovaRequest::class);
        if ($novaRequest->isFormRequest()) {
            $this->component = 'text-field';
        }
    }

    public function modelClass(string $modelClass): InlineTextEdit
    {
        return $this->withMeta(['modelClass' => $modelClass]);
    }

    public function maxWidth(int|null $maxWidthPx = null): InlineTextEdit
    {
        return $this->withMeta(['maxWidth' => $maxWidthPx]);
    }
}
