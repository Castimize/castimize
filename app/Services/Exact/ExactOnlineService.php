<?php

namespace App\Services\Exact;

use App\Enums\Admin\CurrencyEnum;
use App\Enums\Admin\PaymentIssuersEnum;
use App\Models\Country;
use App\Models\CurrencyHistoryRate;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Admin\CurrencyService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Picqer\Financials\Exact\Account;
use Picqer\Financials\Exact\ExchangeRate;
use Picqer\Financials\Exact\GLAccount;
use Picqer\Financials\Exact\SalesEntry;
use Picqer\Financials\Exact\VatCode;
use RuntimeException;

class ExactOnlineService
{
    // Omzet binnenland hoog tarief, Klant komt uit NL
    protected const GL_8000 = '4de43a20-6c86-4af6-bcf5-3adec36677c9';

    // O mzet buitenland intracommunautair, Klant komt uit EU, maar is zakelijke klant (maw met btw nummer)
    protected const GL_8100 = 'fd7f828e-f006-4c98-ad71-5d2aaf64c474';

    // Omzet buiten EU, Klant komt van buiten EU
    protected const GL_8110 = '744f4532-6874-4d64-86a7-1d08a305174b';

    // Omzet binnen EU Particulier, Klant komt uit EU, maar particulier (dus BTW belaste omzet)
    protected const GL_8120 = '07f9774b-2a55-442c-ac02-f972f7f5149f';

    // Debiteuren
    //    protected const GL_1300 = 'b807f1a9-43ef-4b68-8556-ca3db36e6507';
    // Stripe pending
    protected const GL_1103 = 'b25c4786-24aa-4db2-8c18-57bb672ccc3b';

    // Paypal pending
    protected const GL_1104 = '9a56362f-2186-4d69-955a-39ee46fceb20';
    // Af te dragen BTW hoog
    //    protected const GL_1500 = 'f25efed8-ea2c-4bf1-8fdc-3a75602d7205';

    protected const NO_VAT_CODE = '0  ';

    // Diaries
    protected const DIARY_SALES = 70;

    protected const DIARY_MEMORIAL = 90;

    protected $diaries = [
        'SALES' => 70,
        'MEMORIAL' => 90,
    ];

    protected $connection;

    protected $account;

    public function __construct()
    {
        $this->connection = app()->make('Exact\Connection');
    }

    public function getGlAccounts(): array
    {
        $glAccounts = new GLAccount($this->connection);

        $return = [];
        foreach ($glAccounts->get() as $glAccount) {
            $return[] = [
                'id' => $glAccount->ID,
                'Description' => $glAccount->Description,
            ];
        }

        return $return;
    }

    public function syncExchangeRate(CurrencyHistoryRate $currencyHistoryRate): ExchangeRate
    {
        $exchangeRate = new ExchangeRate($this->connection);
        //        $historicalDate = Carbon::parse($currencyHistoryRate->historical_date)->format('Y-m-d');
        //        $exRate = $exchangeRate->filter("StartDate eq datetime'{$historicalDate}'");
        //
        //        if (count($exRate) > 0 && $exRate[0] instanceof ExchangeRate) {
        //            $exchangeRate = $exRate[0];
        //            $exchangeRate->Rate = $currencyHistoryRate->rate;
        //            $exchangeRate->save();
        //
        //            $currencyHistoryRate->exact_online_guid = $exchangeRate->ID;
        //            $currencyHistoryRate->save();
        //
        //            return $exchangeRate;
        //        }

        $exchangeRate->Created = $currencyHistoryRate->historical_date;
        $exchangeRate->Rate = $currencyHistoryRate->rate;
        $exchangeRate->SourceCurrency = $currencyHistoryRate->base_currency;
        $exchangeRate->StartDate = $currencyHistoryRate->historical_date;
        $exchangeRate->TargetCurrency = $currencyHistoryRate->convert_currency;
        $exchangeRate->save();

        $currencyHistoryRate->exact_online_guid = $exchangeRate->ID;
        $currencyHistoryRate->save();

        return $exchangeRate;
    }

    public function syncCustomer(Customer $customer): Account
    {
        if ($customer->wpCustomer === null) {
            throw new Exception(vsprintf('Exact customer has no wpCustomer attached [%s]%s', [
                $customer->id,
                $customer->name,
            ]));
        }
        $account = new Account($this->connection);

        if ($customer->exact_online_guid) {
            $account = $account->filter("ID eq guid'{$customer->exact_online_guid}'");

            if (count($account) > 0 && $account[0] instanceof Account) {
                // now update account (isNewAccount = false, don't update Code)
                $account = $account[0];
                $account = $this->updateAccount($account, $customer, isNewAccount: false);
                $account->save();

                return $account;
            }

            throw new Exception(vsprintf('Exact GUID for customer [%s]%s not found in Exact', [
                $customer->id,
                $customer->name,
            ]));
        }

        // $acc = $account->filter("ID eq guid'{$customer->exact_online_guid}'");

        try {
            $account = $this->updateAccount($account, $customer, isNewAccount: true);
            $account->save();
        } catch (Exception $exception) {
            Log::channel('exact')->error('Customer WP id: '.$customer->wp_id.PHP_EOL.'Error: '.$exception->getMessage().PHP_EOL.print_r($account, true));

            throw $exception;
        }

        $customer->exact_online_guid = $account->ID;
        $customer->save();

        return $account;
    }

    public function syncInvoice(Invoice $invoice): void
    {
        $salesEntryLines = [];

        $orderIds = $invoice->lines->pluck('order_id', 'order_id');

        foreach ($orderIds as $orderId) {
            $orderIdLines = $invoice->lines->where('order_id', $orderId);
            $minAmount = $invoice->debit ? '' : '-';

            // Revenue
            $countryCode = strtoupper($orderIdLines->first()->order->shipping_country ?? $invoice->country);
            $revenueLine = [
                'AmountFC' => $minAmount.number_format($this->getTotalInEuro($invoice, $invoice->total, Carbon::parse($invoice->invoice_date)), 2, '.', ''),
                'Description' => __('Order #:orderNumber', [
                    'orderNumber' => $orderIdLines->first()->order->order_number,
                ]),
                'GLAccount' => $this->findGlAccountForRevenue($invoice),
                'VATCode' => self::NO_VAT_CODE,
                'Quantity' => $invoice->debit ? -1 : 1,
            ];
            // Only look up VAT code for EU countries (OSS system)
            if ($invoice->total_tax !== null && $invoice->total_tax > 0.00 && in_array($countryCode, Country::EU_COUNTRIES, true)) {
                $revenueLine['VATCode'] = $this->findVatCode($countryCode);
            }
            $salesEntryLines[] = $revenueLine;
        }

        if (count($salesEntryLines) > 0) {
            $this->createSalesEntryFromInvoice(
                invoice: $invoice,
                salesEntryLines: $salesEntryLines,
                diary: self::DIARY_SALES,
                type: ($invoice->debit ? 20 : 21),
                entryDate: Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
            );
        }
    }

    public function syncInvoicePaid(Invoice $invoice): void
    {
        $salesEntryLines = [];

        $orderIds = $invoice->lines->pluck('order_id', 'order_id');

        foreach ($orderIds as $orderId) {
            $orderIdLines = $invoice->lines->where('order_id', $orderId);

            $minAmount = $invoice->debit ? '-' : '';
            // Payment method pending, credit
            $salesEntryLines[] = [
                'AmountFC' => $minAmount.number_format((float) $this->getTotalInEuro($invoice, $invoice->total, Carbon::parse($invoice->invoice_date)), 2, '.', ''),
                'Description' => __('Order #:orderNumber', [
                    'orderNumber' => $orderIdLines->first()->order->order_number,
                ]),
                'GLAccount' => $this->findGlAccountForPaymentMethod($orderIdLines->first()->order->payment_issuer),
                'Type' => $invoice->debit ? 20 : 21,
                'Quantity' => $invoice->debit ? -1 : 1,
            ];
        }

        if (count($salesEntryLines) > 0) {
            $this->createSalesEntryFromInvoice(
                invoice: $invoice,
                salesEntryLines: $salesEntryLines,
                diary: self::DIARY_MEMORIAL,
                type: ($invoice->debit ? 20 : 21),
                entryDate: Carbon::parse($invoice->paid_at)->format('Y-m-d'),
            );
        }
    }

    public function deleteSyncedInvoice(Invoice $invoice): void
    {
        foreach ($invoice->exactSalesEntries as $exactSalesEntry) {
            $salesEntry = new SalesEntry($this->connection);
            $salesEntry = $salesEntry->filter("EntryID eq guid'{$exactSalesEntry->exact_online_guid}'");
            if (count($salesEntry) > 0 && $salesEntry[0] instanceof SalesEntry) {
                $salesEntry[0]->delete();
                $exactSalesEntry->forceDelete();
            }
        }
    }

    private function createSalesEntryFromInvoice(Invoice $invoice, array $salesEntryLines, int $diary, int $type, string $entryDate): void
    {
        if ($invoice->customer === null) {
            throw new RuntimeException(sprintf(
                'Invoice #%s has no customer attached',
                $invoice->invoice_number
            ));
        }

        // Sync customer to Exact Online if not yet synced
        if (empty($invoice->customer->exact_online_guid)) {
            if ($invoice->customer->wp_id === null) {
                throw new RuntimeException(sprintf(
                    'Customer #%s has no wp_id, cannot sync to Exact',
                    $invoice->customer->id
                ));
            }

            $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($invoice->customer->wp_id);
            if ($wpCustomer === null) {
                throw new RuntimeException(sprintf(
                    'WooCommerce customer not found for wp_id %s',
                    $invoice->customer->wp_id
                ));
            }

            $invoice->customer->wpCustomer = $wpCustomer;
            $this->syncCustomer($invoice->customer);
            $invoice->customer->refresh();
        }

        $salesEntry = new SalesEntry($this->connection);
        $salesEntry->Customer = $invoice->customer->exact_online_guid;
        $salesEntry->Currency = CurrencyEnum::EUR->value;
        $salesEntry->Journal = $diary;
        $salesEntry->YourRef = $invoice->invoice_number;
        $salesEntry->OrderNumber = $invoice->invoice_nuber;
        $salesEntry->Description = $invoice->description;
        $salesEntry->EntryDate = $entryDate;
        $salesEntry->PaymentCondition = '00';
        $salesEntry->Type = $type;
        $salesEntry->SalesEntryLines = $salesEntryLines;
        $salesEntry->save();

        $invoice->exactSalesEntries()->create([
            'exact_online_guid' => $salesEntry->EntryID,
            'diary' => $diary,
            'exact_data' => $salesEntry->attributes(),
        ]);
    }

    private function updateSalesEntryFromInvoice(Invoice $invoice, array $salesEntryLines, int $diary): void
    {
        $salesEntry = new SalesEntry($this->connection);
    }

    private function updateAccount(Account $account, Customer $customer, bool $isNewAccount = false): Account
    {
        $wpCustomer = $customer->wpCustomer;
        $billingVatNumber = null;
        foreach ($wpCustomer['meta_data'] as $metaData) {
            if ($metaData->key === 'billing_eu_vat_number') {
                $billingVatNumber = $metaData->value;
            }
        }

        // Build name with fallbacks
        $name = trim(($wpCustomer['first_name'] ?? '').' '.($wpCustomer['last_name'] ?? ''));
        if ($name === '') {
            $name = trim(($wpCustomer['billing']->first_name ?? '').' '.($wpCustomer['billing']->last_name ?? ''));
        }
        if ($name === '') {
            $name = $wpCustomer['billing']->company ?? '';
        }
        if ($name === '') {
            $name = $wpCustomer['username'] ?? '';
        }
        if ($name === '') {
            throw new Exception("Cannot sync customer to Exact: Name is empty for wp_id {$wpCustomer['id']}");
        }

        // Only set Code for new accounts - Code is unique in Exact and cannot be changed
        if ($isNewAccount) {
            $account->Code = $wpCustomer['id'];
        }
        $account->AddressLine1 = $wpCustomer['billing']->address_1;
        $account->AddressLine2 = $wpCustomer['billing']->address_2;
        $account->ChamberOfCommerce = null;
        $account->City = $wpCustomer['billing']->city;
        $account->Country = mb_strtoupper($wpCustomer['billing']->country);
        $account->IsSales = 'true';
        $account->Name = $name;
        $account->Postcode = $wpCustomer['billing']->postcode;
        $account->Status = 'C';
        $account->Email = $wpCustomer['email'];
        $account->Phone = $wpCustomer['billing']->phone;
        $account->SecurityLevel = 100;
        $account->VATNumber = $billingVatNumber;

        // Remove ShowRemarkForSales from attributes to avoid "Ongeldige waarde: Opmerkingen weergeven" error
        // Using reflection because the Picqer client doesn't have __unset and $attributes is protected
        $this->removeAccountAttribute($account, 'ShowRemarkForSales');

        return $account;
    }

    private function findGlAccountForRevenue(Invoice $invoice): string
    {
        $country = strtoupper($invoice->country);
        if ($country === 'NL') {
            return self::GL_8000;
        }
        if (in_array($country, Country::EU_COUNTRIES, true)) {
            if ($invoice->vat_number !== null) {
                return self::GL_8100;
            }

            return self::GL_8120;
        }

        return self::GL_8110;
    }

    private function findGlAccountForPaymentMethod(string $paymentIssuer): ?string
    {
        Log::channel('exact')->info("Finding GL account for payment issuer: {$paymentIssuer}");
        if (in_array($paymentIssuer, PaymentIssuersEnum::getStripeMethods(), true)) {
            return self::GL_1103;
        }
        if ($paymentIssuer === PaymentIssuersEnum::Paypal->value) {
            return self::GL_1104;
        }

        return null;
    }

    private function getTotalInEuro(Invoice $invoice, $total, Carbon $historyDate)
    {
        if ($invoice->currency_code === CurrencyEnum::EUR->value) {
            return $total;
        }

        return (new CurrencyService)->convertCurrency($invoice->currency_code, CurrencyEnum::EUR->value, $total, $historyDate);
    }

    public function findVatCode(string $countryCode): string
    {
        if ($countryCode === 'NL') {
            return '4  ';
        }

        $vatCodes = (new VatCode($this->connection))->get();
        /** @var VatCode $vatCode */
        foreach ($vatCodes as $vatCode) {
            $ossCountry = trim((string) $vatCode->OssCountry);
            // Match on OssCountry (case-insensitive)
            if (strcasecmp($ossCountry, $countryCode) === 0) {
                Log::channel('exact')->info("Found VAT code for {$countryCode}: {$vatCode->Code} (OssCountry: {$ossCountry})");

                return $vatCode->Code;
            }
        }

        // Log available VAT codes for debugging
        $availableCodes = [];
        foreach ($vatCodes as $vatCode) {
            $availableCodes[] = "Code: {$vatCode->Code}, OssCountry: ".($vatCode->OssCountry ?? 'null');
        }
        Log::channel('exact')->warning("VAT Code not found for {$countryCode}. Available codes: ".implode('; ', $availableCodes));

        throw new RuntimeException(__('VAT Code not found for :countryCode', [
            'countryCode' => $countryCode,
        ]));
    }

    /**
     * Remove an attribute from a Picqer Exact model using reflection.
     * The Picqer client doesn't have __unset and $attributes is protected.
     */
    private function removeAccountAttribute(Account $account, string $attributeName): void
    {
        $reflection = new \ReflectionClass($account);
        $attributesProperty = $reflection->getProperty('attributes');
        $attributes = $attributesProperty->getValue($account);
        unset($attributes[$attributeName]);
        $attributesProperty->setValue($account, $attributes);
    }
}
