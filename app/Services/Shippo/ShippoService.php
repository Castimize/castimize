<?php

namespace App\Services\Shippo;

use App\Enums\Admin\CurrencyEnum;
use App\Enums\Shippo\ShippoCustomsDeclarationContentTypesEnum;
use App\Enums\Shippo\ShippoCustomsDeclarationIncoTermsEnum;
use App\Enums\Shippo\ShippoCustomsDeclarationNonDeliveryOptionsEnum;
use App\Enums\Shippo\ShippoLabelFileTypesEnum;
use App\Enums\Shippo\ShippoOrderStatusesEnum;
use App\Models\Material;
use App\Models\Order;
use App\Models\Upload;
use App\Nova\Settings\Shipping\CustomsItemSettings;
use App\Nova\Settings\Shipping\DcSettings;
use App\Nova\Settings\Shipping\GeneralSettings;
use App\Nova\Settings\Shipping\PickupSettings;
use App\Services\Admin\LogRequestService;
use Carbon\Carbon;
use JsonException;
use Shippo_Address;
use Shippo_ApiRequestor;
use Shippo_CustomsDeclaration;
use Shippo_CustomsItem;
use Shippo_Object;
use Shippo_Order;
use Shippo_Parcel;
use Shippo_Pickup;
use Shippo_Shipment;
use Shippo_Transaction;

class ShippoService
{
    public $packageTypes = [];

    protected $_carriers = [];

    protected $_services = [];

    private $_fromAddress = [];

    private $_toAddress = [];

    private $_shipmentFromAddress;

    private $_shipmentToAddress;

    private $_orderLineItems = [];

    private $_order;

    private $_parcel;

    private $_customsItems;

    private $_customsDeclaration;

    private $_shipment;

    private $_transaction;

    public function __construct(
        public GeneralSettings $generalSettings,
        public CustomsItemSettings $customsItemSettings,
        public DcSettings $dcSettings,
        public PickupSettings $pickupSettings,
    ) {
        $this->initPackageTypes();
        $this->initCarriers();
        $this->initServices();
    }

    public function getServices(): array
    {
        return $this->_services;
    }

    public function getCarriers(): array
    {
        return $this->_carriers;
    }

    public function getFromAddress(): array
    {
        return $this->_fromAddress;
    }

    public function setFromAddress(array $address): static
    {
        $this->_fromAddress = $address;

        return $this;
    }

    public function getToAddress(): array
    {
        return $this->_toAddress;
    }

    /**
     * @return $this
     */
    public function setToAddress(array $address): static
    {
        $this->_toAddress = $address;

        return $this;
    }

    public function getShipmentFromAddress(): Shippo_Object
    {
        return $this->_shipmentFromAddress;
    }

    /**
     * @return $this
     */
    public function setShipmentFromAddress(Shippo_Object $shipmentFromAddress): static
    {
        $this->_shipmentFromAddress = $shipmentFromAddress;

        return $this;
    }

    public function getShipmentToAddress(): Shippo_Object
    {
        return $this->_shipmentToAddress;
    }

    /**
     * @return $this
     */
    public function setShipmentToAddress(Shippo_Object $shipmentToAddress): static
    {
        $this->_shipmentToAddress = $shipmentToAddress;

        return $this;
    }

    public function getOrder(): Shippo_Object
    {
        return $this->_order;
    }

    public function getShipment(): Shippo_Object
    {
        return $this->_shipment;
    }

    public function getTransaction(): Shippo_Object
    {
        return $this->_transaction;
    }

    /**
     * @throws JsonException
     */
    public function getCacheKey(array $params): string
    {
        $params['only_shippo_address'] = 1;
        if (isset($params['service'])) {
            unset($params['service']);
        }

        if (isset($params['rate_id'])) {
            unset($params['rate_id']);
        }

        $params['api'] = config('services.shippo.key');

        if (isset($params['function'])) {
            unset($params['function']);
        }

        return md5(json_encode($params, JSON_THROW_ON_ERROR));
    }

    public function createFromAddress(bool $validate = true): static
    {
        if ($validate) {
            $this->_fromAddress['validate'] = true;
        }
        $this->_shipmentFromAddress = Shippo_Address::create($this->_fromAddress);
        $this->logShippoCall(
            pathInfo: 'addresses',
            requestUri: 'addresses',
            method: 'POST',
            request: $this->_fromAddress,
            response: json_decode($this->_shipmentFromAddress, true, 512, JSON_THROW_ON_ERROR),
        );

        return $this;
    }

    public function createToAddress(bool $validate = true): static
    {
        if ($validate) {
            $this->_toAddress['validate'] = true;
        }
        $this->_shipmentToAddress = Shippo_Address::create($this->_toAddress);
        $this->logShippoCall(
            pathInfo: 'addresses',
            requestUri: 'addresses',
            method: 'POST',
            request: $this->_toAddress,
            response: json_decode($this->_shipmentToAddress, true, 512, JSON_THROW_ON_ERROR),
        );

        return $this;
    }

    public function createOrderLineItems($lineItems): static
    {
        foreach ($lineItems as $lineItem) {
            $this->_orderLineItems[] = [
                'currency' => $lineItem->currency,
                'quantity' => $lineItem->quantity,
                'title' => $lineItem->name,
                'total_price' => $lineItem->total,
                'weight' => $lineItem->model_box_volume * $lineItem->material->density + $this->customsItemSettings->bag, // (in gram) = volume (cm3) * density + bag
                'weight_unit' => $this->customsItemSettings->massUnit,
            ];
        }

        return $this;
    }

    public function createOrder(Order $order): static
    {
        $weight = 0;
        foreach ($this->_orderLineItems as $lineItem) {
            $weight += $lineItem['weight'];
        }
        $this->_order = Shippo_Order::create([
            'currency' => $order->currency_code,
            'notes' => $order->comments,
            'order_number' => $order->order_number,
            'order_status' => ShippoOrderStatusesEnum::PAID->value,
            'placed_at' => str_replace('+00:00', 'Z', Carbon::parse($order->created_at)->setTimezone('UTC')->format('c')),
            'shipping_cost' => (string) $order->shipping_fee,
            'shipping_cost_currency' => $order->currency_code,
            'total_price' => (string) $order->total,
            'total_tax' => (string) $order->total_tax,
            'weight' => $weight,
            'weight_unit' => $this->customsItemSettings->massUnit,
            'from_address' => $this->_shipmentFromAddress,
            'to_address' => $this->_shipmentToAddress,
            'line_items' => $this->_orderLineItems,
        ]);

        return $this;
    }

    public function createParcel(array $params): static
    {
        $this->_parcel = Shippo_Parcel::create([
            'distance_unit' => $params['distance_unit'],
            'length' => $params['length'],
            'width' => $params['width'],
            'height' => $params['height'],
            'mass_unit' => $params['mass_unit'],
            'weight' => $params['weight'],
        ]);

        return $this;
    }

    public function createCustomsItem(Upload $upload, bool $isCustomerShipment = true): static
    {
        $material = $upload->material;
        $description = $material?->hs_code_description;
        $tariffNUmber = $material?->hs_code;
        if ($description === null) {
            $material = Material::where('name', $upload->material_name)->first();
            $description = $material->hs_code_description;
            $tariffNUmber = $material->hs_code;
        }
        $netWeight = ($upload->model_volume_cc * $material->density + $this->customsItemSettings->bag) * $upload->quantity;
        $customsItem = Shippo_CustomsItem::create([
            'description' => $description,
            'quantity' => $upload->quantity,
            'net_weight' => round($netWeight, 2), // (in gram) = volume (cm3) * density + bag
            'mass_unit' => $this->customsItemSettings->massUnit,
            'value_amount' => $upload->total > 0.00 ? $upload->total : 1.00,
            'value_currency' => $upload->total > 0.00 ? ($upload->currency_code ?? CurrencyEnum::USD->value) : CurrencyEnum::USD->value,
            'origin_country' => $isCustomerShipment ? 'NL' : 'US',
            'tariff_number' => $tariffNUmber,
            'metadata' => __('Order ID #:order_number', [
                'order_number' => $upload->order->order_number,
            ]),
        ]);

        $this->_customsItems[] = $customsItem;

        return $this;
    }

    public function createCustomsDeclaration(array $params): static
    {
        $this->_customsDeclaration = Shippo_CustomsDeclaration::create([
            'certify' => true,
            'certify_signer' => $this->generalSettings->certifySigner,
            'commercial_invoice' => true,
            'contents_type' => $params['contents_type'] ?? ShippoCustomsDeclarationContentTypesEnum::MERCHANDISE->value,
            'contents_explanation' => $this->generalSettings->contentsExplanation,
            'non_delivery_option' => ShippoCustomsDeclarationNonDeliveryOptionsEnum::RETURN->value,
            'exporter_reference' => $params['exporter_reference'],
            'importer_reference' => $params['importer_reference'],
            'inco_term' => ShippoCustomsDeclarationIncoTermsEnum::DDU->value,
            'currency' => $params['currency'],
            'exporter_identification' => [
                'tax_id' => [
                    'number' => $this->generalSettings->taxNumber,
                    'type' => $this->generalSettings->taxType,
                ],
            ],
            'items' => $this->_customsItems,
        ]);

        return $this;
    }

    public function createShipment(?array $extras = null): static
    {
        $data = [
            'object_purpose' => 'PURCHASE',
            'address_from' => $this->_shipmentFromAddress,
            'address_to' => $this->_shipmentToAddress,
            'parcels' => $this->_parcel,
            'customs_declaration' => $this->_customsDeclaration,
            'carrier_accounts' => [
                $this->generalSettings->upsCarrierAccount,
            ],
            'async' => false,
        ];

        if ($extras) {
            $data['extra'] = $extras;
        }

        $this->_shipment = Shippo_Shipment::create($data);
        $this->logShippoCall(
            pathInfo: 'shipments',
            requestUri: 'shipments',
            method: 'POST',
            request: $data,
            response: json_decode($this->_shipment, true, 512, JSON_THROW_ON_ERROR),
        );

        return $this;
    }

    public function createLabel(int $shipmentId, $rateId, $isCustomerShipment = true): static
    {
        $typeShipment = $isCustomerShipment ? 'customer_shipment' : 'manufacturer_shipment';
        $data = [
            'rate' => $rateId,
            'label_file_type' => ShippoLabelFileTypesEnum::ZPLII->value,
            'metadata' => sprintf('%s:%s', $typeShipment, $shipmentId),
            'async' => false,
        ];
        $this->_transaction = Shippo_Transaction::create($data);
        $this->logShippoCall(
            pathInfo: 'transactions',
            requestUri: 'transactions',
            method: 'POST',
            request: $data,
            response: json_decode($this->_transaction, true, 512, JSON_THROW_ON_ERROR),
        );

        return $this;
    }

    public function createPickup(array $params): Shippo_Object
    {
        return Shippo_Pickup::create([
            'carrier_account' => $this->generalSettings->upsCarrierAccount,
            'location' => [
                'building_type' => $params['building_type'] ?? $this->pickupSettings->buildingType,
                'building_location_type' => $params['building_location_type'] ?? $this->pickupSettings->buildingLocationType,
                'instructions' => $params['instructions'],
                'address' => $this->_shipmentFromAddress,
            ],
            'transactions' => $params['transactions'],
            'requested_start_time' => $params['requested_start_time'],
            'requested_end_time' => $params['requested_end_time'],
            'is_test' => true,
        ]);
    }

    public function toArray(): array
    {
        return [
            'fromAddress' => $this->_shipmentFromAddress,
            'toAddress' => $this->_shipmentToAddress,
            'parcel' => $this->_parcel,
            'customs_items' => $this->_customsItems,
            'customs_declaration' => $this->_customsDeclaration,
            'shipment' => $this->_shipment,
            'transaction' => $this->_transaction,
        ];
    }

    private function logShippoCall(string $pathInfo, string $requestUri, string $method, $request, $response): void
    {
        $headers = (new Shippo_ApiRequestor(''))->getRequestHeaders();
        LogRequestService::logRequestOutgoing(
            pathInfo: 'https://api.goshippo.com/'.$pathInfo,
            requestUri: 'https://api.goshippo.com/'.$requestUri,
            userAgent: 'Shippo/v1 PHPBindings/0.0.1',
            method: $method,
            headers: $headers,
            request: $request,
            response: $response,
        );
    }

    protected function initPackageTypes(): void
    {
        $this->packageTypes = [
            'UPS_Express_Envelope' => 'UPS Express Envelope',
            'UPS_Express_Legal_Envelope' => 'UPS Express Legal Envelope',
            'UPS_Express_Box' => 'UPS Express Box',
            'UPS_Express_Box_Small' => 'UPS Small Express Box',
            'UPS_Express_Box_Medium' => 'UPS Medium Express Box',
            'UPS_Express_Box_Large' => 'UPS Large Express Box',
            'UPS_Box_10kg' => 'UPS 10kg Box',
            'UPS_Box_25kg' => 'UPS 25kg Box',
            'UPS_Express_Tube' => 'UPS Express Tube',
            'UPS_Express_Pak' => 'UPS Express Pak',
            'UPS_Laboratory_Pak' => 'UPS Laboratory Pak',
            'UPS_Pad_Pak' => 'UPS Pad Pak',
            'UPS_Pallet' => 'UPS Pallet',
            'UPS_MI_BPM' => 'UPS BPM (Mail Innovations - Domestic & International)',
            'UPS_MI_BPM_Flat' => 'UPS BPM Flat (Mail Innovations - Domestic & International)',
            'UPS_MI_BPM_Parcel' => 'UPS BPM Parcel (Mail Innovations - Domestic & International)',
            'UPS_MI_First_Class' => 'UPS First Class (Mail Innovations - Domestic only)',
            'UPS_MI_Flat' => 'UPS Flat (Mail Innovations - Domestic only)',
            'UPS_MI_Irregular' => 'UPS Irregular (Mail Innovations - Domestic only)',
            'UPS_MI_Machinable' => 'UPS Machinable (Mail Innovations - Domestic only)',
            'UPS_MI_MEDIA_MAIL' => 'UPS Media Mail (Mail Innovations - Domestic only)',
            'UPS_MI_Parcel_Post' => 'UPS Parcel Post (Mail Innovations - Domestic only)',
            'UPS_MI_Priority' => 'UPS Priority (Mail Innovations - Domestic only)',
            'UPS_MI_Standard_Flat' => 'UPS Standard Flat (Mail Innovations - Domestic only)',
        ];
    }

    protected function initCarriers(): void
    {
        $this->_carriers = [
            'ups' => 'UPS',
        ];
    }

    protected function initServices(): void
    {
        $this->_services = [
            'ups_standard' => 'UPS Standard℠',
            'ups_ground' => 'UPS Ground',
            'ups_saver' => 'UPS Saver®',
            'ups_3_day_select' => 'UPS 3 Day Select®',
            'ups_second_day_air' => 'UPS 2nd Day Air®',
            'ups_second_day_air_am' => 'UPS 2nd Day Air® A.M.',
            'ups_next_day_air' => 'UPS Next Day Air®',
            'ups_next_day_air_saver' => 'UPS Next Day Air Saver®',
            'ups_next_day_air_early_am' => 'UPS Next Day Air® Early',
            'ups_mail_innovations_domestic' => 'UPS Mail Innovations (domestic)',
            'ups_surepost' => 'UPS Surepost',
            'ups_surepost_bound_printed_matter' => 'UPS SurePost® Bound Printed Matter',
            'ups_surepost_lightweight' => 'UPS Surepost Lightweight',
            'ups_surepost_media' => 'UPS SurePost® Media',
            'ups_express' => 'UPS Express®',
            'ups_express_1200' => 'UPS Express 12:00',
            'ups_express_plus' => 'UPS Express Plus®',
            'ups_expedited' => 'UPS Expedited®',
            'UPS_Box_10kg' => 'Box 10kg',
            'UPS_Box_25kg' => 'Box 25kg',
            'UPS_Express_Box' => 'UPS Express Box',
            'UPS_Express_Box_Large' => 'UPS Express Box Large',
            'UPS_Express_Box_Medium' => 'UPS Express Box Medium',
            'UPS_Express_Box_Small' => 'UPS Express Box Small',
            'UPS_Express_Envelope' => 'UPS Express Envelope',
            'UPS_Express_Hard_Pak' => 'UPS Express Hard Pak',
            'UPS_Express_Legal_Envelope' => 'UPS Express Legal Envelope',
            'UPS_Express_Pak' => 'UPS Express Pak',
            'UPS_Express_Tube' => 'UPS Express Tube',
            'UPS_Laboratory_Pak' => 'Laboratory Pak',
            'UPS_MI_BPM' => 'BPM (Mail Innovations - Domestic &amp; International)',
            'UPS_MI_BPM_Flat' => 'BPM Flat (Mail Innovations - Domestic &amp; International)',
            'UPS_MI_BPM_Parcel' => 'BPM Parcel (Mail Innovations - Domestic &amp; International)',
            'UPS_MI_First_Class' => 'First Class (Mail Innovations - Domestic only)',
            'UPS_MI_Flat' => 'Flat (Mail Innovations - Domestic only)',
            'UPS_MI_Irregular' => 'Irregular (Mail Innovations - Domestic only)',
            'UPS_MI_Machinable' => 'Machinable (Mail Innovations - Domestic only)',
            'UPS_MI_MEDIA_MAIL' => 'Media Mail (Mail Innovations - Domestic only)',
            'UPS_MI_Parcel_Post' => 'Parcel Post (Mail Innovations - Domestic only)',
            'UPS_MI_Priority' => 'Priority (Mail Innovations - Domestic only)',
            'UPS_MI_Standard_Flat' => 'Standard Flat (Mail Innovations - Domestic only)',
            'UPS_Pad_Pak' => 'UPS Pad Pak',
            'UPS_Pallet' => 'UPS Pallet',
        ];
    }
}
