<?php

namespace App\Http\Controllers;

use App\Models\OrderQueue;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class PoLabelsDownloadController extends Controller
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
        $orderQueues = OrderQueue::with(['upload', 'order'])
            ->where('manufacturer_id', $manufacturerId)
            ->whereIn('id', $data['order_queue_ids'])
            ->get();
        $count = count($orderQueues);

        return Pdf::view('nova-pdf.po-label', compact('orderQueues', 'count'))
            ->download($request->filename);
    }
}
