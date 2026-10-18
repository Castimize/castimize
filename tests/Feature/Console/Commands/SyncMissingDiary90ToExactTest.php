<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Jobs\SyncInvoicePaidToExact;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceExactSalesEntry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncMissingDiary90ToExactTest extends TestCase
{
    use DatabaseTransactions;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->customer = Customer::factory()->create([
            'wp_id' => 77777,
        ]);
    }

    private function createInvoice(string $number): Invoice
    {
        return Invoice::create([
            'customer_id' => $this->customer->id,
            'invoice_number' => $number,
            'invoice_date' => now(),
            'debit' => true,
            'total' => 100.00,
            'total_tax' => 21.00,
            'currency_code' => 'EUR',
            'paid' => true,
            'paid_at' => now(),
        ]);
    }

    #[Test]
    public function it_dispatches_job_for_invoices_with_diary_70_but_missing_diary_90(): void
    {
        $invoice = $this->createInvoice('INV-MISSING-90');
        InvoiceExactSalesEntry::create([
            'invoice_id' => $invoice->id,
            'exact_online_guid' => 'guid-diary-70',
            'diary' => 70,
            'exact_data' => [],
        ]);

        $this->artisan('castimize:sync-missing-diary-90-to-exact')
            ->assertSuccessful();

        Queue::assertPushed(SyncInvoicePaidToExact::class, function ($job) use ($invoice) {
            return $job->invoice->id === $invoice->id;
        });
    }

    #[Test]
    public function it_does_not_dispatch_job_for_invoices_with_both_diary_70_and_diary_90(): void
    {
        $invoice = $this->createInvoice('INV-COMPLETE');
        InvoiceExactSalesEntry::create([
            'invoice_id' => $invoice->id,
            'exact_online_guid' => 'guid-complete-70',
            'diary' => 70,
            'exact_data' => [],
        ]);
        InvoiceExactSalesEntry::create([
            'invoice_id' => $invoice->id,
            'exact_online_guid' => 'guid-complete-90',
            'diary' => 90,
            'exact_data' => [],
        ]);

        $this->artisan('castimize:sync-missing-diary-90-to-exact')
            ->assertSuccessful();

        Queue::assertNotPushed(SyncInvoicePaidToExact::class);
    }

    #[Test]
    public function it_does_not_dispatch_job_for_invoices_with_no_exact_entries_at_all(): void
    {
        $this->createInvoice('INV-NO-ENTRIES');

        $this->artisan('castimize:sync-missing-diary-90-to-exact')
            ->assertSuccessful();

        Queue::assertNotPushed(SyncInvoicePaidToExact::class);
    }

    #[Test]
    public function it_dispatches_jobs_for_multiple_invoices_missing_diary_90(): void
    {
        $invoice1 = $this->createInvoice('INV-MISSING-90-A');
        $invoice2 = $this->createInvoice('INV-MISSING-90-B');

        foreach ([$invoice1, $invoice2] as $i => $invoice) {
            InvoiceExactSalesEntry::create([
                'invoice_id' => $invoice->id,
                'exact_online_guid' => "guid-70-{$i}",
                'diary' => 70,
                'exact_data' => [],
            ]);
        }

        $this->artisan('castimize:sync-missing-diary-90-to-exact')
            ->assertSuccessful();

        Queue::assertPushed(SyncInvoicePaidToExact::class, 2);
    }
}
