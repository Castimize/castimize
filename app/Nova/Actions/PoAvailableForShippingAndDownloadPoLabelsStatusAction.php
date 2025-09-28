<?php

namespace App\Nova\Actions;

use App\Models\OrderQueue;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoAvailableForShippingAndDownloadPoLabelsStatusAction extends Action
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
        return __('Produced and download PO labels');
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

        $orderQueuesService = new OrderQueuesService;
        foreach ($models as $model) {
            $hasEndStatus = [];
            /** @var $model OrderQueue */
            if ($model->getLastStatus()->end_status) {
                $hasEndStatus[] = $model->id;
            }

            if (count($hasEndStatus) > 0) {
                return ActionResponse::danger(
                    __('You selected PO\'s :pos which cannot be changed anymore, because it already has an end status', [
                        'pos' => implode(', ', $hasEndStatus),
                    ])
                );
            }
        }
        foreach ($models as $model) {
            $orderQueuesService->setStatus($model, 'available-for-shipping');
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
