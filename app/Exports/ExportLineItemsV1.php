<?php

namespace App\Exports;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportLineItemsV1 implements FromCollection, ShouldAutoSize
{
    private Collection $data;

    public function __construct(
        private $lineItems,
    ) {
        $this->data = collect();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $this->addHeadingRow();

        $this->addRows();

        return $this->data;
    }

    private function addHeadingRow()
    {
        $this->data->push([
            'Order',
            'PO',
            'Material',
            '# Model parts',
            '# Total parts',
            'Model volume',
            'Total volume',
            'Model surface area',
            'Total surface area',
            'Model costs',
            'Total costs',
            'Entry date',
            'File',
        ]);
    }

    private function addRows()
    {
        foreach ($this->lineItems as $lineItem) {
            try {
                $this->data->push([
                    $lineItem->order->order_number,
                    $lineItem->id,
                    $lineItem->upload->material_name,
                    $lineItem->upload->model_parts,
                    $lineItem->upload->model_parts * $lineItem->upload->quantity,
                    $lineItem->upload->model_volume_cc,
                    $lineItem->upload->model_volume_cc * $lineItem->upload->quantity,
                    $lineItem->upload->model_surface_area_cm2,
                    $lineItem->upload->model_surface_area_cm2 * $lineItem->upload->quantity,
                    $lineItem->manufacturing_costs,
                    $lineItem->manufacturing_costs / $lineItem->upload->quantity,
                    $lineItem->manufacturing_costs,
                    sprintf('%s/%s', config('app.site_url'), $lineItem->upload->file_name),
                ]);
            } catch (Exception $e) {
                Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            }
        }
    }
}
