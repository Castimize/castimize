<?php

namespace App\Services\Admin;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Models\Order;
use Carbon\Carbon;
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
        $customer = Customer::where('wp_id', $request->customer_id)->first();
        $currency = Currency::where('code', $request->currency)->first();

        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->billing['address_1'], $matchBilling);
        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->shipping['address_1'], $matchShipping);

        $order = Order::create([
            'wp_id' => $request->wid,
            'customer_id' => $customer?->id,
            'currency_id' => $currency?->id,
            'order_number' => $request->number,
            'first_name' => $request->billing['first_name'],
            'last_name' => $request->billing['last_name'],
            'email' => $request->billing['email'],
            'billing_first_name' => $request->billing['first_name'],
            'billing_last_name' => $request->billing['last_name'],
            'billing_phone_number' => $request->billing['phone'],
            'billing_address_line1' => $matchBilling[1] ?? $request->billing['address_1'],
            'billing_address_line2' => $request->billing['address_2'],
            'billing_house_number' => $matchBilling[2] ?? null,
            'billing_postal_code' => $request->billing['postcode'],
            'billing_city' => $request->billing['city'],
            'billing_country' => $request->billing['country'],
            'shipping_first_name' => $request->shipping['first_name'],
            'shipping_last_name' => $request->shipping['last_name'],
            'shipping_phone_number' => $request->shipping['phone'],
            'shipping_address_line1' => $matchShipping[1] ?? $request->shipping['address_1'],
            'shipping_address_line2' => $request->shipping['address_2'],
            'shipping_house_number' => $matchShipping[2] ?? null,
            'shipping_postal_code' => $request->shipping['postcode'],
            'shipping_city' => $request->shipping['city'],
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
            'currency_code' => $request->currency ?? 'EUR',
            'payment_method' => $request->payment_method_title,
            'payment_issuer' => $request->payment_method,
            'customer_ip_address' => $request->customer_ip_address,
            'customer_user_agent' => $request->customer_user_agent,
            'meta_data' => $request->meta_data,
            'comments' => $request->customer_note,
            'promo_code' => null,
            'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $request->date_created_gmt), 'GMT')?->setTimezone(env('APP_TIMEZONE')),
            'updated_at' => Carbon::createFromFormat('Y-m-d H:i:s', str_replace('T', '', $request->date_modified_gmt), 'GMT')?->setTimezone(env('APP_TIMEZONE')),
        ]);

        $biggestCustomerLeadTime = null;
        foreach ($request->line_items as $lineItem) {
            $name = null;
            $fileName = null;
            $material = null;
            $modelVolumeCc = null;
            $modelBoxVolume = null;
            $modelXLength = null;
            $modelYLength = null;
            $modelZLength = null;
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
                    $customerLeadTime = $material->customer_lead_time;
                    if ($biggestCustomerLeadTime === null || $material->customer_lead_time > $biggestCustomerLeadTime) {
                        $biggestCustomerLeadTime = $material->customer_lead_time;
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
            $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $fileName);
            $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
            $fileHeaders = get_headers($fileUrl);

            // Check files exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileName)) {
                Storage::disk('r2')->put($fileName, file_get_contents($fileUrl));
            }

            $model = Model::where('file_name', $fileName)->first();
            if ($model) {
                $modelXLength = $model->model_x_length;
                $modelYLength = $model->model_y_length;
                $modelZLength = $model->model_z_length;

                $model->customer_id = $customer?->id;
                $model->save();
            }

            $order->uploads()->create([
                'material_id' => $material->id,
                'customer_id' => $customer?->id,
                'currency_id' => $currency?->id,
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
                'currency_code' => $request->currency ?? 'EUR',
                'customer_lead_time' => $customerLeadTime,
            ]);
        }
        $order->order_customer_lead_time = $biggestCustomerLeadTime;
        $order->save();

        return $order;
    }
}
