<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class DownloadModelsAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Download models');
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
        $zipFileName = sprintf('models-%s.zip', time());
        $orderQueueIds = [];

        foreach ($models as $model) {
            $orderQueueIds[] = $model->id;
        }

        return ActionResponse::download($zipFileName, $this->getDownloadUrl($zipFileName, $orderQueueIds));
    }

    /**
     * Get the fields available on the action.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }

    protected function getDownloadUrl(string $fileName, array $orderQueueIds): string
    {
        return URL::temporarySignedRoute('models.download', now()->addMinutes(5), [
            'manufacturer_id' => encrypt(auth()->user()->manufacturer->id),
            'order_queue_ids' => $orderQueueIds,
            'filename' => $fileName,
        ]);
    }
}
