<?php

namespace App\Http\Controllers;

use App\Models\OrderQueue;
use Illuminate\Http\Request;
use STS\ZipStream\Facades\Zip;

class ModelsDownloadController extends Controller
{

    public function __invoke(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        $data = $request->validate([
            'manufacturer_id' => 'required',
            'order_queue_ids' => 'required',
            'filename' => 'required',
        ]);

        $manufacturerId = decrypt($data['manufacturer_id']);
        $orderQueues = OrderQueue::with('upload')
            ->where('manufacturer_id', $manufacturerId)
            ->whereIn('id', $data['order_queue_ids'])
            ->get();

        $zip = Zip::create($data['filename']);

        foreach ($orderQueues as $orderQueue) {
            $rawFileName = str_replace('wp-content/uploads/p3d/', '', $orderQueue->upload->file_name);
            $zip->add(
                sprintf('%s/%s', env('AWS_URL'), $orderQueue->upload->file_name),
                sprintf('%s-%s', $orderQueue->id, $rawFileName)
            );
        }

        return $zip;
    }
}
