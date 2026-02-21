<?php

namespace App\Services\Admin;

use App\Enums\Shippo\ShippoCustomsDeclarationContentTypesEnum;
use App\Models\Country;
use App\Models\CustomerShipment;
use App\Models\ManufacturerShipment;
use App\Models\Order;
use App\Nova\Settings\Shipping\DcSettings;
use App\Nova\Settings\Shipping\GeneralSettings;
use App\Services\Shippo\ShippoService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;
use Shippo_ApiError;
use Shippo_Object;

class ShippingService
{
    protected $_shippoService;

    protected $_transliterationService;

    protected $_fromAddress;

    protected $_toAddress;

    public function getFromAddress(): array
    {
        return $this->_fromAddress;
    }

    /**
     * @return ShippingService
     */
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

    public function __construct(
        public GeneralSettings $generalSettings,
        public DcSettings $dcSettings
    ) {
        $this->_shippoService = app(ShippoService::class);
        $this->_transliterationService = app(AddressTransliterationService::class);
    }

    public function createShippoAddress(string $type = 'From'): Shippo_Object
    {
        $getAddressMethod = 'get'.$type.'Address';
        $getShipmentAddressMethod = 'getShipment'.$type.'Address';
        $setAddressMethod = 'set'.$type.'Address';
        $createAddressMethod = 'create'.$type.'Address';
        $cacheKey = $this->_shippoService->getCacheKey($this->$getAddressMethod());

        // return Cache::remember($cacheKey . '_v3', 31556926, function() use ($getAddressMethod, $setAddressMethod, $createAddressMethod, $getShipmentAddressMethod) {
        return $this->_shippoService->$setAddressMethod($this->$getAddressMethod())
            ->$createAddressMethod(true)
            ->$getShipmentAddressMethod();
        // });
    }

    /**
     * @throws JsonException
     */
    public function validateAddress(string $type = 'From'): array
    {
        $getAddressMethod = 'get'.$type.'Address';
        $shippoAddress = $this->createShippoAddress($type);
        $address = $this->$getAddressMethod();
        [$valid, $errorMessages] = $this->checkAddressValid($shippoAddress['validation_results']);
        $addressChanged = false;
        $address['object_id'] = $shippoAddress['object_id'];

        if (
            ! empty($shippoAddress['street_no']) ||
            $address['street1'] !== $shippoAddress['street1'] ||
            $address['city'] !== $shippoAddress['city'] ||
            $address['state'] !== $shippoAddress['state'] ||
            $address['zip'] !== $shippoAddress['zip'] ||
            $address['country'] !== $shippoAddress['country']
        ) {
            $addressChanged = true;
            $address['street1'] = $shippoAddress['street1'].(! empty($shippoAddress['street_no']) ? ' '.$shippoAddress['street_no'] : '');
            $address['city'] = $shippoAddress['city'];
            $address['state'] = $shippoAddress['state'];
            $address['zip'] = $shippoAddress['zip'];
            $address['country'] = $shippoAddress['country'];
        }

        return [
            'valid' => $valid,
            'address' => $address,
            'address_changed' => $addressChanged,
            'messages' => $errorMessages,
        ];
    }

    /**
     * @throws Shippo_ApiError
     */
    public function createShippoCustomerOrder(Order $order): void
    {
        $fromAddress = $this->mapDcDefaultToShippoAddress();
        $toAddress = $this->mapToShippoAddress($order->shipping_address);

        $shippoFromAddress = $this->setFromAddress($fromAddress)->createShippoAddress('From');
        $shippoToAddress = $this->setToAddress($toAddress)->createShippoAddress('To');
        [$valid, $errorMessages] = $this->checkAddressValid($shippoToAddress['validation_results'], $shippoToAddress['test']);
        if (! $valid) {
            $message = __('The shipping to address is invalid with the following messages').PHP_EOL;
            foreach ($errorMessages as $errorMessage) {
                $message .= $errorMessage['text'].PHP_EOL;
            }
            throw new RuntimeException($message);
        }

        $this->_shippoService
            ->setShipmentFromAddress($shippoFromAddress)
            ->setShipmentToAddress($shippoToAddress)
            ->createOrderLineItems($order->uploads)
            ->createOrder($order);
    }

    /**
     * @throws Shippo_ApiError
     */
    public function createShippoCustomerShipment(CustomerShipment $customerShipment): array
    {
        $fromAddress = $this->mapToShippoAddress($customerShipment->fromAddress);
        $toAddress = $this->mapToShippoAddress($customerShipment->toAddress);

        $shippoFromAddress = $this->setFromAddress($fromAddress)->createShippoAddress('From');
        $shippoToAddress = $this->setToAddress($toAddress)->createShippoAddress('To');
        [$valid, $errorMessages] = $this->checkAddressValid($shippoToAddress['validation_results'], $shippoToAddress['test'], false);
        if (! $valid) {
            $message = __('The shipping to address is invalid with the following messages').PHP_EOL;
            foreach ($errorMessages as $errorMessage) {
                $source = $errorMessage['source'] ?? 'UNKNOWN';
                $code = $errorMessage['code'] ?? 'UNKNOWN';
                $message .= "[{$source} - {$code}] {$errorMessage['text']}".PHP_EOL;
            }
            throw new RuntimeException($message);
        }

        $this->_shippoService
            ->setShipmentFromAddress($shippoFromAddress)
            ->setShipmentToAddress($shippoToAddress)
            ->createParcel($customerShipment->parcel);

        $orderNumber = null;
        $currency = null;
        $shippingCountry = null;
        $contentsType = ShippoCustomsDeclarationContentTypesEnum::GIFT->value;
        $validItemsCount = 0;
        foreach ($customerShipment->selectedPOs as $selectedPO) {
            if ($selectedPO->upload === null) {
                continue;
            }
            if ($orderNumber === null && $selectedPO->upload->order !== null) {
                $orderNumber = $selectedPO->upload->order->order_number;
                $currency = $selectedPO->upload->order->currency_code;
                $shippingCountry = $selectedPO->upload->order->shipping_country;
            }
            if ($selectedPO->upload->total > 0.00) {
                $contentsType = ShippoCustomsDeclarationContentTypesEnum::MERCHANDISE->value;
            }
            $this->_shippoService->createCustomsItem($selectedPO->upload);
            $validItemsCount++;
        }

        if ($validItemsCount === 0 || $orderNumber === null) {
            throw new RuntimeException(__('Cannot create shipment: no valid order queue items with uploads found.'));
        }

        $this->_shippoService
            ->createCustomsDeclaration([
                'exporter_reference' => $customerShipment->id,
                'importer_reference' => $orderNumber,
                'currency' => $currency,
                'contents_type' => $contentsType,
                // 'eori_number' => strtoupper($customerShipment->toAddress['country']) === 'GB' ? $this->generalSettings->eoriNumberGb : $this->generalSettings->eoriNumber,
            ])
            ->createShipment();
        $shippoShipment = $this->_shippoService->getShipment();
        //        dd($shippoShipment);
        $rate = $this->getCustomerShipmentRate($shippoShipment, $shippingCountry);

        if ($rate === null) {
            $errorMessages = [];
            foreach ($shippoShipment['messages'] as $msg) {
                $source = $msg['source'] ?? 'UNKNOWN';
                $code = $msg['code'] ?? 'UNKNOWN';
                $errorMessages[] = "[{$source} - {$code}] {$msg['text']}";
            }
            throw new Shippo_ApiError(
                sprintf(
                    '%s%s%s',
                    __('No rates found for this shipment.'),
                    PHP_EOL,
                    implode(PHP_EOL, $errorMessages)
                )
            );
        }

        $this->_shippoService = $this->_shippoService
            ->createLabel($customerShipment->id, $rate['object_id']);
        $transaction = $this->_shippoService->getTransaction();
        Log::info(print_r($transaction, true));
        if ($transaction && $transaction['status'] === 'SUCCESS') {
            return $this->_shippoService->toArray();
        }

        $errorMessages = [];
        foreach ($transaction['messages'] as $msg) {
            $source = $msg['source'] ?? 'UNKNOWN';
            $code = $msg['code'] ?? 'UNKNOWN';
            $errorMessages[] = "[{$source} - {$code}] {$msg['text']}";
        }
        if (! empty($errorMessages)) {
            throw new Shippo_ApiError(
                sprintf(
                    '%s%s%s',
                    __('Transaction unsuccessful.'),
                    PHP_EOL,
                    implode(PHP_EOL, $errorMessages)
                )
            );
        }

        return [];
    }

    /**
     * @throws Shippo_ApiError
     */
    public function createShippoManufacturerShipment(ManufacturerShipment $manufacturerShipment): array
    {
        $fromAddress = $this->mapToShippoAddress($manufacturerShipment->fromAddress);
        $toAddress = $this->mapToShippoAddress($manufacturerShipment->toAddress);

        $shippoFromAddress = $this->setFromAddress($fromAddress)->createShippoAddress('From');
        $shippoToAddress = $this->setToAddress($toAddress)->createShippoAddress('To');
        [$valid, $errorMessages] = $this->checkAddressValid($shippoFromAddress['validation_results'], $shippoFromAddress['test'], false);
        if (! $valid) {
            $message = __('The shipping from address is invalid with the following messages').PHP_EOL;
            foreach ($errorMessages as $errorMessage) {
                $message .= $errorMessage['text'].PHP_EOL;
            }
            throw new RuntimeException($message);
        }

        $this->_shippoService
            ->setShipmentFromAddress($shippoToAddress)
            ->setShipmentToAddress($shippoFromAddress)
            ->createParcel($manufacturerShipment->parcel);

        $orderNumber = null;
        $currency = null;
        $shippingCountry = $manufacturerShipment->fromAddress['country'];
        foreach ($manufacturerShipment->selectedPOs as $selectedPO) {
            if ($orderNumber === null) {
                $orderNumber = $selectedPO->upload->order->order_number;
                $currency = $selectedPO->upload->order->currency_code;
            }
            $this->_shippoService->createCustomsItem($selectedPO->upload);
        }

        // Make as return and bill to us
        $extra = [
            'is_return' => true,
            //            'billing' => [
            //                'account' => 'G2240C',
            //                'country' => 'NL',
            //                'type' => 'THIRD_PARTY',
            //                'zip' => $toAddress['zip'],
            //            ],
        ];

        $this->_shippoService
            ->createCustomsDeclaration([
                'exporter_reference' => $manufacturerShipment->id,
                'importer_reference' => $orderNumber,
                'currency' => $currency,
                // 'eori_number' => $this->generalSettings->eoriNumber,
            ])
            ->createShipment($extra);
        $shippoShipment = $this->_shippoService->getShipment();
        //        dd($shippoShipment);
        $rate = $this->getCustomerShipmentRate($shippoShipment, $shippingCountry);

        if ($rate === null) {
            Log::info(print_r($shippoShipment, true));
            $errorMessages = [];
            foreach ($shippoShipment['messages'] as $message) {
                $errorMessages[] = $message['text'];
            }
            throw new Shippo_ApiError(
                sprintf(
                    '%s%s%s%s%s',
                    __('No rates found for this shipment.'),
                    PHP_EOL,
                    print_r($this->_shippoService->toArray(), true),
                    PHP_EOL,
                    implode(PHP_EOL, $errorMessages)
                )
            );
        }

        $this->_shippoService = $this->_shippoService
            ->createLabel($manufacturerShipment->id, $rate['object_id']);
        $transaction = $this->_shippoService->getTransaction();

        // Log::info(print_r($transaction, true));
        if ($transaction && $transaction['status'] === 'SUCCESS') {
            return $this->_shippoService->toArray();
        }

        $errorMessages = [];
        foreach ($transaction['messages'] as $message) {
            $errorMessages[] = $message['text'];
        }
        if (! empty($errorMessages)) {
            throw new Shippo_ApiError(
                sprintf(
                    '%s%s%s%s%s',
                    __('Transaction unsuccessful.'),
                    PHP_EOL,
                    print_r($this->_shippoService->toArray(), true),
                    PHP_EOL,
                    implode(PHP_EOL, $errorMessages)
                )
            );
        }

        return [];
    }

    public function createShippoPickup(Collection $customerShipments, array $params)
    {
        $params['transactions'] = $customerShipments->pluck('shippo_transaction_id')->toArray();
        $params['requested_start_time'] = str_replace('+00:00', 'Z', Carbon::parse($params['requested_start_time'])->setTimezone('UTC')->format('c'));
        $params['requested_end_time'] = str_replace('+00:00', 'Z', Carbon::parse($params['requested_end_time'])->setTimezone('UTC')->format('c'));

        $shippoPickup = $this->_shippoService
            ->setFromAddress($this->getFromAddress())
            ->createFromAddress()
            ->createPickup($params);
        dd($shippoPickup);
    }

    private function mapToShippoAddress(array $address): array
    {
        $transliteratedAddress = $this->_transliterationService->transliterateAddress($address);

        return [
            'name' => $transliteratedAddress['name'],
            'company' => $transliteratedAddress['company'],
            'street1' => $transliteratedAddress['address_line1'],
            'street2' => $transliteratedAddress['address_line2'],
            'city' => $transliteratedAddress['city'],
            'state' => $transliteratedAddress['state'] ?? null,
            'zip' => $address['postal_code'],
            'country' => $address['country'],
            'email' => $address['email'],
            'phone' => $address['phone'],
        ];
    }

    private function mapDcDefaultToShippoAddress(): array
    {
        return [
            'name' => $this->dcSettings->name,
            'company' => $this->dcSettings->company,
            'street1' => $this->dcSettings->addressLine1,
            'street2' => $this->dcSettings->addressLine2,
            'city' => $this->dcSettings->city,
            'state' => $this->dcSettings->state,
            'zip' => $this->dcSettings->postalCode,
            'country' => $this->dcSettings->country,
            'email' => $this->dcSettings->email,
            'phone' => $this->dcSettings->phone,
        ];
    }

    private function getCustomerShipmentRate($shippoShipment, string $shippingCountry): mixed
    {
        $country = Country::with('logisticsZone')->where('alpha2', $shippingCountry)->first();
        $firstCarrierAccountRate = null;
        foreach ($shippoShipment['rates'] as $rate) {
            if ($firstCarrierAccountRate === null) {
                $firstCarrierAccountRate = $rate;
            }
            if ($rate['servicelevel']['token'] === $country->logisticsZone->shipping_servicelevel_token) {
                return $rate;
            }
        }

        if ($firstCarrierAccountRate) {
            return $firstCarrierAccountRate;
        }

        return $shippoShipment['rates'][0] ?? null;
    }

    private function getServicelevelToken(string $shippingCountry): string
    {
        $country = Country::with('logisticsZone')->where('alpha2', $shippingCountry)->first();

        return $country->logisticsZone->shipping_servicelevel_token;
    }

    private function checkAddressValid($validation_results, bool $test = false, bool $frontend = true): array
    {
        $valid = 1;
        if (is_array($validation_results)) {
            if (app()->environment('production')) {
                $valid = $validation_results['is_valid'] ? 1 : 0;
            }
        }
        $errorMessages = [];
        if (isset($validation_results['messages']) && $validation_results['messages']) {
            foreach ($validation_results['messages'] as $message) {
                if ($message['type'] === 'address_error') {
                    $valid = 0;
                }
                $errorMessages[] = [
                    'source' => $message['source'],
                    'code' => $message['code'],
                    'type' => $message['type'],
                    'text' => $message['text'],
                ];
            }
        }

        return [$valid, $errorMessages];
    }
}
