<?php

namespace App\Services\Shippo;

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
    public const VAT_TYPES = [
        'EIN' => 'EIN',
        'VAT' => 'VAT',
        'IOSS' => 'IOSS',
        'ARN' => 'ARN',
    ];

    public const DISTANCE_UNITS = [
        'cm' => 'Cm',
        'in' => 'In',
        'ft' => 'Ft',
        'mm' => 'Mm',
        'm' => 'M',
        'yd' => 'Yd',
    ];

    public const MASS_UNITS = [
        'g' => 'G',
        'oz' => 'Oz',
        'lb' => 'Lb',
        'kg' => 'Kg',
    ];

    public const SERVICES = [
        'ups_standard' => 'UPS Standard℠',
        'ups_express_saver_worldwide_ca' => 'UPS Worldwide Express Saver®',
    ];

    public const BUILDING_TYPES = [
        'apartment' => 'Apartment',
        'building' => 'Building',
        'department' => 'Department',
        'floor' => 'Floor',
        'room' => 'Room',
        'suite' => 'Suite',
    ];

    public const BUILDING_LOCATION_TYPES = [
        'Front Door' => 'Front Door',
        'Back Door' => 'Back Door',
        'Side Door' => 'Side Door',
        'Knock on Door' => 'Knock on Door',
        'Ring Bell' => 'Ring Bell',
        'Mail Room' => 'Mail Room',
        'Office' => 'Office',
        'Reception' => 'Reception',
        'In/At Mailbox' => 'In At Mailbox',
        'Security Deck' => 'Security Deck',
        'Shipping Dock' => 'Shipping Dock',
        'Other' => 'Other',
    ];

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
        public PickupSettings $pickupSettings
    )
    {
        $this->initPackageTypes();
        $this->initCarriers();
        $this->initServices();
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return $this->_services;
    }

    /**
     * @return array
     */
    public function getCarriers(): array
    {
        return $this->_carriers;
    }

    /**
     * @return array
     */
    public function getFromAddress(): array
    {
        return $this->_fromAddress;
    }

    /**
     * @param array $address
     * @return static
     */
    public function setFromAddress(array $address): static
    {
        $this->_fromAddress = $address;
        return $this;
    }

    /**
     * @return array
     */
    public function getToAddress(): array
    {
        return $this->_toAddress;
    }

    /**
     * @param array $address
     * @return $this
     */
    public function setToAddress(array $address): static
    {
        $this->_toAddress = $address;
        return $this;
    }

    /**
     * @return Shippo_Object
     */
    public function getShipmentFromAddress(): Shippo_Object
    {
        return $this->_shipmentFromAddress;
    }

    /**
     * @param Shippo_Object $shipmentFromAddress
     * @return $this
     */
    public function setShipmentFromAddress(Shippo_Object $shipmentFromAddress): static
    {
        $this->_shipmentFromAddress = $shipmentFromAddress;
        return $this;
    }

    /**
     * @return Shippo_Object
     */
    public function getShipmentToAddress(): Shippo_Object
    {
        return $this->_shipmentToAddress;
    }

    /**
     * @param Shippo_Object $shipmentToAddress
     * @return $this
     */
    public function setShipmentToAddress(Shippo_Object $shipmentToAddress): static
    {
        $this->_shipmentToAddress = $shipmentToAddress;
        return $this;
    }

    /**
     * @return Shippo_Object
     */
    public function getOrder(): Shippo_Object
    {
        return $this->_order;
    }

    /**
     * @return Shippo_Object
     */
    public function getShipment(): Shippo_Object
    {
        return $this->_shipment;
    }

    /**
     * @return Shippo_Object
     */
    public function getTransaction(): Shippo_Object
    {
        return $this->_transaction;
    }

    /**
     * @param array $params
     * @return string
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

    /**
     * @param bool $validate
     * @return static
     */
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

    /**
     * @param bool $validate
     * @return static
     */
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

    /**
     * @param $lineItems
     * @return $this
     */
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

    /**
     * @param Order $order
     * @return $this
     */
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
            'order_status' => 'PAID',
            'placed_at' => str_replace('+00:00', 'Z', Carbon::parse($order->created_at)->setTimezone('UTC')->format('c')),
            'shipping_cost' => (string)$order->shipping_fee,
            'shipping_cost_currency' => $order->currency_code,
            'total_price' => (string)$order->total,
            'total_tax' => (string)$order->total_tax,
            'weight' => $weight,
            'weight_unit' => $this->customsItemSettings->massUnit,
            'from_address' => $this->_shipmentFromAddress,
            'to_address' => $this->_shipmentToAddress,
            'line_items' => $this->_orderLineItems,
        ]);

        return $this;
    }

    /**
     * @param array $params
     * @return static
     */
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

    /**
     * @param Upload $upload
     * @param bool $isCustomerShipment
     * @return static
     */
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
            'value_amount' => $upload->total,
            'value_currency' => $upload->currency_code ?? 'USD',
            'origin_country' => $isCustomerShipment ? 'NL' : 'US',
            'tariff_number' => $tariffNUmber,
            'metadata' => __('Order ID #:order_number', ['order_number' => $upload->order->order_number]),
        ]);

        $this->_customsItems[] = $customsItem;
        return $this;
    }

    /**
     * @param array $params
     * @return static
     */
    public function createCustomsDeclaration(array $params): static
    {
        $this->_customsDeclaration = Shippo_CustomsDeclaration::create([
            'certify' => true,
            'certify_signer' => $this->generalSettings->certifySigner,
            'commercial_invoice' => true,
            'contents_type' => 'MERCHANDISE',
            'contents_explanation' => $this->generalSettings->contentsExplanation,
            'non_delivery_option' => 'RETURN',
            'exporter_reference' => $params['exporter_reference'],
            'importer_reference' => $params['importer_reference'],
            'inco_term' => 'DDU',
//            'b13a_filing_option' => 'NOT_REQUIRED',
            'currency' => $params['currency'],
            'exporter_identification' => [
                //'eori_number' => $params['eori_number'],
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
            $data['extras'] = $extras;
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

    /**
     * Create the shipping label transaction
     *
     * @param int $customerShipmentId
     * @param $rateId -- object_id from rates_list
     * @return static
     */
    public function createLabel(int $customerShipmentId, $rateId): static
    {
        $data = [
            'rate' => $rateId,
            'label_file_type' => 'ZPLII',
            'metadata' => sprintf('customer_shipment:%s', $customerShipmentId),
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

//    /**
//     * Create the shipping label transaction
//     *
//     * @param $shippoShipment
//     * @param int $customerShipmentId
//     * @param string $servicelevelToken
//     * @return Shippo_Object
//     */
//    public function createLabelInstant($shippoShipment, int $customerShipmentId, string $servicelevelToken): Shippo_Object
//    {
//        return Shippo_Transaction::create([
//            'shipment' => $shippoShipment,
//            'label_file_type' => 'PDF',
//            'metadata' => sprintf('customer_shipment:%s', $customerShipmentId),
//            'servicelevel_token' => $servicelevelToken,
//        ]);
//    }

    /**
     * @param array $params
     * @return Shippo_Object
     */
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
            pathInfo: 'https://api.goshippo.com/' . $pathInfo,
            requestUri: 'https://api.goshippo.com/' . $requestUri,
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
//            'parcel' => 'Parcel',
//
//            'couriersplease_500g_satchel' => 'CouriersPlease 500g Satchel',
//            'couriersplease_1kg_satchel' => 'CouriersPlease 1kg Satchel',
//            'couriersplease_3kg_satchel' => 'CouriersPlease 3kg Satchel',
//            'couriersplease_5kg_satchel' => 'CouriersPlease 5g Satchel',
//
//            'DHLeC_Irregular' => 'DHL eCommerce Irregular',
//            'DHLeC_SM_Flats' => 'DHL eCommerce Flats',
//
//            'Fastway_Australia_Satchel_A2' => 'Fastway Australia Satchel A2',
//            'Fastway_Australia_Satchel_A3' => 'Fastway Australia Satchel A3',
//            'Fastway_Australia_Satchel_A4' => 'Fastway Australia Satchel A4',
//            'Fastway_Australia_Satchel_A5' => 'Fastway Australia Satchel A5',
//
//            'FedEx_Envelope' => 'FedEx Envelope',
//            'FedEx_Padded_Pak' => 'FedEx Padded Pak',
//            'FedEx_Pak_2' => 'FedEx Small Pak',
//            'FedEx_Pak_1' => 'FedEx Large Pak',
//            'FedEx_XL_Pak' => 'FedEx Extra Large Pak',
//            'FedEx_Tube' => 'FedEx Tube',
//            'FedEx_Box_10kg' => 'FedEx 10kg Box',
//            'FedEx_Box_25kg' => 'FedEx 25kg Box',
//            'FedEx_Box_Small_1' => 'FedEx Small Box (S1)',
//            'FedEx_Box_Small_2' => 'FedEx Small Box (S2)',
//            'FedEx_Box_Medium_1' => 'FedEx Medium Box (M1)',
//            'FedEx_Box_Medium_2' => 'FedEx Medium Box (M2)',
//            'FedEx_Box_Large_1' => 'FedEx Large Box (L1)',
//            'FedEx_Box_Large_2' => 'FedEx Large Box (L2)',
//            'FedEx_Box_Extra_Large_1' => 'FedEx Extra Large Box (X1)',
//            'FedEx_Box_Extra_Large_2' => 'FedEx Extra Large Box (X2)',

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
            'UPS_MI_BPM' =>	'UPS BPM (Mail Innovations - Domestic & International)',
            'UPS_MI_BPM_Flat' => 'UPS BPM Flat (Mail Innovations - Domestic & International)',
            'UPS_MI_BPM_Parcel' =>	'UPS BPM Parcel (Mail Innovations - Domestic & International)',
            'UPS_MI_First_Class' =>	'UPS First Class (Mail Innovations - Domestic only)',
            'UPS_MI_Flat' => 'UPS Flat (Mail Innovations - Domestic only)',
            'UPS_MI_Irregular' => 'UPS Irregular (Mail Innovations - Domestic only)',
            'UPS_MI_Machinable' => 'UPS Machinable (Mail Innovations - Domestic only)',
            'UPS_MI_MEDIA_MAIL' => 'UPS Media Mail (Mail Innovations - Domestic only)',
            'UPS_MI_Parcel_Post' => 'UPS Parcel Post (Mail Innovations - Domestic only)',
            'UPS_MI_Priority' => 'UPS Priority (Mail Innovations - Domestic only)',
            'UPS_MI_Standard_Flat' => 'UPS Standard Flat (Mail Innovations - Domestic only)',

//            'USPS_FlatRateCardboardEnvelope' => 'USPS Flat Rate Cardboard Envelope',
//            'USPS_FlatRateEnvelope' => 'USPS Flat Rate Envelope',
//            'USPS_FlatRateGiftCardEnvelope' => 'USPS Flat Rate Gift Card Envelope',
//            'USPS_FlatRateLegalEnvelope' => 'USPS Flat Rate Legal Envelope',
//            'USPS_FlatRatePaddedEnvelope' => 'USPS Flat Rate Padded Envelope',
//            'USPS_FlatRateWindowEnvelope' => 'USPS Flat Rate Window Envelope',
//            'USPS_IrregularParcel' => 'USPS Irregular Parcel',
//            'USPS_LargeFlatRateBoardGameBox' => 'USPS Large Flat Rate Board Game Box',
//            'USPS_LargeFlatRateBox' => 'USPS Large Flat Rate Box',
//            'USPS_APOFlatRateBox' => 'USPS APO/FPO/DPO Large Flat Rate Box',
//            'USPS_LargeVideoFlatRateBox' => 'USPS Flat Rate Large Video Box (Int\'l only)',
//            'USPS_MediumFlatRateBox1' => 'USPS Medium Flat Rate Box 1',
//            'USPS_MediumFlatRateBox2' => 'USPS Medium Flat Rate Box 2',
//            'USPS_RegionalRateBoxA1' => 'USPS Regional Rate Box A1',
//            'USPS_RegionalRateBoxA2' => 'USPS Regional Rate Box A2',
//            'USPS_RegionalRateBoxB1' => 'USPS Regional Rate Box B1',
//            'USPS_RegionalRateBoxB2' => 'USPS Regional Rate Box B2',
//            'USPS_SmallFlatRateBox' => 'USPS Small Flat Rate Box',
//            'USPS_SmallFlatRateEnvelope' => 'USPS Small Flat Rate Envelope',
//            'USPS_SoftPack' => 'USPS Soft Pack Padded Envelope',
        ];
    }

    protected function initCarriers(): void
    {
        $this->_carriers = [
//            'apc_postal' => 'APC Postal',
//            'australia_post' => 'Australia Post (also used for Startrack)',
//            'aramex' => 'Aramex',
//            'asendia_us' => 'Asendia',
//            'axlehire' => 'AxleHire',
//            'borderguru' => 'BorderGuru',
//            'boxberry' => 'Boxberry',
//            'bring' => 'Bring (also used for Posten Norge)',
//            'canada_post' => 'Canada Post',
//            'cdl' => 'CDL',
//            'correios_br' => 'Correios Brazil',
//            'correos_espana' => 'Correos Espana',
//            'collect_plus' => 'CollectPlus',
//            'couriersplease' => 'CouriersPlease',
//            'deutsche_post' => 'Deutsche Post',
//            'dhl_benelux' => 'DHL Benelux',
//            'dhl_germany' => 'DHL Germany',
//            'dhl_ecommerce' => 'DHL eCommerce',
//            'dhl_express' => 'DHL Express',
//            'dpd_germany' => 'DPD Germany',
//            'dpd_uk' => 'DPD UK',
//            'estafeta' => 'Estafeta',
//            'fastway_australia' => 'Fastway Australia',
//            'fedex' => 'FedEx',
//            'gls_de' => 'GLS Germany',
//            'gls_fr' => 'GLS France',
//            'globegistics' => 'Globegistics',
//            'gophr' => 'Gophr',
//            'gso' => 'GSO',
//            'hermes_uk' => 'Hermes UK',
//            'hongkong_post' => 'HongKong Post',
//            'lasership' => 'Lasership',
//            'lso' => 'LSO',
//            'mondial_relay' => 'Mondial Relay',
//            'new_zealand_post' => 'New Zealand Post (also used for Pace and CourierPost)',
//            'newgistics' => 'Newgistics',
//            'nippon_express' => 'Nippon Express',
//            'ontrac' => 'OnTrac',
//            'orangeds' => 'OrangeDS',
//            'parcel' => 'Parcel',
//            'posti' => 'Posti',
//            'purolator' => 'Purolator',
//            'rr_donnelley' => 'RR Donnelley',
//            'russian_post' => 'Russian Post',
//            'sendle' => 'Sendle',
//            'skypostal' => 'SkyPostal',
//            'stuart' => 'Stuart',
            'ups' => 'UPS',
//            'usps' => 'USPS',
//            'yodel' => 'Yodel',
        ];
    }

    protected function initServices(): void
    {
        $this->_services = [
//            'usps_priority' => 'USPS Priority Mail',
//            'usps_priority_express' => 'USPS Priority Mail Express',
//            'usps_first' => 'USPS First Class Mail/Package',
//            'usps_parcel_select' => 'USPS Parcel Select',
//            'usps_media_mail' => 'USPS Media Mail, only for existing Shippo customers with grandfathered Media Mail option.',
//            'usps_priority_mail_international' => 'USPS Priority Mail International',
//            'usps_priority_mail_express_international' => 'USPS Priority Mail Express International',
//            'usps_first_class_package_international_service' => 'USPS First Class Package International',
//            'fedex_ground' => 'FedEx Ground®',
//            'fedex_home_delivery' => 'FedEx Home Delivery®',
//            'fedex_smart_post' => 'FedEx SmartPost®',
//            'fedex_2_day' => 'FedEx 2Day®',
//            'fedex_2_day_am' => 'FedEx 2Day® A.M.',
//            'fedex_express_saver' => 'FedEx Express Saver®',
//            'fedex_standard_overnight' => 'FedEx Standard Overnight®',
//            'fedex_priority_overnight' => 'FedEx Priority Overnight®',
//            'fedex_first_overnight' => 'FedEx First Overnight®',
//            'fedex_freight_priority' => 'FedEx Freight® Priority',
//            'fedex_next_day_freight' => 'FedEx Next Day Freight',
//            'fedex_freight_economy' => 'FedEx Freight® Economy',
//            'fedex_first_freight' => 'FedEx First Freight',
//            'fedex_international_economy' => 'FedEx International Economy®',
//            'fedex_international_priority' => 'FedEx International Priority®',
//            'fedex_international_first' => 'FedEx International First®',
//            'fedex_europe_first_international_priority' => 'FedEx Europe International First®',
//            'fedex_international_priority_express' => 'FedEx International Priority Express',
//            'international_economy_freight' => 'FedEx International Economy® Freight',
//            'international_priority_freight' => 'FedEx International Priority® Freight',
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
//            'apc_postal_parcelconnect_expedited' => 'APC parcelConnect Expedited',
//            'apc_postal_parcelconnect_priority' => 'APC parcelConnect Priority',
//            'apc_postal_parcelconnect_priority_delcon' => 'APC parcelConnect Priority Delcon',
//            'apc_postal_parcelconnect_priority_pqw' => 'APC parcelConnect Priority PQW',
//            'apc_postal_parcelconnect_book_service' => 'APC parcelConnect Book Service',
//            'apc_postal_parcelconnect_standard' => 'APC parcelConnect Standard',
//            'apc_postal_parcelconnect_epmi' => 'APC parcelConnect ePMI',
//            'apc_postal_parcelconnect_epacket' => 'APC parcelConnect ePacket',
//            'apc_postal_parcelconnect_epmei' => 'APC parcelConnect ePMEI',
//            'asendia_us_priority_tracked' => 'Asendia USA Priority Tracked',
//            'asendia_us_international_express' => 'Asendia USA International Express',
//            'asendia_us_international_priority_airmail' => 'Asendia USA International Priority Airmail',
//            'asendia_us_international_surface_airlift' => 'Asendia USA International Surface Air Lift',
//            'asendia_us_priority_mail_international' => 'Asendia USA Priority Mail International',
//            'asendia_us_priority_mail_express_international' => 'Asendia USA Priority Mail Express International',
//            'asendia_us_epacket' => 'Asendia USA International ePacket',
//            'asendia_us_other' => 'Asendia USA Other Services (custom)',
//            'australia_post_express_post' => 'Australia Express Post',
//            'australia_post_parcel_post' => 'Australia Parcel Post',
//            'australia_post_pack_and_track_international' => 'Australia Pack and Track International',
//            'australia_post_international_airmail' => 'Australia International Airmail',
//            'australia_post_express_post_international' => 'Australia Express Post International',
//            'australia_post_express_courier_international' => 'Australia Express Courier International',
//            'australia_post_international_express' => 'Australia International Express',
//            'australia_post_international_standard' => 'Australia International Standard',
//            'australia_post_international_economy' => 'Australia International Economy',
//            'axlehire_next_day' => 'AxleHire Next Day',
//            'canada_post_regular_parcel' => 'Canada Post Regular Parcel',
//            'canada_post_expedited_parcel' => 'Canada Post Expedited Parcel',
//            'canada_post_priority' => 'Canada Post Priority',
//            'canada_post_xpresspost' => 'Canada Post Xpresspost',
//            'canada_post_xpresspost_international' => 'Canada Post Xpresspost International',
//            'canada_post_xpresspost_usa' => 'Canada Post Xpresspost USA',
//            'canada_post_expedited_parcel_usa' => 'Canada Post Expedited Parcel USA',
//            'canada_post_tracked_packet_usa' => 'Canada Post Tracked Packet USA',
//            'canada_post_small_packet_usa_air' => 'Canada Post Small Packet USA Air',
//            'canada_post_tracked_packet_international' => 'Canada Post Tracked Packet International',
//            'canada_post_small_packet_international_air' => 'Canada Post Small Package International Air',
//            'cdl_next_day' => 'CDL Next Day',
//            'couriersplease_domestic_priority_auth_to_leave' => 'CouriersPlease Domestic Priority - Authority To Leave/POPPoints',
//            'couriersplease_domestic_priority_sign_required' => 'CouriersPlease Domestic Priority - Signature Required',
//            'couriersplease_gold_domestic_auth_to_leave' => 'CouriersPlease Gold Domestic - Authority To Leave/POPPoints',
//            'couriersplease_gold_domestic_sign_required' => 'CouriersPlease Gold Domestic - Signature Required',
//            'couriersplease_off_peak_auth_to_leave' => 'CouriersPlease Off Peak - Authority To Leave/POPPoints',
//            'couriersplease_off_peak_sign_required' => 'CouriersPlease Off Peak - Signature Required',
//            'couriersplease_parcel_auth_to_leave' => 'CouriersPlease Parcel - Authority To Leave',
//            'couriersplease_parcel_sign_required' => 'CouriersPlease Parcel - Signature Required',
//            'couriersplease_road_express' => 'CouriersPlease Road Express',
//            'couriersplease_satchel_auth_to_leave' => 'Satchel - Authority To Leave',
//            'couriersplease_satchel_sign_required' => 'Satchel - Signature Required',
//            'purolator_ground' => 'Purolator Ground',
//            'purolator_ground9_am' => 'Purolator Ground 9am',
//            'purolator_ground1030_am' => 'Purolator Ground 10:30am',
//            'purolator_ground_distribution' => 'Purolator Ground Distribution',
//            'purolator_ground_evening' => 'Purolator Ground Evening',
//            'purolator_ground_us' => 'Purolator Ground US',
//            'purolator_express' => 'Purolator Express',
//            'purolator_express9_am' => 'Purolator Express 9am',
//            'purolator_express1030_am' => 'Purolator Express 10am',
//            'purolator_express_evening' => 'Purolator Express Evening',
//            'purolator_express_us' => 'Purolator Express US',
//            'purolator_express_us9_am' => 'Purolator Express US 9am',
//            'purolator_express_us1030_am' => 'Purolator Express US 10:30am',
//            'purolator_express_us1200' => 'Purolator Express US 12pm',
//            'purolator_express_international' => 'Purolator Express International',
//            'purolator_express_international9_am' => 'Purolator Express International 9am',
//            'purolator_express_international1030_am' => 'Purolator Express International 10:30am',
//            'purolator_express_international1200' => 'Purolator Express International 12pm',
//            'dhl_express_domestic_express_doc' => 'DHL Domestic Express Doc',
//            'dhl_express_economy_select_doc' => 'DHL Economy Select Doc',
//            'dhl_express_worldwide_nondoc' => 'DHL Express Worldwide Nondoc',
//            'dhl_express_worldwide_doc' => 'DHL Express Worldwide Doc',
//            'dhl_express_worldwide' => 'DHL Worldwide',
//            'dhl_express_worldwide_eu_doc' => 'DHL Express Worldwide EU Doc',
//            'dhl_express_break_bulk_express_doc' => 'DHL Break Bulk Express Doc',
//            'dhl_express_express_9_00_nondoc' => 'DHL Express 9:00 NonDoc',
//            'dhl_express_economy_select_nondoc' => 'DHL Economy Select NonDoc',
//            'dhl_express_break_bulk_economy_doc' => 'DHL Break Bulk Economy Doc',
//            'dhl_express_express_9_00_doc' => 'DHL Express 9:00 Doc',
//            'dhl_express_express_10_30_doc' => 'DHL Express 10:30 Doc',
//            'dhl_express_express_10_30_nondoc' => 'DHL Express 10:30 NonDoc',
//            'dhl_express_express_12_00_doc' => 'DHL Express 12:00 Doc',
//            'dhl_express_europack_nondoc' => 'DHL Europack NonDoc',
//            'dhl_express_express_envelope_doc' => 'DHL Express Envelope Doc',
//            'dhl_express_express_12_00_nondoc' => 'DHL Express 12:00 NonDoc',
//            'dhl_express_express_12_doc' => 'DHL Domestic Express 12:00',
//            'dhl_express_worldwide_b2c_doc' => 'DHL Express Worldwide (B2C) Doc',
//            'dhl_express_worldwide_b2c_nondoc' => 'DHL Express Worldwide (B2C) NonDoc',
//            'dhl_express_medical_express' => 'DHL Medical Express',
//            'dhl_express_express_easy_nondoc' => 'DHL Express Easy NonDoc',
//            'dhl_ecommerce_marketing_parcel_expedited' => 'DHL eCommerce Marketing Parcel Expedited',
//            'dhl_ecommerce_globalmail_business_ips' => 'DHL eCommerce Parcel International Expedited',
//            'dhl_ecommerce_parcel_international_direct' => 'DHL eCommerce GlobalMail Business Standard',
//            'dhl_ecommerce_parcels_expedited_max' => 'DHL eCommerce Parcels Expedited Max',
//            'dhl_ecommerce_bpm_ground' => 'DHL eCommerce Bounded Printed Matter Ground',
//            'dhl_ecommerce_priority_expedited' => 'DHL eCommerce Priority Expedited',
//            'dhl_ecommerce_globalmail_packet_ipa' => 'DHL eCommerce GlobalMail Packet Priority',
//            'dhl_ecommerce_globalmail_packet_isal' => 'DHL eCommerce GlobalMail Packet Standard',
//            'dhl_ecommerce_easy_return_plus' => 'DHL eCommerce Easy Return Plus',
//            'dhl_ecommerce_marketing_parcel_ground' => 'DHL eCommerce Marketing Parcel Ground',
//            'dhl_ecommerce_first_class_parcel_expedited' => 'DHL eCommerce First Class Parcel Expedited',
//            'dhl_ecommerce_globalmail_business_priority' => 'DHL eCommerce Parcel International Standard',
//            'dhl_ecommerce_parcels_expedited' => 'DHL eCommerce Parcels Expedited',
//            'dhl_ecommerce_globalmail_business_isal' => 'DHL eCommerce Parcel International Direct',
//            'dhl_ecommerce_parcel_plus_expedited_max' => 'DHL eCommerce Parcel Plus Expedited Max',
//            'dhl_ecommerce_globalmail_packet_plus' => 'DHL eCommerce GlobalMail Packet IPA',
//            'dhl_ecommerce_parcels_ground' => 'DHL eCommerce Parcels Ground',
//            'dhl_ecommerce_expedited' => 'DHL eCommerce Expedited',
//            'dhl_ecommerce_parcel_plus_ground' => 'DHL eCommerce Parcel Plus Ground',
//            'dhl_ecommerce_parcel_international_standard' => 'DHL eCommerce GlobalMail Business ISAL',
//            'dhl_ecommerce_bpm_expedited' => 'DHL eCommerce Bounded Printed Matter Expedited',
//            'dhl_ecommerce_parcel_international_expedited' => 'DHL eCommerce GlobalMail Business IPA',
//            'dhl_ecommerce_globalmail_packet_priority' => 'DHL eCommerce GlobalMail Packet ISAL',
//            'dhl_ecommerce_easy_return_light' => 'DHL eCommerce Easy Return Light',
//            'dhl_ecommerce_parcel_plus_expedited' => 'DHL eCommerce Parcel Plus Expedited',
//            'dhl_ecommerce_globalmail_business_standard' => 'DHL eCommerce GlobalMail Packet Plus',
//            'dhl_ecommerce_ground' => 'DHL eCommerce Ground',
//            'dhl_ecommerce_globalmail_packet_standard' => 'DHL eCommerce GlobalMail Business Priority',
//            'dhl_germany_europaket' => 'DHL Germany Europaket',
//            'dhl_germany_paket' => 'DHL Germany Paket',
//            'dhl_germany_paket_connect' => 'DHL Germany Paket Connect',
//            'dhl_germany_paket_international' => 'DHL Germany Paket International',
//            'dhl_germany_paket_priority' => 'DHL Germany Paket Priority',
//            'dhl_germany_paket_sameday' => 'DHL Germany Paket Sameday',
//            'deutsche_post_postkarte' => 'Deutsche Post Postkarte',
//            'deutsche_post_standardbrief' => 'Deutsche Post Standardbrief',
//            'deutsche_post_kompaktbrief' => 'Deutsche Post Kompaktbrief',
//            'deutsche_post_grossbrief' => 'Deutsche Post Grossbrief',
//            'deutsche_post_maxibrief' => 'Deutsche Post Maxibrief',
//            'deutsche_post_maxibrief_plus' => 'Deutsche Post Maxibrief Plus',
//            'deutsche_post_warenpost_international_xs' => 'Deutsche Post Warenpost International XS',
//            'deutsche_post_warenpost_international_s' => 'Deutsche Post Warenpost International S',
//            'deutsche_post_warenpost_international_m' => 'Deutsche Post Warenpost International M',
//            'deutsche_post_warenpost_international_l' => 'Deutsche Post Warenpost International L',
//            'fastway_australia_parcel' => 'Fastway Australia Parcel',
//            'fastway_australia_satchel' => 'Fastway Australia Satchel',
//            'fastway_australia_box_small' => 'Fastway Australia Box Small',
//            'fastway_australia_box_medium' => 'Fastway Australia Box Medium',
//            'fastway_australia_box_large' => 'Fastway Australia Box Large',
//            'globegistics_priority_mail_express_international' => 'Globegistics Priority Mail Express International',
//            'globegistics_priority_mail_international' => 'Globegistics Priority Mail International',
//            'globegistics_priority_mail_express_international_pds' => 'Globegistics Priority Mail Express International PreSort Drop Ship',
//            'globegistics_priority_mail_international_pds' => 'Globegistics Priority Mail International PreSort Drop Ship',
//            'globegistics_epacket' => 'Globegistics ePacket',
//            'globegistics_ecom_tracked_ddp' => 'Globegistics eCom Tracked DDP',
//            'globegistics_ecom_packet_ddp' => 'Globegistics eCom Packet DDP',
//            'globegistics_ecom_priority_mail_international_ddp' => 'Globegistics eCom Priority Mail International DDP',
//            'globegistics_ecom_priority_mail_express_international_ddp' => 'Globegistics eCom Priority Mail Express International DDP',
//            'globegistics_ecom_extra' => 'Globegistics eCom Extra',
//            'globegistics_ecom_international_priority_airmail' => 'Globegistics eCom International Priority Airmail',
//            'globegistics_ecom_international_surface_airlift' => 'Globegistics eCom International Surface Air Lift',
//            'gls_deutschland_business_parcel' => 'GLS Germany Business Parcel',
//            'gls_france_business_parcel' => 'GLS France Business Parcel',
//            'lso_ground' => 'LSO Ground',
//            'lso_economy_next_day' => 'LSO Economy Next Day',
//            'lso_saturday_delivery' => 'LSO Saturday Delivery',
//            'lso_2nd_day' => 'LSO 2nd Day',
//            'lso_priority_next_day' => 'LSO Priority Next Day',
//            'lso_early_overnight' => 'LSO Early Overnight',
//            'mondial_relay_pointrelais' => 'Mondial Relay Point Relais',
//            'parcelforce_express48' => 'Parcelforce Express 48',
//            'parcelforce_express24' => 'Parcelforce Express 24',
//            'parcelforce_expressam' => 'Parcelforce Express AM',
//            'rr_donnelley_domestic_economy_parcel' => 'RR Donnelley Domestic Economy Parcel',
//            'rr_donnelley_domestic_priority_parcel' => 'RR Donnelley Domestic Priority Parcel ',
//            'rr_donnelley_domestic_parcel_bpm' => 'RR Donnelley Domestic Parcel BPM',
//            'rr_donnelley_priority_domestic_priority_parcel_bpm' => 'RR Donnelley Domestic Priority Parcel BPM',
//            'rr_donnelley_priority_parcel_delcon' => 'RR Donnelley International Priority Parcel DelCon',
//            'rr_donnelley_priority_parcel_nondelcon' => 'RR Donnelley International Priority Parcel NonDelcon',
//            'rr_donnelley_economy_parcel' => 'RR Donnelley Economy Parcel Service ',
//            'rr_donnelley_ipa' => 'RR Donnelley International Priority Airmail (IPA)',
//            'rr_donnelley_courier' => 'RR Donnelley International Courier',
//            'rr_donnelley_isal' => 'RR Donnelley International Surface Air Lift (ISAL)',
//            'rr_donnelley_epacket' => 'RR Donnelley e-Packet',
//            'rr_donnelley_pmi' => 'RR Donnelley Priority Mail International',
//            'rr_donnelley_emi' => 'RR Donnelley Express Mail International',
//            'sendle_parcel' => 'Sendle Parcel',
//            'newgistics_parcel_select_lightweight' => 'Newgistics Parcel Select Lightweight',
//            'newgistics_parcel_select' => 'Newgistics Parcel Select',
//            'newgistics_priority_mail' => 'Newgistics Priority Mail',
//            'newgistics_first_class_mail' => 'Newgistics First Class Mail',
//            'ontrac_ground' => 'OnTrac Ground',
//            'ontrac_sunrise_gold' => 'OnTrac Sunrise Gold',
//            'ontrac_sunrise' => 'OnTrac Sunrise',
//            'lasership_routed_delivery' => 'Lasership Routed Delivery',
//            'hermes_uk_courier_collection' => 'Hermes UK Courier Collection',
//            'hermes_uk_parcelshop_dropoff' => 'Hermes UK ParcelShop Drop-Off',
//            'FedEx_Box_10kg' => 'FedEx® 10kg Box',
//            'FedEx_Box_25kg' => 'FedEx® 25kg Box',
//            'FedEx_Box_Extra_Large_1' => 'FedEx® Extra Large Box (X1)',
//            'FedEx_Box_Extra_Large_2' => 'FedEx® Extra Large Box (X2)',
//            'FedEx_Box_Large_1' => 'FedEx® Large Box (L1)',
//            'FedEx_Box_Large_2' => 'FedEx® Large Box (L2)',
//            'FedEx_Box_Medium_1' => 'FedEx® Medium Box (M1)',
//            'FedEx_Box_Medium_2' => 'FedEx® Medium Box (M2)',
//            'FedEx_Box_Small_1' => 'FedEx® Small Box (S1)',
//            'FedEx_Box_Small_2' => 'FedEx® Small Box (S2)',
//            'FedEx_Envelope' => 'FedEx® Envelope',
//            'FedEx_Padded_Pak' => 'FedEx® Padded Pak',
//            'FedEx_Pak_1' => 'FedEx® Large Pak',
//            'FedEx_Pak_2' => 'FedEx® Small Pak',
//            'FedEx_Tube' => 'FedEx® Tube',
//            'FedEx_XL_Pak' => 'FedEx® Extra Large Pak',
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
//            'USPS_FlatRateCardboardEnvelope' => 'USPS Flat Rate Cardboard Envelope',
//            'USPS_FlatRateEnvelope' => 'USPS Flat Rate Envelope',
//            'USPS_FlatRateGiftCardEnvelope' => 'USPS Flat Rate Gift Card Envelope',
//            'USPS_FlatRateLegalEnvelope' => 'USPS Flat Rate Legal Envelope',
//            'USPS_FlatRatePaddedEnvelope' => 'USPS Flat Rate Padded Envelope',
//            'USPS_FlatRateWindowEnvelope' => 'USPS Flat Rate Window Envelope',
//            'USPS_IrregularParcel' => 'USPS Irregular Parcel',
//            'USPS_LargeFlatRateBoardGameBox' => 'USPS Large Flat Rate Board Game Box',
//            'USPS_LargeFlatRateBox' => 'USPS Large Flat Rate Box',
//            'USPS_APOFlatRateBox' => 'USPS APO/FPO/DPO Large Flat Rate Box',
//            'USPS_LargeVideoFlatRateBox' => 'USPS Flat Rate Large Video Box (Int\'l only)',
//            'USPS_MediumFlatRateBox1' => 'USPS Medium Flat Rate Box 1',
//            'USPS_MediumFlatRateBox2' => 'Medium Flat Rate Box 2',
//            'USPS_RegionalRateBoxA1' => 'USPS Regional Rate Box A1',
//            'USPS_RegionalRateBoxA2' => 'USPS Regional Rate Box A2',
//            'USPS_RegionalRateBoxB1' => 'USPS Regional Rate Box B1',
//            'USPS_RegionalRateBoxB2' => 'USPS Regional Rate Box B2',
//            'USPS_SmallFlatRateBox' => 'USPS Small Flat Rate Box',
//            'USPS_SmallFlatRateEnvelope' => 'USPS Small Flat Rate Envelope',
//            'USPS_SoftPack' => 'USPS Soft Pack Padded Envelope',
        ];
    }
}
