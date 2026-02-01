<?php

namespace Castimize\PoStatusCard\Http\Controllers;

use App\Models\OrderQueue;
use Illuminate\Database\Eloquent\Builder;
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
    public function getTotals(NovaRequest $request): JsonResponse
    {
        $totals = [];

        foreach ($request->statuses as $slug => $status) {
            $query = OrderQueue::with(['order', 'orderQueueStatuses'])
                ->whereHasLastOrderQueueStatus($slug)
                ->whereHas('order', function (Builder $query) {
                    $query->removeTestEmailAddresses('email')
                        ->removeTestCustomerIds('customer_id');
                });

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
