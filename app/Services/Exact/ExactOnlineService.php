<?php

namespace App\Services\Exact;

use App\Models\Country;
use App\Models\CurrencyHistoryRate;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Picqer\Financials\Exact\Account;
use Picqer\Financials\Exact\ApiException;
use Picqer\Financials\Exact\BankAccount;
use Picqer\Financials\Exact\Connection;
use Picqer\Financials\Exact\DirectDebitMandate;
use Picqer\Financials\Exact\ExchangeRate;
use Picqer\Financials\Exact\GLAccount;
use Picqer\Financials\Exact\Me;
use Picqer\Financials\Exact\Receivable;
use Picqer\Financials\Exact\ReceivablesList;
use Picqer\Financials\Exact\SalesEntry;
use Picqer\Financials\Exact\SalesInvoice;
use Picqer\Financials\Exact\Transaction;
use Picqer\Financials\Exact\VatCode;
use Picqer\Financials\Exact\WebhookSubscription;

class ExactOnlineService
{
    // Omzet binnenland hoog tarief, Klant komt uit NL
    protected const GL_8000 = '4de43a20-6c86-4af6-bcf5-3adec36677c9';
    //O mzet buitenland intracommunautair, Klant komt uit EU, maar is zakelijke klant (maw met btw nummer)
    protected const GL_8100 = 'fd7f828e-f006-4c98-ad71-5d2aaf64c474';
    // Omzet buiten EU, Klant komt van buiten EU
    protected const GL_8110 = '744f4532-6874-4d64-86a7-1d08a305174b';
    // Omzet binnen EU Particulier, Klant komt uit EU, maar particulier (dus BTW belaste omzet)
    protected const GL_8120 = '07f9774b-2a55-442c-ac02-f972f7f5149f';
    // Debiteuren
    protected const GL_1300 = 'b807f1a9-43ef-4b68-8556-ca3db36e6507';
    // Stripe pending
    protected const GL_1103 = '9a56362f-2186-4d69-955a-39ee46fceb20';
    // Paypal pending
    protected const GL_1104 = '9a56362f-2186-4d69-955a-39ee46fceb20';
    // Af te dragen BTW hoog
    protected const GL_1500 = 'f25efed8-ea2c-4bf1-8fdc-3a75602d7205';

    // Diaries
    protected const DIARY_SALES = 70;
    protected const DIARY_MEMORIAL = 90;

//    protected $glAccounts = [
//        '8000' => [
//            'gl' => self::GL_8000,
//            'diary' => self
//        ],
//        'default' => [
//            self::LEADS_REVENUE => 'ab56daba-8e55-4392-ae93-ab669a79e950', # Omzet binnen EU
//            self::STORNO => 'c4d95665-3e6f-4b9f-8e17-6ce163648b0d', # Omzet storneringsboetes binnen EU
//            self::SERVICE_FEE => '44d08572-f3e8-4c07-ba74-e65f44a30e16', # Omzet service fee binnen EU
//        ],
//    ];

    protected $diaries = [
        'SALES' => 70,
        'MEMORIAL' => 90,
    ];

    protected $connection;

    protected $account;

    /**
     * ExactOnline constructor.
     */
    public function __construct()
    {
        $this->connection = app()->make('Exact\Connection');
    }

    private function connect()
    {
        $connection = new Connection();
        $connection->setRedirectUrl(Config('exactonline.callback_url'));
        $connection->setExactClientId(Config('exactonline.exact_client_id'));
        $connection->setExactClientSecret(Config('exactonline.exact_client_secret'));

        $authorizationCode = self::getValue('authorizationcode');
        if ($authorizationCode) {
            $connection->setAuthorizationCode($authorizationCode);
        }

        $accessToken = self::getValue('accesstoken');
        if ($accessToken) {
            $connection->setAccessToken(unserialize($accessToken));
        }

        $refreshToken = self::getValue('refreshtoken');
        if ($refreshToken) {
            $connection->setRefreshToken($refreshToken);
        }

        $expiresIn = self::getValue('expires_in');
        if ($expiresIn) {
            $connection->setTokenExpires($expiresIn);
        }

        $connection->setAcquireAccessTokenLockCallback('App\Services\Exact\ExactOnlineService::acquireLock');
        $connection->setAcquireAccessTokenUnlockCallback('App\Services\Exact\ExactOnlineService::releaseLock');
        $connection->setTokenUpdateCallback('App\Services\Exact\ExactOnlineService::updateTokens');

        // Make the client connect and exchange tokens
        try {
            $connection->connect();
        } catch (Exception $exception) {
            throw new Exception('Could not connect to Exact: ' . $exception->getMessage());
        }

        $connection->setDivision(Config('exactonline.exact_division'));

        return $connection;
    }

    public static function updateTokens(Connection $connection): bool
    {
        // Save the new tokens for next connections
        self::setValue('accesstoken', serialize($connection->getAccessToken()));
        self::setValue('refreshtoken', $connection->getRefreshToken());

        // Optionally, save the expiry-timestamp. This prevents exchanging valid tokens (ie. saves you some requests)
        self::setValue('expires_in', $connection->getTokenExpires());

        return true;
    }

    public static function acquireLock(): bool
    {
        Log::info('Acquire exact-lock');
        if (!Cache::has('exact-lock')) {
            return false;
        }
        Log::info('Set exact-lock');

        return Cache::put('exact-lock', time());
    }

    public static function releaseLock()
    {
        Log::info('Release exact-lock');

        return Cache::forget('exact-lock');
    }

    /**
     * Function to retrieve persisted data for the example
     *
     * @param string $key
     *
     * @return null|string
     * @throws JsonException
     */
    public static function getValue(string $key): ?string
    {
//        $storage = (array)json_decode(Redis::get('exact:api:json'), true);
        $storage = (array)json_decode(Storage::disk('r2_private')->get('exact/credentials.json'), true, 512, JSON_THROW_ON_ERROR);

        return $storage[$key] ?? null;
    }

    /**
     * Function to persist some data for the example
     *
     * @param string $key
     * @param string $value
     * @throws JsonException
     */
    public static function setValue(string $key, string $value)
    {
        $storage = [];
        if (Storage::disk('r2_private')->exists('exact/credentials.json')) {
            $storage = json_decode(Storage::disk('s3')->get('exact/credentials.json'), true, 512, JSON_THROW_ON_ERROR);
        }
        $storage[$key] = $value;

        Storage::disk('r2_private')->put('exact/credentials.json', json_encode($storage, JSON_THROW_ON_ERROR));
    }

    public function test(): void
    {
        $glAccount = new GLAccount($this->connection);
        $glAccount = $glAccount->filter("Code eq '8000'", '', '', ['$top' => 1]);

        dd($glAccount);
    }

    public function syncExchangeRate(CurrencyHistoryRate $currencyHistoryRate): ExchangeRate
    {
        $exchangeRate = new ExchangeRate($this->connection);
        $exchangeRate->Created = $currencyHistoryRate->historical_date;
        $exchangeRate->Rate = $currencyHistoryRate->rate;
        $exchangeRate->SourceCurrency = $currencyHistoryRate->base_currency;
        $exchangeRate->StartDate = $currencyHistoryRate->historical_date;
        $exchangeRate->TargetCurrency = $currencyHistoryRate->convert_currency;
        $exchangeRate->save();

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
                // now update account
                $account = $account[0];
                $account = $this->updateAccount($account, $customer);
                $account->save();

                $customer->exact_online_guid = $account->ID;
                $customer->save();

                return $account;
            }

            throw new Exception(vsprintf('Exact GUID for customer [%s]%s not found in Exact', [
                $customer->id,
                $customer->name,
            ]));
        }

        $account = $this->updateAccount($account, $customer);
        $account->save();

        $customer->exact_online_guid = $account->ID;
        $customer->save();

        return $account;
    }

    public function syncInvoice(Invoice $invoice)
    {
        $salesEntryLines = [];


        $salesEntryLines[] = [
            'AmountFC' => number_format($invoice->total, 2, '.', ''),
            'Description' => __('Order #:orderNumber', ['orderNUmber' => $invoice->lines->first()->order->order_number]),
            'GLAccount' => $this->getGlAccountForInvoice($invoice, 'revenue'),
            'Quantity' => $invoice->debit ? 1 : -1,
        ];


//        if ($bill->charges()->where('is_service_fee', 1)->exists()) {
//            foreach ($bill->charges()->where('is_service_fee', 1)->get() as $billCharge) {
//                $salesEntryLines[] = [
//                    'AmountFC' => number_format($billCharge->currency_amount, 2, '.', ''),
//                    'Description' => $billCharge->description,
//                    'GLAccount' => $this->getGlAccountForBill($countryCode, self::SERVICE_FEE), // Omzet service fee
//                    'Quantity' => $billCharge->amount > 0 ? 1 : -1,
//                ];
//            }
//        }


        $salesEntry = new SalesEntry($this->connection);
        $salesEntry->Customer = $invoice->customer->exact_online_guid;
        $salesEntry->Currency = $bill->company->currency->code;
        $salesEntry->Journal = $this->diaries[$bill->company->currency->code ?? 'EUR'];
        $salesEntry->YourRef = $bill->billnumber;
        $salesEntry->OrderNumber = $bill->billnumber;
        $salesEntry->Description = $bill->is_storno == 1 ? $bill->charges->first()->description : $description;
        $salesEntry->EntryNumber = $bill->billnumber;
        $salesEntry->EntryDate = $bill->created_at->format('Y-m-d');
        $salesEntry->PaymentCondition = $this->getPaymentConditionsForDays($bill->company->getBillExpiryDate());
        $salesEntry->Type = $bill->charges->sum('amount') > 0 ? 20 : 21;
        $salesEntry->SalesEntryLines = $salesEntryLines;
        $salesEntry->save();
        $invoice->exact_online_guid = $salesEntry->EntryID;
    }

    private function updateAccount(Account $account, Customer $customer): Account
    {
        $wpCustomer = $customer->wpCustomer;
        $billingVatNumber = null;
        foreach ($wpCustomer['meta_data'] as $metaData) {
            if ($metaData->key === 'billing_eu_vat_number') {
                $billingVatNumber = $metaData->value;
            }
        }
        $account->Code = $wpCustomer['id'];
        $account->AddressLine1 = $wpCustomer['billing']->address_1;
        $account->AddressLine2 = $wpCustomer['billing']->address_2;
        $account->ChamberOfCommerce = null;
        $account->City = $wpCustomer['billing']->city;
        $account->Country = mb_strtoupper($wpCustomer['billing']->country);
        $account->IsSales = 'true';
        $account->Name = $wpCustomer['first_name'] . ' ' . $wpCustomer['last_name'];
        $account->Postcode = $wpCustomer['billing']->postcode;
        $account->Status = 'C';
        $account->Email = $wpCustomer['email'];
        $account->Phone = $wpCustomer['billing']->phone;
        $account->SecurityLevel = 100;
        $account->VATNumber = $billingVatNumber;

        return $account;
    }

    public function getGlAccounts()
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

    public function getGlAccountForInvoice(Invoice $invoice, string $type)
    {
//        if (!in_array($accountType, [self::LEADS_REVENUE, self::STORNO, self::SERVICE_FEE], true)) {
//            throw new Exception('GLAccount "' . $accountType . '" does not exist');
//        }
        return match ($type) {
            'revenue' => $this->findGlAccountForRevenue($invoice),
        };
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

    public function findVatCode()
    {
        $vatCodes = (new VatCode($this->connection))->get();
        dd($vatCodes);
    }

    /**
     * @param string $iban
     * @return string|null
     */
    private function getBicFromIban(string $iban): ?string
    {
        return match (true) {
            str_contains($iban, 'RABO') => 'RABONL2U',
            str_contains($iban, 'ABNA') => 'ABNANL2A',
            str_contains($iban, 'INGB') => 'INGBNL2A',
            str_contains($iban, 'KNAB') => 'KNABNL2H',
            str_contains($iban, 'SNSB') => 'SNSBNL2A',
            str_contains($iban, 'TRIO') => 'TRIONL2U',
            str_contains($iban, 'RBRB') => 'RBRBNL21',
            str_contains($iban, 'ASNB') => 'ASNBNL21',
            str_contains($iban, 'BUNQ') => 'BUNQNL2A',
            str_contains($iban, 'FVLB') => 'FVLBNL22',
        };
    }
}
