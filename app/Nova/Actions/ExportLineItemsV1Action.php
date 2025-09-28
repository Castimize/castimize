<?php

namespace App\Nova\Actions;

use App\Exports\ExportLineItemsV1;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportLineItemsV1Action extends Action
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
        return __('Export items');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $response = Excel::download(new ExportLineItemsV1($models), $fields->filename);

        if (! $response instanceof BinaryFileResponse || $response->isInvalid()) {
            return Action::danger(__('Resource could not be exported.'));
        }

        return ActionResponse::download($fields->filename, $this->getDownloadUrl($response->getFile()->getPathname(), $fields->filename));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make(__('Filename'), 'filename')
                ->help(__('Add filename with extension, like "export.xlsx"')),
        ];
    }

    protected function getDownloadUrl(string $filePath, string $fileName): string
    {
        return URL::temporarySignedRoute(
            'laravel-nova-excel.download',
            now()->addMinutes(1), [
                'path' => encrypt($filePath),
                'filename' => $fileName,
            ]
        );
    }
}
