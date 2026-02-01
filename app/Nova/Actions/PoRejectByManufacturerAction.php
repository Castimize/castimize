<?php

namespace App\Nova\Actions;

use App\Models\OrderQueue;
use App\Models\RejectionReason;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class PoRejectByManufacturerAction extends Action
{
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return mixed
     */
    public function name()
    {
        return __('Reject');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
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
        $fileName = sprintf(
            '%s-rejection-%s.%s',
            auth()->user()->manufacturer->id,
            time(),
            $fields->photo->extension()
        );
        $fullFileName = 'admin/rejections/'.$fileName;
        Storage::disk('r2_private')->putFileAs('admin/rejections/', $fields->photo, $fileName);

        foreach ($models as $model) {
            $rejectionReason = RejectionReason::find($fields->rejection_reason_id);
            $model->rejection()->create([
                'manufacturer_id' => auth()->user()->manufacturer->id,
                'order_id' => $model->order_id,
                'upload_id' => $model->upload_id,
                'rejection_reason_id' => $fields->rejection_reason_id,
                'reason_manufacturer' => $rejectionReason->reason,
                'note_manufacturer' => $fields->note_manufacturer,
                'photo' => $fullFileName,
            ]);

            $orderQueuesService->setStatus($model, 'rejection-request');
        }

        return ActionResponse::message(__('PO\'s successfully rejected.'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make(__('Reason'), 'rejection_reason_id')
                ->options(
                    RejectionReason::all()->pluck('reason', 'id')->toArray()
                )->displayUsingLabels(),

            Textarea::make(__('Extra note'), 'note_manufacturer'),

            Image::make(__('Photo'), 'photo')
                ->disk('r2_private')
                ->path('admin/rejections'),
        ];
    }
}
