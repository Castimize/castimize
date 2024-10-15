<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class AcceptRejectionAction extends Action implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Accept rejection');
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            // ToDo: Refund customer CRON

            // ToDo: Mail to customer with note Castimize CRON

            $model->note_castimize = $fields->note_castimize;
            $model->accepted_at = now();
            $model->save();
        }

        return ActionResponse::message(__('Rejection successfully accepted'));
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Textarea::make(__('Note Castimize'), 'note_castimize')->default(function ($request) {
                return $request->note_manufacturer;
            }),
        ];
    }
}
