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

class DownloadPoLabelsAction extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Download PO labels');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $labelFileName = sprintf('po-labels-%s.pdf', time());
        $orderQueueIds = [];

        foreach ($models as $model) {
            $orderQueueIds[] = $model->id;
        }

        return ActionResponse::download($labelFileName, $this->getDownloadUrl($labelFileName, $orderQueueIds));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }

    protected function getDownloadUrl(string $fileName, array $orderQueueIds): string
    {
        return URL::temporarySignedRoute(
            'po.labels.download',
            now()->addMinutes(5), [
                'manufacturer_id' => encrypt(auth()->user()->manufacturer->id),
                'order_queue_ids' => $orderQueueIds,
                'filename' => $fileName,
            ]
        );
    }
}
