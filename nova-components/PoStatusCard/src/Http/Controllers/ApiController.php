<?php

namespace Castimize\PoStatusCard\Http\Controllers;

use App\Models\OrderQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class ApiController.
 *
 * @package Castimize\SelectWithOverview\Http\Controllers
 * @date    06/05/2024
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
    public function getTotals(NovaRequest $request): JsonResponse
    {
        $totals = [];

        foreach ($request->statuses as $slug => $status) {
            $query = OrderQueue::with(['orderQueueStatuses'])
                ->whereHasLastOrderQueueStatus($slug);

            if (auth()->user()->hasRole('manufacturer')) {
                $query->where('manufacturer_id', auth()->user()->manufacturer->id);
            }
            $totals[$slug] = $query->count();
        }

        return response()->json([
            'totals' => $totals,
        ]);
    }
}
