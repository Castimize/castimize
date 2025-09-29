<?php

namespace App\Console\Commands\Temp\Etsy;

use App\DTO\Order\OrderDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Country;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\Admin\OrdersService;
use App\Services\Admin\PaymentService;
use App\Services\Admin\ShopOrderService;
use App\Services\Etsy\EtsyService;
use App\Services\Etsy\EtsyShippingProfileService;
use App\Services\Payment\Stripe\StripeService;
use App\Services\Woocommerce\WoocommerceApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixShippingProfileWithDestinations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:fix-shipping-profile-with-destinations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Etsy shipping profile with destinations';

    /**
     * Execute the console command.
     */
    public function handle(
        EtsyService $etsyService,
    ) {
        $shops = Shop::with(['shopOwner.customer'])
            ->where('active', true)
            ->where('shop', ShopOwnerShopsEnum::Etsy->value)
            ->get();

        foreach ($shops as $shop) {
            try {
                $shippingProfile = $etsyService->getShippingProfile($shop);
                if ($shippingProfile) {
                    $existingShippingProfileDestinations = [];
                    foreach ($shippingProfile->shipping_profile_destinations as $shippingProfileDestination) {
                        $existingShippingProfileDestinations[] = $shippingProfileDestination->destination_country_iso;
                    }

                    $countries = Country::with(['logisticsZone.shippingFee'])
                        ->whereNotIn('alpha2', $existingShippingProfileDestinations)
                        ->get();

                    foreach ($countries as $country) {
                        if ($country->has('logisticsZone')) {
                            (new EtsyShippingProfileService(shop: $shop))->createShippingProfileDestination(
                                shippingProfileDestinationDTO: ShippingProfileDestinationDTO::fromCountry(
                                    shopId: $shop->shop_oauth['shop_id'],
                                    country: $country,
                                    shippingProfileId: $shop->shop_oauth['shop_shipping_profile_id'],
                                ),
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine());
            }
        }

        return true;
    }
}
