<?php

namespace Castimize\SelectWithOverview\Http\Controllers;

use App\Models\OrderQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class ApiController.
 *
 * @date    06/05/2024
 */
class ApiController extends Controller
{
    /**
     * Load system settings.
     */
    public function getOverviewItem(NovaRequest $request): JsonResponse
    {
        $orderQueue = OrderQueue::with('upload.material')->find($request->id);

        if ($orderQueue === null) {
            return response()->json(['item' => null]);
        }

        return response()->json([
            'item' => $orderQueue->getOverviewItem(),
        ]);
    }

    public function getOverviewFooter(NovaRequest $request): JsonResponse
    {
        $orderQueues = OrderQueue::with('upload.material')->whereIn('id', $request->ids)->get();
        $overviewFooter = OrderQueue::getOverviewFooter($orderQueues);

        return response()->json([
            'footer' => $overviewFooter,
        ]);
    }
}
