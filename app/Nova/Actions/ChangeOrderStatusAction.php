<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChangeOrderStatusAction extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     */
    public function name(): string
    {
        return __('Change status');
    }

    /**
     * Perform the action on the given models.
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        foreach ($models as $model) {
            $model->update([
                'status' => $fields->status,
            ]);
        }

        $count = $models->count();

        return ActionResponse::message(
            trans_choice(
                '{1} Order status has been updated.|[2,*] :count order statuses have been updated.',
                $count,
                ['count' => $count]
            )
        );
    }

    /**
     * Get the fields available on the action.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make(__('Status'), 'status')
                ->options([
                    'pending' => __('Pending'),
                    'processing' => __('Processing'),
                    'almost-overdue' => __('Almost overdue'),
                    'overdue' => __('Overdue'),
                    'completed' => __('Completed'),
                    'canceled' => __('Canceled'),
                ])
                ->rules('required'),
        ];
    }
}
