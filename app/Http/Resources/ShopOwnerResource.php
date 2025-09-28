<?php

namespace App\Http\Resources;

use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Services\Payment\Stripe\StripeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [
                'id' => null,
                'is_shop_owner' => false,
                'active' => false,
                'vat_number' => null,
                'stripe_id' => null,
                'mandate' => [],
                'shops' => [],
                'shops_list' => ShopOwnerShopsEnum::getList(),
            ];
        }

        $stripeService = new StripeService();
        $mandate = [];
        $paymentMethodChargable = false;
        $paymentMethod = null;
        $paymentMethodAcceptedAt = null;
        $stripeData = $this->customer->stripe_data ?? [];
        if (isset($stripeData['mandate_id']) && $stripeData['mandate_id']) {
            $stripeMandate = $stripeService->getMandate($stripeData['mandate_id']);
            $paymentMethod = $stripeService->getPaymentMethod($stripeMandate->payment_method);
            $paymentMethodChargable = true;
            $paymentMethodAcceptedAt = Carbon::createFromTimestamp($stripeMandate->customer_acceptance->accepted_at)->toDateTimeString();
            $mandate = [
                'id' => $this->customer->stripe_data['mandate_id'],
                'accepted_at' => $paymentMethodAcceptedAt,
                'payment_method' => $paymentMethod->type,
            ];
        } elseif (isset($stripeData['payment_method_chargable']) && $stripeData['payment_method_chargable']) {
            $paymentMethod = $stripeService->getPaymentMethod($stripeData['payment_method']);
            $paymentMethodChargable = true;
            $paymentMethodAcceptedAt = Carbon::createFromTimestamp($stripeData['payment_method_accepted_at'])->toDateTimeString();
        }

        return [
            'id' => $this->id,
            'is_shop_owner' => true,
            'active' => $this->active,
            'vat_number' => $this->customer->vat_number,
            'stripe_id' => is_array($this->customer->stripe_data) && array_key_exists('stripe_id', $this->customer->stripe_data) ? $this->customer->stripe_data['stripe_id'] : null,
            'payment_method' => $paymentMethod->type,
            'payment_method_chargable' => $paymentMethodChargable,
            'payment_method_accepted_at' => $paymentMethodAcceptedAt,
            'mandate' => $mandate,
            'shops' => ShopResource::collection($this->shops)->keyBy->shop,
            'shops_list' => ShopOwnerShopsEnum::getList(),
        ];
    }
}
