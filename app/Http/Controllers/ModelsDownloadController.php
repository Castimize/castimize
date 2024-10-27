<?php

namespace App\Http\Controllers;

use App\Models\OrderQueue;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use STS\ZipStream\Facades\Zip;

class ModelsDownloadController extends Controller
{
    use ValidatesRequests;

    /**
     * Handle a models download.
     *
     * @param Request $request
     * @throws ValidationException
     */
    public function __invoke(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        $data = $this->validate($request, [
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
            $zip->add(sprintf('%s/%s', env('CLOUDFLARE_R2_URL'), $orderQueue->upload->file_name));
        }

        return $zip;
    }
}
