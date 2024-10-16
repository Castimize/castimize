<?php

namespace App\Services\Admin;

use App\Jobs\UploadToOrderQueue;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrdersService
{
    /**
     * Store a order completely from WP API request
     * @param $request
     * @return Order
     */
    public function storeOrderWpFromApi($request): Order
    {
        $systemUser = User::find(1);
        $country = Country::where('alpha2', strtolower($request->billing['country']))->first();
        if ($country === null) {
            $country = Country::where('alpha2', 'nl')->first();
        }
        $customer = null;
        if (!empty($request->customer_id)) {
            $customer = Customer::where('wp_id', $request->customer_id)->first();
            if ($customer === null) {
                $customer = (new CustomersService())->storeCustomerFromWpApi($request);
            }
        }

        $currency = Currency::where('code', $request->currency)->first();
        if ($currency === null) {
            $currency = Currency::where('code', 'USD')->first();
        }

        $stripePaymentId = null;
        $billingVatNumber = null;
        foreach ($request->meta_data as $orderMetaData) {
            if ($orderMetaData['key'] === '_billing_eu_vat_number') {
                $billingVatNumber = $orderMetaData['value'];
            }
            if ($orderMetaData['key'] === '_stripe_intent_id') {
                $stripePaymentId = $orderMetaData['value'];
            }
        }

        $createdAt = Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $request->date_created_gmt), 'GMT')?->setTimezone(env('APP_TIMEZONE'));

        $order = Order::create([
            'wp_id' => $request->id,
            'customer_id' => $customer->id,
            'currency_id' => $currency->id,
            'country_id' => $country->id,
            'order_number' => $request->number,
            'order_key' => $request->order_key,
            'first_name' => $request->billing['first_name'],
            'last_name' => $request->billing['last_name'],
            'email' => $request->billing['email'],
            'billing_first_name' => $request->billing['first_name'],
            'billing_last_name' => $request->billing['last_name'],
            'billing_company' => $request->billing['company'],
            'billing_phone_number' => $request->billing['phone'],
            'billing_email' => $request->billing['email'],
            'billing_address_line1' => $request->billing['address_1'],
            'billing_address_line2' => $request->billing['address_2'],
            'billing_postal_code' => $request->billing['postcode'],
            'billing_city' => $request->billing['city'],
            'billing_state' => $request->billing['state'] ?? null,
            'billing_country' => $request->billing['country'],
            'billing_vat_number' => $billingVatNumber,
            'shipping_first_name' => $request->shipping['first_name'],
            'shipping_last_name' => $request->shipping['last_name'],
            'shipping_company' => $request->shipping['company'],
            'shipping_phone_number' => $request->shipping['phone'] ?? null,
            'shipping_email' => $request->shipping['email'] ?? null,
            'shipping_address_line1' => $request->shipping['address_1'],
            'shipping_address_line2' => $request->shipping['address_2'],
            'shipping_postal_code' => $request->shipping['postcode'],
            'shipping_city' => $request->shipping['city'],
            'shipping_state' => $request->shipping['state'] ?? null,
            'shipping_country' => $request->shipping['country'],
            'service_fee' => null,
            'service_fee_tax' => null,
            'shipping_fee' => $request->shipping_total,
            'shipping_fee_tax' => $request->shipping_tax,
            'discount_fee' => $request->discount_total,
            'discount_fee_tax' => $request->discount_tax,
            'total' => $request->total,
            'total_tax' => $request->total_tax,
            'production_cost' => null,
            'production_cost_tax' => null,
            'prices_include_tax' => $request->prices_include_tax ?? true,
            'currency_code' => $request->currency ?? 'USD',
            'payment_method' => $request->payment_method_title,
            'payment_issuer' => $request->payment_method,
            'payment_intent_id' => $stripePaymentId,
            'customer_ip_address' => $request->customer_ip_address,
            'customer_user_agent' => $request->customer_user_agent,
            'meta_data' => $request->meta_data,
            'comments' => $request->customer_note,
            'promo_code' => null,
            'is_paid' => true,
            'paid_at' => $createdAt,
            'created_by' => $systemUser->id,
            'created_at' => $createdAt,
            'updated_by' => $systemUser->id,
            'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $request->date_modified_gmt), 'GMT')?->setTimezone(env('APP_TIMEZONE')),
        ]);

        $biggestCustomerLeadTime = null;
        foreach ($request->line_items as $lineItem) {
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
            foreach ($lineItem['meta_data'] as $metaData) {
                if ($metaData['key'] === 'pa_p3d_filename') {
                    $name = $metaData['value'];
                }
                if ($metaData['key'] === 'pa_p3d_model') {
                    $fileName = $metaData['value'];
                }
                if ($metaData['key'] === 'pa_p3d_material') {
                    [$materialId, $materialName] = explode('. ', $metaData['value']);
                    $material = Material::where('wp_id', $materialId)->first();
                    $customerLeadTime = $material->dc_lead_time + ($country->logisticsZone->shippingFee?->default_lead_time ?? 0);
                    if ($biggestCustomerLeadTime === null || $customerLeadTime > $biggestCustomerLeadTime) {
                        $biggestCustomerLeadTime = $customerLeadTime;
                    }
                }
                if ($metaData['key'] === '_p3d_stats_material_volume') {
                    $modelVolumeCc = $metaData['value'];
                }
                if ($metaData['key'] === '_p3d_stats_box_volume') {
                    $modelBoxVolume = $metaData['value'];
                }
                if ($metaData['key'] === '_p3d_stats_surface_area') {
                    $surfaceArea = $metaData['value'];
                }
            }
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
                $modelXLength = $model->model_x_length;
                $modelYLength = $model->model_y_length;
                $modelZLength = $model->model_z_length;

                $model->customer_id = $customer->id;
                $model->file_name = $fileName;
                $model->meta_data = $lineItem['meta_data'];
                $model->save();
            }

            $upload = $order->uploads()->create([
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
                'quantity' => $lineItem['quantity'],
                'subtotal' => $lineItem['subtotal'],
                'subtotal_tax' => $lineItem['subtotal_tax'],
                'total' => $lineItem['total'],
                'total_tax' => $lineItem['total_tax'],
                'currency_code' => $request->currency ?? 'USD',
                'customer_lead_time' => $customerLeadTime,
                'meta_data' => $lineItem['meta_data'],
                'created_by' => $systemUser->id,
                'updated_by' => $systemUser->id,
            ]);

            // Set upload to order queue
            UploadToOrderQueue::dispatch($upload);
        }
        $order->order_customer_lead_time = $biggestCustomerLeadTime;
        $order->due_date = Carbon::parse($order->created_at)->addBusinessDays($biggestCustomerLeadTime);
        $order->save();

        return $order;
    }
}
