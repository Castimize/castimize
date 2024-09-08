<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JsonException;
use App\Services\Exact\LaravelExactOnline;

class ExactOnlineController extends Controller
{
    /**
     * Connect Exact app
     *
     * @return Factory|View
     */
    public function appConnect()
    {
        if (!auth()->check()) {
            abort(403);
        }

        return view('exact-online.connect');
    }

    /**
     * Authorize to Exact
     * Sends an oAuth request to the Exact App to get tokens
     */
    public function appAuthorize()
    {
        if (!auth()->check()) {
            abort(403);
        }

        $connection = app()->make('Exact\Connection');
        $connection->redirectForAuthorization();
    }

    /**
     * Exact Callback
     * Saves the authorisation and refresh tokens
     *
     * @param Request $request
     * @throws JsonException
     */
    public function appCallback(Request $request)
    {
        // When getting the access token and refresh token for the first time, you need to refresh the token instead of using the access token. Else the refresh token does not work!
        $config = LaravelExactOnline::loadConfig();
        $config->exact_authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        app()->make('Exact\Connection');

        dd('Exact Online connected.');
    }

    public function appCallbackWebhook(Request $request)
    {
//        Log::info(print_r($request->toArray(), true));
//        $exactOnlineHelper = new ExactOnlineHelper();
//
//        $exactRequest = $request->toArray();
//        if (array_key_exists('Content', $exactRequest) && array_key_exists('Topic', $exactRequest['Content'])) {
//            if ($exactRequest['Content']['Topic'] === 'FinancialTransactions') {
//                $transaction = $exactOnlineHelper->getTransaction($exactRequest['Content']['Key']);
//                $logTransaction = [
//                    'EntryID' => $transaction->EntryID,
//                    'ClosingBalanceFC' => $transaction->ClosingBalanceFC,
//                    'Created' => $transaction->Created,
//                    'Date' => $transaction->Date,
//                    'Description' => $transaction->Description,
//                    'Division' => $transaction->Division,
//                    'Document' => $transaction->Document,
//                    'DocumentNumber' => $transaction->DocumentNumber,
//                    'DocumentSubject' => $transaction->DocumentSubject,
//                    'EntryNumber' => $transaction->EntryNumber,
//                    'ExternalLinkDescription' => $transaction->ExternalLinkDescription,
//                    'ExternalLinkReference' => $transaction->ExternalLinkReference,
//                    'FinancialPeriod' => $transaction->FinancialPeriod,
//                    'FinancialYear' => $transaction->FinancialYear,
//                    'IsExtraDuty' => $transaction->IsExtraDuty,
//                    'JournalCode' => $transaction->JournalCode,
//                    'JournalDescription' => $transaction->JournalDescription,
//                    'Modified' => $transaction->Modified,
//                    'OpeningBalanceFC' => $transaction->OpeningBalanceFC,
//                    'PaymentConditionCode' => $transaction->PaymentConditionCode,
//                    'PaymentConditionDescription' => $transaction->PaymentConditionDescription,
//                    'PaymentReference' => $transaction->PaymentReference,
//                    'Status' => $transaction->Status,
//                    'StatusDescription' => $transaction->StatusDescription,
//                    'TransactionLines' => $transaction->TransactionLines,
//                    'Type' => $transaction->Type,
//                    'TypeDescription' => $transaction->TypeDescription,
//                ];
//                Log::info(print_r($logTransaction, true));
//
//                $receivable = $exactOnlineHelper->getReceivable($exactRequest['Content']['Key']);
//                if (!empty($receivable->TransactionID)) {
//                    $logReceivable = [
//                        'InvoiceNumber' => $receivable->InvoiceNumber,
//                        'YourRef' => $receivable->YourRef,
//                        'AmountDC' => $receivable->AmountDC,
//                        'TransactionAmountDC' => $receivable->TransactionAmountDC,
//                        'Description' => $receivable->Description,
//                        'Status' => $receivable->Status,
//                        'IsFullyPaid' => $receivable->IsFullyPaid,
//                        'TransactionID' => $receivable->TransactionID,
//                        'TransactionStatus' => $receivable->TransactionStatus,
//                    ];
//                    Log::info(print_r($logReceivable, true));
//                }
//                if (!empty($salesInvoice->Created)) {
//                    $salesInvoice = $exactOnlineHelper->getSalesInvoice($exactRequest['Content']['Key']);
//                    $logSalesInvoice = [
//                        'InvoiceID' => $salesInvoice->InvoiceID,
//                        'AmountDC' => $salesInvoice->AmountDC,
//                        'AmountDiscount' => $salesInvoice->AmountDiscount,
//                        'AmountDiscountExclVat' => $salesInvoice->AmountDiscountExclVat,
//                        'AmountFC' => $salesInvoice->AmountFC,
//                        'AmountFCExclVat' => $salesInvoice->AmountFCExclVat,
//                        'Created' => $salesInvoice->Created,
//                        'Creator' => $salesInvoice->Creator,
//                        'CreatorFullName' => $salesInvoice->CreatorFullName,
//                        'Currency' => $salesInvoice->Currency,
//                        'DeliverTo' => $salesInvoice->DeliverTo,
//                        'DeliverToAddress' => $salesInvoice->DeliverToAddress,
//                        'DeliverToContactPerson' => $salesInvoice->DeliverToContactPerson,
//                        'DeliverToContactPersonFullName' => $salesInvoice->DeliverToContactPersonFullName,
//                        'DeliverToName' => $salesInvoice->DeliverToName,
//                        'Description' => $salesInvoice->Description,
//                        'Discount' => $salesInvoice->Discount,
//                        'DiscountType' => $salesInvoice->DiscountType,
//                        'Division' => $salesInvoice->Division,
//                        'Document' => $salesInvoice->Document,
//                        'DocumentNumber' => $salesInvoice->DocumentNumber,
//                        'DocumentSubject' => $salesInvoice->DocumentSubject,
//                        'DueDate' => $salesInvoice->DueDate,
//                        'ExtraDutyAmountFC' => $salesInvoice->ExtraDutyAmountFC,
//                        'GAccountAmountFC' => $salesInvoice->GAccountAmountFC,
//                        'IncotermAddress' => $salesInvoice->IncotermAddress,
//                        'IncotermCode' => $salesInvoice->IncotermCode,
//                        'IncotermVersion' => $salesInvoice->IncotermVersion,
//                        'InvoiceDate' => $salesInvoice->InvoiceDate,
//                        'InvoiceNumber' => $salesInvoice->InvoiceNumber,
//                        'InvoiceTo' => $salesInvoice->InvoiceTo,
//                        'InvoiceToContactPerson' => $salesInvoice->InvoiceToContactPerson,
//                        'InvoiceToContactPersonFullName' => $salesInvoice->InvoiceToContactPersonFullName,
//                        'InvoiceToName' => $salesInvoice->InvoiceToName,
//                        'IsExtraDuty' => $salesInvoice->IsExtraDuty,
//                        'Journal' => $salesInvoice->Journal,
//                        'JournalDescription' => $salesInvoice->JournalDescription,
//                        'Modified' => $salesInvoice->Modified,
//                        'Modifier' => $salesInvoice->Modifier,
//                        'ModifierFullName' => $salesInvoice->ModifierFullName,
//                        'OrderDate' => $salesInvoice->OrderDate,
//                        'OrderedBy' => $salesInvoice->OrderedBy,
//                        'OrderedByContactPerson' => $salesInvoice->OrderedByContactPerson,
//                        'OrderedByContactPersonFullName' => $salesInvoice->OrderedByContactPersonFullName,
//                        'OrderedByName' => $salesInvoice->OrderedByName,
//                        'OrderNumber' => $salesInvoice->OrderNumber,
//                        'PaymentCondition' => $salesInvoice->PaymentCondition,
//                        'PaymentConditionDescription' => $salesInvoice->PaymentConditionDescription,
//                        'PaymentReference' => $salesInvoice->PaymentReference,
//                        'Remarks' => $salesInvoice->Remarks,
//                        'SalesChannel' => $salesInvoice->SalesChannel,
//                        'SalesChannelCode' => $salesInvoice->SalesChannelCode,
//                        'SalesChannelDescription' => $salesInvoice->SalesChannelDescription,
//                        'SalesInvoiceOrderChargeLines' => $salesInvoice->SalesInvoiceOrderChargeLines,
//                        'Salesperson' => $salesInvoice->Salesperson,
//                        'SalespersonFullName' => $salesInvoice->SalespersonFullName,
//                        'SelectionCode' => $salesInvoice->SelectionCode,
//                        'SelectionCodeCode' => $salesInvoice->SelectionCodeCode,
//                        'SelectionCodeDescription' => $salesInvoice->SelectionCodeDescription,
//                        'ShippingMethod' => $salesInvoice->ShippingMethod,
//                        'ShippingMethodCode' => $salesInvoice->ShippingMethodCode,
//                        'ShippingMethodDescription' => $salesInvoice->ShippingMethodDescription,
//                        'StarterSalesInvoiceStatus' => $salesInvoice->StarterSalesInvoiceStatus,
//                        'StarterSalesInvoiceStatusDescription' => $salesInvoice->StarterSalesInvoiceStatusDescription,
//                        'Status' => $salesInvoice->Status,
//                        'StatusDescription' => $salesInvoice->StatusDescription,
//                        'Type' => $salesInvoice->Type,
//                        'TypeDescription' => $salesInvoice->TypeDescription,
//                        'VATAmountDC' => $salesInvoice->VATAmountDC,
//                        'VATAmountFC' => $salesInvoice->VATAmountFC,
//                        'Warehouse' => $salesInvoice->Warehouse,
//                        'WithholdingTaxAmountFC' => $salesInvoice->WithholdingTaxAmountFC,
//                        'WithholdingTaxBaseAmount' => $salesInvoice->WithholdingTaxBaseAmount,
//                        'WithholdingTaxPercentage' => $salesInvoice->WithholdingTaxPercentage,
//                        'YourRef' => $salesInvoice->YourRef,
//                        'SalesInvoiceLines' => $salesInvoice->SalesInvoiceLines,
//                    ];
//                    Log::info(print_r($logSalesInvoice, true));
//                }
//
//            } else if ($exactRequest['Content']['Topic'] === 'GLAccount') {
//
//            }
//        }

        return response('', 200);
    }
}
