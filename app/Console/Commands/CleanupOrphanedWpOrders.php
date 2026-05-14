<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupOrphanedWpOrders extends Command
{
    protected $signature = 'castimize:cleanup-orphaned-wp-orders
                            {--dry-run : Check only, do not delete anything}';

    protected $description = 'Delete local orders whose wp_id no longer exists in WordPress (for use after DB sync without matching WP sync)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in dry-run mode — nothing will be deleted.');
        }

        $orders = Order::withTrashed()
            ->whereNotNull('wp_id')
            ->orderBy('id')
            ->get(['id', 'wp_id', 'order_number']);

        $this->info("Checking {$orders->count()} orders against WordPress...");

        $totalMissing = 0;
        $totalDeleted = 0;
        $totalFailed = 0;

        foreach ($orders as $order) {
            try {
                /** @phpstan-ignore staticMethod.protected */
                $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($order->wp_id);
            } catch (Throwable $e) {
                $this->warn("  Order #{$order->order_number} (ID: {$order->id}, wp_id: {$order->wp_id}): WP lookup failed — {$e->getMessage()}");
                Log::warning('CleanupOrphanedWpOrders: WP lookup failed', [
                    'order_id' => $order->id,
                    'wp_id' => $order->wp_id,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            if (! empty($wpOrder)) {
                continue;
            }

            $totalMissing++;
            $this->warn("  [MISSING] Order #{$order->order_number} (ID: {$order->id}, wp_id: {$order->wp_id}) not found in WordPress.");
            Log::info('CleanupOrphanedWpOrders: order not found in WordPress', [
                'order_id' => $order->id,
                'wp_id' => $order->wp_id,
                'order_number' => $order->order_number,
            ]);

            if ($dryRun) {
                continue;
            }

            try {
                DB::transaction(function () use ($order): void {
                    $order->load(['uploads.orderQueues.orderQueueStatuses', 'invoiceLines', 'shopOrder', 'rejections', 'reprints']);

                    // Delete order_queue_statuses under each order_queue
                    foreach ($order->uploads as $upload) {
                        foreach ($upload->orderQueues as $orderQueue) {
                            $orderQueue->orderQueueStatuses()->forceDelete();
                            $orderQueue->forceDelete();
                        }
                        $upload->forceDelete();
                    }

                    $order->invoiceLines()->forceDelete();
                    $order->rejections()->forceDelete();
                    $order->reprints()->forceDelete();
                    $order->shopOrder()->forceDelete();
                    $order->orderQueues()->forceDelete();
                    $order->forceDelete();
                });

                $totalDeleted++;
                $this->info("  [DELETED] Order #{$order->order_number} (ID: {$order->id}) and all related records removed.");
                Log::info('CleanupOrphanedWpOrders: order and relations deleted', [
                    'order_id' => $order->id,
                    'wp_id' => $order->wp_id,
                    'order_number' => $order->order_number,
                ]);
            } catch (Throwable $e) {
                $totalFailed++;
                $this->error("  [FAILED]  Order #{$order->order_number} (ID: {$order->id}): {$e->getMessage()}");
                Log::error('CleanupOrphanedWpOrders: failed to delete order', [
                    'order_id' => $order->id,
                    'wp_id' => $order->wp_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Dry-run complete. Orders missing in WordPress: {$totalMissing}.");
        } else {
            $this->info("Done. Missing: {$totalMissing}, Deleted: {$totalDeleted}, Failed: {$totalFailed}.");
        }

        return $totalFailed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
