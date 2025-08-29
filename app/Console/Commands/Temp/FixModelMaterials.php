<?php

namespace App\Console\Commands\Temp;

use App\Models\Customer;
use Illuminate\Console\Command;

class FixModelMaterials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:fix-model-materials';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add model materials for customer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customers = Customer::withTrashed()->get();

        foreach ($customers as $customer) {
            $models = $customer->models()->withTrashed()->get();
            foreach ($models as $model) {
                $model->materials()->attach($model->material_id);
            }
            //            $uniqueModels = [];
            //            foreach ($customer->models->sortBy('name')->sortBy('id') as $model) {
            //                $key = sprintf('%s_%s', $model->file_name, $model->model_scale);
            //                if (! array_key_exists($key, $uniqueModels)) {
            //                    $uniqueModels[$key] = [
            //                        'ids' => [],
            //                        'names' => [],
            //                        'materials' => [],
            //                    ];
            //                }
            //
            //                $uniqueModels[$key]['ids'][] = $model->id;
            //                if (! in_array($model->name, $uniqueModels[$key]['names'], true)) {
            //                    $uniqueModels[$key]['names'][] = $model->name;
            //                }
            //                if (! in_array($model->material_id, $uniqueModels[$key]['materials'], true)) {
            //                    $uniqueModels[$key]['materials'][] = $model->material_id;
            //                }
            //            }
            //            dd($uniqueModels);
        }
    }
}
