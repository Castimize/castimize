<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Enums\Admin\CurrencyEnum;
use App\Enums\Woocommerce\WcOrderStatesEnum;
use Codexshaper\WooCommerce\Models\Order;
use stdClass;

trait NeedsWoocommerceModel
{
    protected function getWoocommerceOrder(
        ?int $orderNumber = null,
        ?WcOrderStatesEnum $wcOrderStatesEnum = null,
        ?CurrencyEnum $currencyEnum = null,
    ) {
        $orderNumber = $orderNumber ?? fake()->numberBetween(1000, 9999);
        $wpOrder = [];
        $wpOrder['id'] = $orderNumber;
        $wpOrder['total'] = '211.51';
        $wpOrder['number'] = $orderNumber;
        $wpOrder['status'] = $wcOrderStatesEnum->value ?? fake()->randomElement(WcOrderStatesEnum::cases());
        $wpOrder['billing'] = new stdClass();
        $wpOrder['billing']->city = 'Amsterdam';
        $wpOrder['billing']->email = 'castimize@gmail.com';
        $wpOrder['billing']->phone = '+31612345678';
        $wpOrder['billing']->state = 'NH';
        $wpOrder['billing']->company = 'Castimize';
        $wpOrder['billing']->country = 'NL';
        $wpOrder['billing']->postcode = '1111AA';
        $wpOrder['billing']->address_1 = 'Teststraat 1';
        $wpOrder['billing']->address_2 = '';
        $wpOrder['billing']->last_name = 'de Tester';
        $wpOrder['billing']->first_name = 'Piet';
        $wpOrder['shipping'] = new stdClass();
        $wpOrder['shipping']->city = 'Amsterdam';
        $wpOrder['shipping']->email = 'castimize@gmail.com';
        $wpOrder['shipping']->phone = '+31612345678';
        $wpOrder['shipping']->state = 'NH';
        $wpOrder['shipping']->company = 'Castimize';
        $wpOrder['shipping']->country = 'NL';
        $wpOrder['shipping']->postcode = '1111AA';
        $wpOrder['shipping']->address_1 = 'Teststraat 1';
        $wpOrder['shipping']->address_2 = '';
        $wpOrder['shipping']->last_name = 'de Tester';
        $wpOrder['shipping']->first_name = 'Piet';
        $wpOrder['cart_tax'] = '0.00';
        $wpOrder['currency'] = $currencyEnum->value ?? CurrencyEnum::USD->value;
        $wpOrder['date_paid'] = now()->format('c');
        $wpOrder['documents'] = new stdClass();
        $wpOrder['documents']->invoice = new stdClass();
        $wpOrder['documents']->invoice->date = now()->addMinute()->format('c');
        $wpOrder['documents']->invoice->number = fake()->numberBetween(1, 999);
        $wpOrder['documents']->invoice->date_timestamp = now()->addMinute()->timestamp;
        $wpOrder['fee_lines'] = new stdClass();
        $wpOrder['fee_lines']->id = fake()->numberBetween(1, 9999);
        $wpOrder['fee_lines']->name = 'Paypal usage & Handling fee';
        $wpOrder['fee_lines']->taxes = [];
        $wpOrder['fee_lines']->total = '8.14';
        $wpOrder['fee_lines']->amount = '8.135092';
        $wpOrder['fee_lines']->tax_class = null;
        $wpOrder['fee_lines']->total_tax = '0.00';
        $wpOrder['fee_lines']->tax_status = 'taxable';
        $wpOrder['fee_lines']->meta_data = [];
        $feeLineMetaData = new stdClass();
        $feeLineMetaData->id = fake()->numberBetween(1, 99999);
        $feeLineMetaData->key = '_last_added_fee';
        $feeLineMetaData->value = 'Paypal usage & Handling fee';
        $feeLineMetaData->display_key = '_last_added_fee';
        $feeLineMetaData->display_value = 'Paypal usage & Handling fee';
        $wpOrder['fee_lines']->meta_data[] = $feeLineMetaData;

        $wpOrder->meta_data = [
            [
                'id' => 9457,
                'key' => '_billing_eu_vat_number',
                'value' => 'NL866959300B01',
            ],
            [
                'id' => 9488,
                'key' => '_ga_tracked',
                'value' => '1',
            ],
            [
                'id' => 9476,
                'key' => '_paypal_fee',
                'value' => '7.54',
            ],
            [
                'id' => 9477,
                'key' => '_paypal_net',
                'value' => '203.97',
            ],
            [
                'id' => 9487,
                'key' => '_paypal_payer_id',
                'value' => 'CSBMEZRGEU7TG',
            ],
            [
                'id' => 9486,
                'key' => '_ppcp_environment',
                'value' => 'production',
            ],
            [
                'id' => 9485,
                'key' => '_ppcp_paypal_order_id',
                'value' => '46T10311R2067902Y',
            ],
            [
                'id' => 9458,
                'key' => '_shipping_email',
                'value' => 'castimize@gmail.com',
            ],
            [
                'id' => 9475,
                'key' => '_wc_order_attribution_device_type',
                'value' => 'Desktop',
            ],
            [
                'id' => 9467,
                'key' => '_wc_order_attribution_referrer',
                'value' => 'https://www.google.com/',
            ],
            [
                'id' => 9473,
                'key' => '_wc_order_attribution_session_count',
                'value' => '1',
            ],
            [
                'id' => 9470,
                'key' => '_wc_order_attribution_session_entry',
                'value' => 'https://castimize.com/',
            ],
            [
                'id' => 9472,
                'key' => '_wc_order_attribution_session_pages',
                'value' => '5',
            ],
            [
                'id' => 9471,
                'key' => '_wc_order_attribution_session_start_time',
                'value' => '2025-04-07 18:07:58',
            ],
            [
                'id' => 9466,
                'key' => '_wc_order_attribution_source_type',
                'value' => 'organic',
            ],
            [
                'id' => 9474,
                'key' => '_wc_order_attribution_user_agent',
                'value' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
            ],
            [
                'id' => 9469,
                'key' => '_wc_order_attribution_utm_medium',
                'value' => 'organic',
            ],
            [
                'id' => 9468,
                'key' => '_wc_order_attribution_utm_source',
                'value' => 'google',
            ],
            [
                'id' => 9480,
                'key' => '_wcpdf_invoice_creation_trigger',
                'value' => 'email_attachment',
            ],
            [
                'id' => 9481,
                'key' => '_wcpdf_invoice_date',
                'value' => '1744049399',
            ],
            [
                'id' => 9482,
                'key' => '_wcpdf_invoice_date_formatted',
                'value' => '2025-04-07 20:09:59',
            ],
            [
                'id' => 9479,
                'key' => '_wcpdf_invoice_display_date',
                'value' => 'invoice_date',
            ],
            [
                'id' => 9483,
                'key' => '_wcpdf_invoice_number',
                'value' => '240',
            ],
            [
                'id' => 9484,
                'key' => '_wcpdf_invoice_number_data',
                'value' => [
                'number' => 240,
                    'prefix' => null,
                    'suffix' => null,
                    'padding' => null,
                    'order_id' => 6987,
                    'document_type' => 'invoice',
                    'formatted_number' => '240',
                ],
            ],
            [
                'id' => 9478,
                'key' => '_wcpdf_invoice_settings',
                'value' => [
                'footer' => [
                    'default' => null,
                    ],
                    'extra_1' => [
                        'default' => null,
                    ],
                    'extra_2' => [
                        'default' => null,
                    ],
                    'extra_3' => [
                        'default' => null,
                    ],
                    'shop_name' => [
                        'default' => 'Castimize B.V.',
                    ],
                    'coc_number' => '94982007',
                    'vat_number' => 'NL866959300B01',
                    'header_logo' => [
                        'default' => '3604',
                    ],
                    'display_date' => 'invoice_date',
                    'shop_address' => [
                        'default' => 'Sint Pietershalsteeg 4\r\n1012GL Amsterdam\r\nNetherlands\r\n\r\nCoC: 94982007\r\nVAT: NL866959300B01',
                    ],
                    'display_email' => '1',
                    'display_number' => 'invoice_number',
                    'shop_phone_number' => [
                        'default' => null,
                    ],
                    'header_logo_height' => null,
                    'use_latest_settings' => '1',
                    'display_customer_notes' => '1',
                    'display_shipping_address' => 'when_different',
                ],
            ],
            [
                'id' => 9460,
                'key' => 'activecampaign_for_woocommerce_external_checkout_id',
                'value' => '67dd797a8bdc34.53271579',
            ],
            [
                'id' => 9464,
                'key' => 'billing_eu_vat_number_details',
                'value' => [
                'vat_number' => [
                        'data' => '866959300B01',
                        'label' => 'VAT Number',
                    ],
                    'country_code' => [
                        'data' => 'NL',
                        'label' => 'Country Code',
                    ],
                    'business_name' => [
                        'data' => '---',
                        'label' => 'Business Name',
                    ],
                    'business_address' => [
                        'data' => '---',
                        'label' => 'Business Address',
                    ],
                ],
            ],
            [
                'id' => 9459,
                'key' => 'is_vat_exempt',
                'value' => 'yes',
            ],
            [
                'id' => 9465,
                'key' => 'wcpdf_order_locale',
                'value' => 'en_US',
            ],
            [
                'id' => 9463,
                'key' => 'wmc_order_info',
                'value' => [
                    'EUR' => [
                        'pos' => 'left_space',
                        'hide' => '0',
                        'rate' => '0.90470',
                        'custom' => null,
                        'decimals' => '2',
                        'decimal_sep' => null,
                        'thousand_sep' => null,
                        ],
                    'USD' => [
                        'pos' => 'left_space',
                        'hide' => '0',
                        'rate' => 1,
                        'custom' => null,
                        'is_main' => 1,
                        'decimals' => '2',
                        'decimal_sep' => null,
                        'thousand_sep' => null,
                    ],
                ],
            ],
        ];
        $wpOrder->order_key = 'wc_order_yIjuV9aoUqHSg';
        $wpOrder->parent_id = 0;
        $wpOrder->tax_lines = [];
        $wpOrder->total_tax = '0.00';
        $wpOrder->line_items = [
            [
                'id' => 1674,
                'sku' => null,
                'name' => '3D',
                'image' => [
                'id' => null,
                    'src' => null,
                ],
                'price' => 27.141,
                'taxes' => [],
                'total' => '108.56',
                'quantity' => 4,
                'subtotal' => '108.56',
                'meta_data' => [
                    [
                        'id' => 25502,
                        'key' => 'pa_p3d_printer',
                        'value' => '3. Default',
                        'display_key' => 'Printer',
                        'display_value' => '3. Default',
                    ],
                    [
                        'id' => 25503,
                        'key' => 'pa_p3d_filename',
                        'value' => 'venus von willendorf 3nf.stl',
                        'display_key' => 'Filename',
                        'display_value' => 'venus von willendorf 3nf.stl',
                    ],
                    [
                        'id' => 25504,
                        'key' => 'pa_p3d_material',
                        'value' => '5. 14k Yellow Gold Plated Brass',
                        'display_key' => 'Material',
                        'display_value' => '5. 14k Yellow Gold Plated Brass',
                    ],
                    [
                        'id' => 25505,
                        'key' => 'pa_p3d_model',
                        'value' => '67dd79487dbe7_0a7a0d7be4a50326ecc86debbfac135e.stl',
                        'display_key' => 'Model',
                        'display_value' => '67dd79487dbe7_0a7a0d7be4a50326ecc86debbfac135e.stl',
                    ],
                    [
                        'id' => 25506,
                        'key' => 'pa_p3d_unit',
                        'value' => 'mm',
                        'display_key' => 'Unit',
                        'display_value' => 'mm',
                    ],
                    [
                        'id' => 25507,
                        'key' => 'pa_p3d_scale',
                        'value' => '&times;1 (1.04 &times; 0.87 &times; 2.58 cm)',
                        'display_key' => 'Scale',
                        'display_value' => '&times;1 (1.04 &times; 0.87 &times; 2.58 cm)',
                    ],
                    [
                        'id' => 25508,
                        'key' => '_p3d_stats_material_volume',
                        'value' => '0.69',
                        'display_key' => 'Material Volume',
                        'display_value' => '0.69cm3',
                    ],
                    [
                        'id' => 25509,
                        'key' => '_p3d_stats_print_time',
                        'value' => '0',
                        'display_key' => 'Print Time',
                        'display_value' => '00:00:00',
                    ],
                    [
                        'id' => 25510,
                        'key' => '_p3d_stats_surface_area',
                        'value' => '5.79',
                        'display_key' => 'Surface Area',
                        'display_value' => '5.79cm2',
                    ],
                    [
                        'id' => 25511,
                        'key' => '_p3d_stats_polygons',
                        'value' => '63164',
                        'display_key' => 'Number of Polygons',
                        'display_value' => '63164',
                    ],
                    [
                        'id' => 25512,
                        'key' => '_p3d_stats_weight',
                        'value' => '6.21',
                        'display_key' => 'Model Weight',
                        'display_value' => '6.21g',
                    ],
                    [
                        'id' => 25513,
                        'key' => '_p3d_stats_box_volume',
                        'value' => '2.32',
                        'display_key' => 'Box Volume',
                        'display_value' => '2.32cm3',
                    ],
                ],
                'tax_class' => null,
                'total_tax' => '0.00',
                'product_id' => 3228,
                'parent_name' => null,
                'subtotal_tax' => '0.00',
                'variation_id' => 0,
            ],
        ];
        $wpOrder->created_via = 'checkout';
        $wpOrder->customer_id = 125;
        $wpOrder->is_editable = false;
        $wpOrder->payment_url = 'https://castimize.com/check-out/order-pay/6987/?pay_for_order=true&key=wc_order_yIjuV9aoUqHSg';
        $wpOrder->coupon_lines = [];
        $wpOrder->date_created = '2025-04-07T20:09:55';
        $wpOrder->discount_tax = '0.00';
        $wpOrder->shipping_tax = '0.00';
        $wpOrder->customer_note = null;
        $wpOrder->date_modified = '2025-04-07T20:10:03';
        $wpOrder->date_paid_gmt = '2025-04-07T18:09:58';
        $wpOrder->needs_payment = false;
        $wpOrder->date_completed = null;
        $wpOrder->discount_total = '0.00';
        $wpOrder->payment_method = 'ppcp';
        $wpOrder->shipping_lines = [
            [
                'id' => 1678,
                'taxes' => [],
                'total' => '9.16',
                'meta_data' => [
                    [
                        'id' => 25567,
                        'key' => 'Items',
                        'value' => '3D &times; 4, 3D &times; 1, 3D &times; 2',
                        'display_key' => 'Items',
                        'display_value' => '3D &times; 4, 3D &times; 1, 3D &times; 2',
                    ],
                ],
                'method_id' => 'flat_rate',
                'total_tax' => '0.00',
                'tax_status' => 'taxable',
                'instance_id' => '3',
                'method_title' => 'Rate',
            ],
        ];
        $wpOrder->shipping_total = '9.16';
        $wpOrder->transaction_id = '9VV50308AU002902S';
        $wpOrder->currency_symbol = '€';
        $wpOrder->date_created_gmt = '2025-04-07T18:09:55';
        $wpOrder->needs_processing = true;
        $wpOrder->date_modified_gmt = '2025-04-07T18:10:03';
        $wpOrder->date_completed_gmt = null;
        $wpOrder->prices_include_tax = false;
        $wpOrder->customer_ip_address = '2003:e2:73f:3200:8d4e:5077:ad8a:323e';
        $wpOrder->customer_user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36';
        $wpOrder->payment_method_title = 'PayPal - castimize@gmail.com';

        return $wpOrder;
    }
}
