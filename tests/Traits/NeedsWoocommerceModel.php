<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Enums\Admin\CurrencyEnum;
use App\Enums\Woocommerce\WcOrderStatesEnum;
use stdClass;

trait NeedsWoocommerceModel
{
    protected function getWoocommerceOrder(
        ?int $orderNumber = null,
        ?WcOrderStatesEnum $wcOrderStatesEnum = null,
        ?CurrencyEnum $currencyEnum = null,
    ): array {
        $orderNumber = $orderNumber ?? fake()->numberBetween(1000, 9999);

        $billing = new stdClass;
        $billing->city = 'Amsterdam';
        $billing->email = 'castimize@gmail.com';
        $billing->phone = '+31612345678';
        $billing->state = 'NH';
        $billing->company = 'Castimize';
        $billing->country = 'NL';
        $billing->postcode = '1111AA';
        $billing->address_1 = 'Teststraat 1';
        $billing->address_2 = '';
        $billing->last_name = 'de Tester';
        $billing->first_name = 'Piet';

        $shipping = new stdClass;
        $shipping->city = 'Amsterdam';
        $shipping->email = 'castimize@gmail.com';
        $shipping->phone = '+31612345678';
        $shipping->state = 'NH';
        $shipping->company = 'Castimize';
        $shipping->country = 'NL';
        $shipping->postcode = '1111AA';
        $shipping->address_1 = 'Teststraat 1';
        $shipping->address_2 = '';
        $shipping->last_name = 'de Tester';
        $shipping->first_name = 'Piet';

        // Create meta_data as array of objects (service expects $orderMetaData->key)
        $metaData = [];

        $meta = new stdClass;
        $meta->id = 9457;
        $meta->key = '_billing_eu_vat_number';
        $meta->value = 'NL866959300B01';
        $metaData[] = $meta;

        $meta = new stdClass;
        $meta->id = 9458;
        $meta->key = '_shipping_email';
        $meta->value = 'castimize@gmail.com';
        $metaData[] = $meta;

        // Create line_items with meta_data as objects
        $lineItemMetaData = [];

        $itemMeta = new stdClass;
        $itemMeta->id = 25503;
        $itemMeta->key = 'pa_p3d_filename';
        $itemMeta->value = 'venus von willendorf 3nf.stl';
        $lineItemMetaData[] = $itemMeta;

        $itemMeta = new stdClass;
        $itemMeta->id = 25504;
        $itemMeta->key = 'pa_p3d_material';
        $itemMeta->value = '5. 14k Yellow Gold Plated Brass';
        $lineItemMetaData[] = $itemMeta;

        $itemMeta = new stdClass;
        $itemMeta->id = 25505;
        $itemMeta->key = 'pa_p3d_model';
        $itemMeta->value = '67dd79487dbe7_0a7a0d7be4a50326ecc86debbfac135e.stl';
        $lineItemMetaData[] = $itemMeta;

        $itemMeta = new stdClass;
        $itemMeta->id = 25508;
        $itemMeta->key = '_p3d_stats_material_volume';
        $itemMeta->value = '0.69';
        $lineItemMetaData[] = $itemMeta;

        $itemMeta = new stdClass;
        $itemMeta->id = 25510;
        $itemMeta->key = '_p3d_stats_surface_area';
        $itemMeta->value = '5.79';
        $lineItemMetaData[] = $itemMeta;

        $itemMeta = new stdClass;
        $itemMeta->id = 25513;
        $itemMeta->key = '_p3d_stats_box_volume';
        $itemMeta->value = '2.32';
        $lineItemMetaData[] = $itemMeta;

        $lineItem = new stdClass;
        $lineItem->id = 1674;
        $lineItem->quantity = 4;
        $lineItem->subtotal = '108.56';
        $lineItem->subtotal_tax = '0.00';
        $lineItem->total = '108.56';
        $lineItem->total_tax = '0.00';
        $lineItem->meta_data = $lineItemMetaData;

        return [
            'id' => $orderNumber,
            'total' => '211.51',
            'number' => $orderNumber,
            'status' => $wcOrderStatesEnum?->value ?? WcOrderStatesEnum::Processing->value,
            'billing' => $billing,
            'shipping' => $shipping,
            'cart_tax' => '0.00',
            'currency' => $currencyEnum?->value ?? CurrencyEnum::USD->value,
            'date_paid' => now()->format('c'),
            'meta_data' => $metaData,
            'order_key' => 'wc_order_'.fake()->uuid(),
            'tax_lines' => [],
            'total_tax' => '0.00',
            'line_items' => [$lineItem],
            'customer_id' => 125,
            'date_created' => now()->format('Y-m-d\TH:i:s'),
            'discount_tax' => '0.00',
            'shipping_tax' => '0.00',
            'customer_note' => null,
            'date_modified' => now()->format('Y-m-d\TH:i:s'),
            'date_paid_gmt' => now()->format('Y-m-d\TH:i:s'),
            'discount_total' => '0.00',
            'payment_method' => 'ppcp',
            'shipping_total' => '9.16',
            'date_created_gmt' => now()->format('Y-m-d\TH:i:s'),
            'date_modified_gmt' => now()->format('Y-m-d\TH:i:s'),
            'prices_include_tax' => false,
            'customer_ip_address' => '127.0.0.1',
            'customer_user_agent' => 'PHPUnit Test',
            'payment_method_title' => 'PayPal - test@example.com',
        ];
    }
}
