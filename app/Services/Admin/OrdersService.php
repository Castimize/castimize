<?php

namespace App\Services\Admin;

use App\DTO\Order\OrderDTO;
use App\DTO\Order\UploadDTO;
use App\Enums\Woocommerce\WcOrderStatesEnum;
use App\Jobs\CreateInvoicesFromOrder;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Models\Order;
use App\Models\Upload;
use App\Models\User;
use App\Services\Woocommerce\WoocommerceApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Stripe\Charge;

class OrdersService
{
    private CustomersService $customersService;
    private UploadsService $uploadsService;
    private OrderQueuesService $orderQueuesService;
    private WoocommerceApiService $woocommerceApiService;


    public function __construct()
    {
        $this->customersService = new CustomersService();
        $this->uploadsService = new UploadsService();
        $this->orderQueuesService = new OrderQueuesService();
        $this->woocommerceApiService = new WoocommerceApiService();
    }

    public function storeOrderFromWpOrder($wpOrder)
    {
        $systemUser = User::find(1);
        $country = Country::where('alpha2', strtolower($wpOrder['billing']->country))->first();
        if ($country === null) {
            $country = Country::where('alpha2', 'nl')->first();
        }
        $customer = null;
        if (!empty($wpOrder['customer_id'])) {
            $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($wpOrder['customer_id']);
            $customer = $this->customersService->storeCustomerFromWpCustomer($wpCustomer);
        }

        $currency = Currency::where('code', $wpOrder['currency'])->first();
        if ($currency === null) {
            $currency = Currency::where('code', 'USD')->first();
        }

        $stripePaymentId = null;
        $billingVatNumber = null;
        $shippingEmail = null;
        foreach ($wpOrder['meta_data'] as $orderMetaData) {
            if ($orderMetaData->key === '_billing_eu_vat_number') {
                $billingVatNumber = $orderMetaData->value;
            }
            if ($orderMetaData->key === '_payment_intent_id') {
                $stripePaymentId = $orderMetaData->value;
            }
            if ($orderMetaData->key === '_shipping_email') {
                $shippingEmail = $orderMetaData->value;
            }
        }

        $isPaid = $wpOrder['date_paid'] !== null;
        $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $wpOrder['date_created_gmt']), 'GMT')?->setTimezone(env('APP_TIMEZONE'));

        $taxPercentage = null;
        if (count($wpOrder['tax_lines']) > 0) {
            $taxPercentage = $wpOrder['tax_lines'][0]->rate_percent;
        }

        $order = Order::create([
            'wp_id' => $wpOrder['id'],
            'customer_id' => $customer->id,
            'currency_id' => $currency->id,
            'country_id' => $country->id,
            'order_number' => $wpOrder['number'],
            'order_key' => $wpOrder['order_key'],
            'status' => $wpOrder['status'],
            'first_name' => $wpOrder['billing']->first_name,
            'last_name' => $wpOrder['billing']->last_name,
            'email' => $wpOrder['billing']->email,
            'billing_first_name' => $wpOrder['billing']->first_name,
            'billing_last_name' => $wpOrder['billing']->last_name,
            'billing_company' => $wpOrder['billing']->company,
            'billing_phone_number' => $wpOrder['billing']->phone,
            'billing_email' => $wpOrder['billing']->email,
            'billing_address_line1' => $wpOrder['billing']->address_1,
            'billing_address_line2' => $wpOrder['billing']->address_2,
            'billing_postal_code' => $wpOrder['billing']->postcode,
            'billing_city' => $wpOrder['billing']->city,
            'billing_state' => $wpOrder['billing']->state ?? null,
            'billing_country' => $wpOrder['billing']->country,
            'billing_vat_number' => $billingVatNumber,
            'shipping_first_name' => $wpOrder['shipping']->first_name,
            'shipping_last_name' => $wpOrder['shipping']->last_name,
            'shipping_company' => $wpOrder['shipping']->company,
            'shipping_phone_number' => ! empty($wpOrder['shipping']->phone) ? $wpOrder['shipping']->phone : $wpOrder['billing']->phone,
            'shipping_email' => $shippingEmail ?? $wpOrder['billing']->email,
            'shipping_address_line1' => $wpOrder['shipping']->address_1,
            'shipping_address_line2' => $wpOrder['shipping']->address_2,
            'shipping_postal_code' => $wpOrder['shipping']->postcode,
            'shipping_city' => $wpOrder['shipping']->city,
            'shipping_state' => $wpOrder['shipping']->state ?? null,
            'shipping_country' => $wpOrder['shipping']->country,
            'service_fee' => null,
            'service_fee_tax' => null,
            'shipping_fee' => $wpOrder['shipping_total'],
            'shipping_fee_tax' => $wpOrder['shipping_tax'],
            'discount_fee' => $wpOrder['discount_total'],
            'discount_fee_tax' => $wpOrder['discount_tax'],
            'total' => $wpOrder['total'],
            'total_tax' => $wpOrder['total_tax'],
            'production_cost' => null,
            'production_cost_tax' => null,
            'prices_include_tax' => $wpOrder['prices_include_tax'] ?? true,
            'tax_percentage' => $taxPercentage,
            'currency_code' => $wpOrder['currency'] ?? 'USD',
            'payment_method' => $wpOrder['payment_method_title'],
            'payment_issuer' => $wpOrder['payment_method'],
            'payment_intent_id' => $stripePaymentId,
            'customer_ip_address' => $wpOrder['customer_ip_address'],
            'customer_user_agent' => $wpOrder['customer_user_agent'],
            'meta_data' => $wpOrder['meta_data'],
            'comments' => $wpOrder['customer_note'],
            'promo_code' => null,
            'is_paid' => $isPaid !== null,
            'paid_at' => $wpOrder['date_paid'],
            'created_by' => $systemUser->id,
            'created_at' => $createdAt,
            'updated_by' => $systemUser->id,
            'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $wpOrder['date_modified_gmt']), 'GMT')?->setTimezone(env('APP_TIMEZONE')),
        ]);

        $biggestCustomerLeadTime = $this->storeOrderLineItems(
            wpOrder: $wpOrder,
            order: $order,
            customer: $customer,
            country: $country,
            currency: $currency,
            isPaid: $isPaid
        );

        $order->order_customer_lead_time = $biggestCustomerLeadTime;
        $order->due_date = Carbon::parse($order->created_at)->addBusinessDays($biggestCustomerLeadTime);
        $order->save();

        if ($isPaid) {
            foreach ($order->uploads()->get() as $upload) {
                // Set upload to order queue
                $this->uploadsService->setUploadToOrderQueue($upload);
            }
        }

        CreateInvoicesFromOrder::dispatch($order->wp_id);

        return $order;
    }

    public function storeOrderFromDto(OrderDTO $orderDto): Order
    {
        $systemUser = User::find(1);
        $country = Country::where('alpha2', strtolower($orderDto->billingCountry))->first();
        if ($country === null) {
            $country = Country::where('alpha2', 'nl')->first();
        }
        $customer = null;
        if (!empty($orderDto->customerId) && $orderDto->source === 'wp') {
            $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($orderDto->customerId);
            $customer = $this->customersService->storeCustomerFromWpCustomer($wpCustomer);
        }

        $currency = Currency::where('code', $orderDto->currencyCode)->first();
        if ($currency === null) {
            $currency = Currency::where('code', 'USD')->first();
        }

        $order = Order::create([
            'wp_id' => $orderDto->wpId,
            'customer_id' => $customer->id,
            'currency_id' => $currency->id,
            'country_id' => $country->id,
            'order_number' => $orderDto->orderNumber,
            'order_key' => $orderDto->orderKey,
            'status' => $orderDto->status,
            'first_name' => $orderDto->firstName,
            'last_name' => $orderDto->lastName,
            'email' => $orderDto->email,
            'billing_first_name' => $orderDto->billingFirstName,
            'billing_last_name' => $orderDto->billingLastName,
            'billing_company' => $orderDto->billingCompany,
            'billing_phone_number' => $orderDto->billingPhoneNumber,
            'billing_email' => $orderDto->billingEmail,
            'billing_address_line1' => $orderDto->billingAddressLine1,
            'billing_address_line2' => $orderDto->billingAddressLine2,
            'billing_postal_code' => $orderDto->billingPostalCode,
            'billing_city' => $orderDto->billingCity,
            'billing_state' => $orderDto->billingState,
            'billing_country' => $orderDto->billingCountry,
            'billing_vat_number' => $orderDto->billingVatNumber,
            'shipping_first_name' => $orderDto->shippingFirstName,
            'shipping_last_name' => $orderDto->shippingLastName,
            'shipping_company' => $orderDto->shippingCompany,
            'shipping_phone_number' => $orderDto->shippingPhoneNumber,
            'shipping_email' => $orderDto->shippingEmail,
            'shipping_address_line1' => $orderDto->shippingAddressLine1,
            'shipping_address_line2' => $orderDto->shippingAddressLine2,
            'shipping_postal_code' => $orderDto->shippingPostalCode,
            'shipping_city' => $orderDto->shippingCity,
            'shipping_state' => $orderDto->shippingState,
            'shipping_country' => $orderDto->shippingCountry,
            'service_fee' => null,
            'service_fee_tax' => null,
            'shipping_fee' => $orderDto->shippingFee,
            'shipping_fee_tax' => $orderDto->shippingFeeTax,
            'discount_fee' => $orderDto->discountFee,
            'discount_fee_tax' => $orderDto->discountFeeTax,
            'total' => $orderDto->total,
            'total_tax' => $orderDto->totalTax,
            'production_cost' => null,
            'production_cost_tax' => null,
            'tax_percentage' => $orderDto->taxPercentage,
            'currency_code' => $orderDto->currencyCode,
            'payment_method' => $orderDto->paymentMethod,
            'payment_issuer' => $orderDto->paymentIssuer,
            'payment_intent_id' => $orderDto->paymentIntentId,
            'customer_ip_address' => $orderDto->customerIpAddress,
            'customer_user_agent' => $orderDto->customerUserAgent,
            'meta_data' => $orderDto->metaData,
            'comments' => $orderDto->comments,
            'promo_code' => null,
            'is_paid' => $orderDto->isPaid,
            'paid_at' => $orderDto->paidAt,
            'created_by' => $systemUser->id,
            'created_at' => $orderDto->createdAt,
            'updated_by' => $systemUser->id,
            'updated_at' => $orderDto->updatedAt,
        ]);

        $biggestCustomerLeadTime = $this->storeUploads(
            uploads: $orderDto->uploads,
            order: $order,
            customer: $customer,
            currency: $currency,
        );

        $order->order_customer_lead_time = $biggestCustomerLeadTime;
        $order->due_date = Carbon::parse($order->created_at)->addBusinessDays($biggestCustomerLeadTime);
        $order->save();

        if ($orderDto->isPaid) {
            foreach ($order->uploads()->get() as $upload) {
                // Set upload to order queue
                $this->uploadsService->setUploadToOrderQueue($upload);
            }
        }

        CreateInvoicesFromOrder::dispatch($order->wp_id);

        return $order;
    }

    public function updateOrderFromDto(Order $order, OrderDTO $orderDto): Order
    {
        $systemUser = User::find(1);

        $order->update([
            'is_paid' => $orderDto->isPaid,
            'paid_at' => $orderDto->paidAt,
            'payment_method' => $orderDto->paymentMethod,
            'payment_issuer' => $orderDto->paymentIssuer,
            'total' => $orderDto->total,
            'total_tax' => $orderDto->totalTax,
            'discount_fee' => $orderDto->discountFee,
            'discount_fee_tax' => $orderDto->discountFeeTax,
            'shipping_fee' => $orderDto->shippingFee,
            'shipping_fee_tax' => $orderDto->shippingFeeTax,
            'updated_by' => $systemUser->id,
            'updated_at' => now(),
        ]);

        $this->updateUploads($orderDto->uploads);

        if ($orderDto->isPaid && $order->orderQueues()->count() === 0) {
            foreach ($order->uploads()->get() as $upload) {
                // Set upload to order queue
                $this->uploadsService->setUploadToOrderQueue($upload);
            }
        }

        CreateInvoicesFromOrder::dispatch($order->wp_id);

        return $order;
    }

    public function handleRejectionsAndRefund(Order $order, $orderQueues)
    {
        $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($order->wp_id);

        $refundAll = true;
        $refundAmount = 0.00;
        $refundTaxAmount = 0.00;
        $lineItems = [];
        foreach ($orderQueues as $orderQueue) {
            if (!$orderQueue->rejection) {
                $refundAll = false;
            } else {
                $refundAmount += $orderQueue->upload->total;
                $refundTaxAmount += $orderQueue->upload->total_tax;
                $lineItems[] = $this->orderQueuesService->getRefundLineItem($orderQueue, $orderQueue->upload->total, $wpOrder['line_items']);
                $orderQueue->upload->total_refund = $refundAmount;
                $orderQueue->upload->total_refund_tax = $orderQueue->upload->total_tax;
                $orderQueue->upload->save();
            }
        }

        $cancelOrder = false;
        if ($refundAll) {
            $refundAmount = $order->total;
            $refundTaxAmount = $order->total_tax;
            $cancelOrder = true;
        }

        if ($refundAmount > 0.00) {
            $order->total_refund = $refundAmount;
            $order->total_refund_tax = $refundTaxAmount;
            if ($cancelOrder) {
                $order->status = 'canceled';
            }
            $order->save();
        }

        $refundOrder = $this->woocommerceApiService->refundOrder($order->wp_id, (string)$refundAmount, $lineItems);

        if ($cancelOrder) {
            $this->woocommerceApiService->updateOrderStatus($order->wp_id, WcOrderStatesEnum::Cancelled->value);
        }

        return $refundOrder;
    }

    public function handleStripeRefund(Order $order, Charge $charge): void
    {
        if ($order !== null && $charge->status === 'succeeded' && $charge->refunded) {
            $order->total_refund = ($charge->amount_refunded / 100);
            if ($order->total === $order->total_refund) {
                $order->total_refund_tax = $order->total_tax;
            }
            $order->save();

            $this->uploadsService->handleStripeRefund($order);

            CreateInvoicesFromOrder::dispatch($order->wp_id);
        }
    }

    public function handleManualRefund(Order $order, float $refundAmount)
    {
        $order->has_manual_refund = true;
        $order->total_refund += $refundAmount;
        if ($order->total_tax > 0.00) {
            $order->total_refund_tax = ($order->tax_percentage / 100) * $refundAmount;
        }
        $order->save();

        return $this->woocommerceApiService->refundOrder($order->wp_id, (string)$refundAmount);
    }

    public function handleRefund(Order $order, $orderQueues)
    {
        $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($order->wp_id);

        $refundAmount = 0.00;
        $refundTaxAmount = 0.00;
        $lineItems = [];
        foreach ($orderQueues as $orderQueue) {
            $refundAmount += $orderQueue->upload->total;
            $refundTaxAmount += $orderQueue->upload->total_tax;
            $lineItems[] = $this->orderQueuesService->getRefundLineItem($orderQueue, $orderQueue->upload->total, $wpOrder['line_items']);
            $orderQueue->upload->total_refund = $refundAmount;
            $orderQueue->upload->total_refund_tax = $orderQueue->upload->total_tax;
            $orderQueue->upload->save();
        }

        $cancelOrder = false;
        if (count($orderQueues) === $order->orderQueues->count()) {
            $refundAmount = $order->total;
            $refundTaxAmount = $order->total_tax;
            $cancelOrder = true;
        }

        if ($refundAmount > 0.00) {
            $order->total_refund = $refundAmount;
            $order->total_refund_tax = $refundTaxAmount;
            if ($cancelOrder) {
                $order->status = 'canceled';
            }
            $order->save();
        }

        $refundOrder = $this->woocommerceApiService->refundOrder($order->wp_id, (string)$refundAmount, $lineItems);

        if ($cancelOrder) {
            $this->woocommerceApiService->updateOrderStatus($order->wp_id, WcOrderStatesEnum::Cancelled->value);
        }

        return $refundOrder;
    }

    private function storeUploads($uploads, Order $order, Customer $customer, Currency $currency)
    {
        $systemUser = User::find(1);
        $biggestCustomerLeadTime = null;
        /** @var UploadDTO $uploadDto */
        foreach ($uploads as $uploadDto) {
            if ($biggestCustomerLeadTime === null || $uploadDto->customerLeadTime > $biggestCustomerLeadTime) {
                $biggestCustomerLeadTime = $uploadDto->customerLeadTime;
            }

            $model = $customer->models
                ->where('name', $uploadDto->name)
                ->where('material_id', $uploadDto->materialId)
                ->where('model_volume_cc', $uploadDto->modelVolumeCc)
                ->where('model_surface_area_cm2', $uploadDto->surfaceArea)
                ->where('model_box_volume', $uploadDto->modelBoxVolume)
                ->first();

            if ($model === null) {
                try {
                    $fileNameThumb = sprintf('%s%s.thumb.png', env('APP_SITE_STL_UPLOAD_DIR'), str_replace('_resized', '', $uploadDto->fileName));
                    $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $uploadDto->fileName);
                    $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
                    $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
                    $withoutResizedFileName = str_replace('_resized', '', $fileName);
                    $fileHeaders = get_headers($fileUrl);
                    // Check files exists on local storage of site and not on R2
                    if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileName)) {
                        Storage::disk('r2')->put($fileName, file_get_contents($fileUrl));
                    }
                    // Check files exists on local storage of site and not on R2 (without resized
                    if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($withoutResizedFileName)) {
                        Storage::disk('r2')->put($withoutResizedFileName, file_get_contents($fileUrl));
                    }
                    // Check file thumb exists on local storage of site and not on R2
                    if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileNameThumb)) {
                        Storage::disk('r2')->put($fileNameThumb, file_get_contents($fileThumb));
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }

                $model = Model::where('file_name', $withoutResizedFileName)->first();
                if ($model) {
                    $model->customer_id = $customer->id;
                    $model->file_name = $fileName;
                    $model->meta_data = $uploadDto->metaData;
                    $model->save();
                }
            }

            $name = $model->model_name ?? $uploadDto->name;
            $modelXLength = $model->model_x_length ?? null;
            $modelYLength = $model->model_y_length ?? null;
            $modelZLength = $model->model_z_length ?? null;

            $order->uploads()->create([
                'wp_id' => $uploadDto->wpId,
                'material_id' => $uploadDto->materialId,
                'customer_id' => $customer->id,
                'currency_id' => $currency->id,
                'name' => $name,
                'file_name' => sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $uploadDto->fileName),
                'material_name' => $uploadDto->materialName,
                'model_volume_cc' => $uploadDto->modelVolumeCc,
                'model_x_length' => $modelXLength,
                'model_y_length' => $modelYLength,
                'model_z_length' => $modelZLength,
                'model_box_volume' => $uploadDto->modelBoxVolume,
                'model_surface_area_cm2' => $uploadDto->surfaceArea,
                'model_parts' => 1,
                'quantity' => $uploadDto->quantity,
                'subtotal' => $uploadDto->subtotal,
                'subtotal_tax' => $uploadDto->subtotalTax,
                'total' => $uploadDto->total,
                'total_tax' => $uploadDto->totalTax,
                'currency_code' => $order->currency_code,
                'customer_lead_time' => $uploadDto->customerLeadTime,
                'meta_data' => $uploadDto->metaData,
                'created_by' => $systemUser->id,
                'updated_by' => $systemUser->id,
            ]);
        }

        return $biggestCustomerLeadTime;
    }

    private function updateUploads($uploads): void
    {
        /** @var UploadDTO $uploadDto */
        foreach ($uploads as $uploadDto) {
            $upload = Upload::where('wp_id', $uploadDto->wpId)->first();
            if ($upload) {
                $upload->update([
                    'quantity' => $uploadDto->quantity,
                    'subtotal' => $uploadDto->subtotal,
                    'subtotal_tax' => $uploadDto->subtotalTax,
                    'total' => $uploadDto->total,
                    'total_tax' => $uploadDto->totalTax,
                ]);
            }
        }
    }

    private function storeOrderLineItems($wpOrder, Order $order, Customer $customer, Country $country, Currency $currency, bool $isPaid)
    {
        $systemUser = User::find(1);
        $biggestCustomerLeadTime = null;
        foreach ($wpOrder['line_items'] as $lineItem) {
            $name = null;
            $fileName = null;
            $material = null;
            $modelVolumeCc = null;
            $modelBoxVolume = null;
            $modelXLength = 0.01;
            $modelYLength = 0.01;
            $modelZLength = 0.01;
            $surfaceArea = null;
            $customerLeadTime = null;
            foreach ($lineItem->meta_data as $metaData) {
                if ($metaData->key === 'pa_p3d_filename') {
                    $name = $metaData->value;
                }
                if ($metaData->key === 'pa_p3d_model') {
                    $fileName = $metaData->value;
                }
                if ($metaData->key === 'pa_p3d_material') {
                    [$materialId, $materialName] = array_pad(explode('. ', $metaData->value), 2, null);
                    $material = Material::where('wp_id', $materialId)->first();
                    $customerLeadTime = $material->dc_lead_time + ($country->logisticsZone->shippingFee?->default_lead_time ?? 0);
                    if ($biggestCustomerLeadTime === null || $customerLeadTime > $biggestCustomerLeadTime) {
                        $biggestCustomerLeadTime = $customerLeadTime;
                    }
                }
                if ($metaData->key === '_p3d_stats_material_volume') {
                    $modelVolumeCc = $metaData->value;
                }
                if ($metaData->key === '_p3d_stats_box_volume') {
                    $modelBoxVolume = $metaData->value;
                }
                if ($metaData->key === '_p3d_stats_surface_area') {
                    $surfaceArea = $metaData->value;
                }
            }

            $model = $customer->models
                ->where('name', $name)
                ->where('material_id', $material->id)
                ->where('model_volume_cc', $modelVolumeCc)
                ->where('model_surface_area_cm2', $surfaceArea)
                ->where('model_box_volume', $modelBoxVolume)
                ->first();

            if ($model === null) {
                $fileNameThumb = sprintf('%s%s.thumb.png', env('APP_SITE_STL_UPLOAD_DIR'), str_replace('_resized', '', $fileName));
                $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $fileName);
                $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
                $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
                $fileHeaders = get_headers($fileUrl);
                $withoutResizedFileName = str_replace('_resized', '', $fileName);

                try {
                    // Check files exists on local storage of site and not on R2
                    if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileName)) {
                        Storage::disk('r2')->put($fileName, file_get_contents($fileUrl));
                    }
                    // Check files exists on local storage of site and not on R2 (without resized
                    if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($withoutResizedFileName)) {
                        Storage::disk('r2')->put($withoutResizedFileName, file_get_contents($fileUrl));
                    }
                    // Check file thumb exists on local storage of site and not on R2
                    if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileNameThumb)) {
                        Storage::disk('r2')->put($fileNameThumb, file_get_contents($fileThumb));
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }

                $model = Model::where('file_name', $withoutResizedFileName)->first();
                if ($model) {
                    $model->customer_id = $customer->id;
                    $model->file_name = $fileName;
                    $model->meta_data = $lineItem->meta_data;
                    $model->save();
                }
            }

            $name = $model->model_name ?? $name;
            $modelXLength = $model->model_x_length ?? null;
            $modelYLength = $model->model_y_length ?? null;
            $modelZLength = $model->model_z_length ?? null;

            $order->uploads()->create([
                'wp_id' => $lineItem->id ?? null,
                'material_id' => $material->id,
                'customer_id' => $customer->id,
                'currency_id' => $currency->id,
                'name' => $name,
                'file_name' => $fileName,
                'material_name' => $material->name,
                'model_volume_cc' => $modelVolumeCc,
                'model_x_length' => $modelXLength,
                'model_y_length' => $modelYLength,
                'model_z_length' => $modelZLength,
                'model_box_volume' => $modelBoxVolume,
                'model_surface_area_cm2' => $surfaceArea,
                'model_parts' => 1,
                'quantity' => $lineItem->quantity,
                'subtotal' => $lineItem->subtotal,
                'subtotal_tax' => $lineItem->subtotal_tax,
                'total' => $lineItem->total,
                'total_tax' => $lineItem->total_tax,
                'currency_code' => $wpOrder['currency'] ?? 'USD',
                'customer_lead_time' => $customerLeadTime,
                'meta_data' => $lineItem->meta_data,
                'created_by' => $systemUser->id,
                'updated_by' => $systemUser->id,
            ]);
        }

        return $biggestCustomerLeadTime;
    }
}
