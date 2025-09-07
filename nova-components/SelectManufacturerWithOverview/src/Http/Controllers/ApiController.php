<?php

namespace Castimize\SelectManufacturerWithOverview\Http\Controllers;

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
        $orderQueue = OrderQueue::find($request->id);
        $overviewItem = $orderQueue->getOverviewItem(false);

        return response()->json([
            'item' => $overviewItem,
        ]);
    }

    public function getOverviewFooter(NovaRequest $request): JsonResponse
    {
        $orderQueues = OrderQueue::whereIn('id', $request->ids)->get();
        $overviewFooter = OrderQueue::getOverviewFooter($orderQueues, false);

        return response()->json([
            'footer' => $overviewFooter,
        ]);
    }
}
