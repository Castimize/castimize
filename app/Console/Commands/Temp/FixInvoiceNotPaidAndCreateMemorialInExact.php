<?php

namespace App\Console\Commands\Temp;

use App\Enums\Admin\PaymentMethodsEnum;
use App\Models\Invoice;
use App\Services\Admin\InvoicesService;
use Illuminate\Console\Command;

class FixInvoiceNotPaidAndCreateMemorialInExact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:temp-fix-invoice-not-paid-and-create-memorial-in-exact {--invoice-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temp command to set invoice paid if order is paid and create the memorial booking in Exact';

    /**
     * Execute the console command.
     */
    public function handle(InvoicesService  $invoicesService)
    {
        if ($this->option('invoice-id')) {
            $invoicesQuery = Invoice::with(['customer', 'lines.order'])
                ->where('id', $this->option('invoice-id'));
        } else {
            $invoicesQuery = Invoice::with(['customer', 'lines.order'])
                ->whereHas('lines.order', function ($query) {
                    $query->where('is_paid', 1)
                        ->where('payment_issuer', '!=', PaymentMethodsEnum::DIRECT_BANK_TRANSFER->value);
                })
                ->where('paid', 0);
        }

        $count = $invoicesQuery->count();
        $invoices = $invoicesQuery->get();

        $progressBar = $this->output->createProgressBar();
        $this->info("Updating $count invoices");
        $progressBar->start();

        foreach ($invoices as $invoice) {
            $this->info("Handle invoice $invoice->id");
            $invoicesService->updatePaid($invoice);

            $progressBar->advance();
        }

        $progressBar->finish();

        return true;
    }
}
