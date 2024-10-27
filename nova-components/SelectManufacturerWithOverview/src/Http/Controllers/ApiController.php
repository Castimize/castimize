<?php

namespace Castimize\SelectManufacturerWithOverview\Http\Controllers;

use App\Models\OrderQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class ApiController.
 *
 * @package Castimize\SelectWithOverview\Http\Controllers
 * @date    06/05/2024
 * @author  Abdullah Al-Faqeir <abdullah@devloops.net>
 */
class ApiController extends Controller
{
    /**
     * Load system settings.
     *
     * @param NovaRequest $request
     *
     * @return JsonResponse
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
