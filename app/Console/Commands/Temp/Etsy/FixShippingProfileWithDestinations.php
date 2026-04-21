<?php

namespace App\Console\Commands\Temp\Etsy;

use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Country;
use App\Models\Shop;
use App\Services\Etsy\EtsyService;
use App\Services\Etsy\EtsyShippingProfileService;
use Exception;
use Illuminate\Console\Command;
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

        $this->info("Found {$shops->count()} active Etsy shop(s).");
        Log::info('FixShippingProfileWithDestinations started', ['shop_count' => $shops->count()]);

        foreach ($shops as $shop) {
            $this->line("Processing shop {$shop->id} (shop_owner_id: {$shop->shop_owner_id})...");

            try {
                $shippingProfile = $etsyService->getShippingProfile($shop);

                if (! $shippingProfile) {
                    $this->warn('  No shipping profile found — skipping.');
                    Log::warning('FixShippingProfileWithDestinations: no shipping profile found', ['shop_id' => $shop->id]);

                    continue;
                }

                $existingShippingProfileDestinations = [];
                foreach ($shippingProfile->shipping_profile_destinations as $shippingProfileDestination) {
                    $existingShippingProfileDestinations[$shippingProfileDestination->destination_country_iso] = $shippingProfileDestination->shipping_profile_destination_id;
                }

                $this->line('  Existing destinations: '.implode(', ', array_keys($existingShippingProfileDestinations)));

                $countries = Country::with(['logisticsZone.shippingFee'])
                    ->whereHas('logisticsZone', fn ($q) => $q->whereHas('shippingFee'))
                    ->get();

                $created = 0;
                $updated = 0;
                $failed = 0;

                foreach ($countries as $country) {
                    try {
                        $shippingProfileDestinationService = new EtsyShippingProfileService(shop: $shop);

                        if (! array_key_exists($country->alpha2, $existingShippingProfileDestinations)) {
                            $shippingProfileDestinationService->createShippingProfileDestination(
                                shippingProfileDestinationDTO: ShippingProfileDestinationDTO::fromCountry(
                                    shop: $shop,
                                    country: $country,
                                    shippingProfileId: $shop->shop_oauth['shop_shipping_profile_id'],
                                ),
                            );
                            $created++;
                        } else {
                            $shippingProfileDestinationService->updateShippingProfileDestination(
                                shippingProfileDestinationDTO: ShippingProfileDestinationDTO::fromCountry(
                                    shop: $shop,
                                    country: $country,
                                    shippingProfileId: $shop->shop_oauth['shop_shipping_profile_id'],
                                    shippingProfileDestinationId: $existingShippingProfileDestinations[$country->alpha2],
                                ),
                            );
                            $updated++;
                        }
                    } catch (Exception $e) {
                        $failed++;
                        $this->error("  Failed for {$country->alpha2}: {$e->getMessage()}");
                        Log::warning('FixShippingProfileWithDestinations: destination failed', [
                            'shop_id' => $shop->id,
                            'country' => $country->alpha2,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $this->info("  Done — created: {$created}, updated: {$updated}, failed: {$failed}.");
                Log::info('FixShippingProfileWithDestinations: shop processed', [
                    'shop_id' => $shop->id,
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                ]);
            } catch (Exception $e) {
                $this->error("  Error: {$e->getMessage()}");
                Log::error('FixShippingProfileWithDestinations: shop failed', [
                    'shop_id' => $shop->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info('Done.');
        Log::info('FixShippingProfileWithDestinations finished');

        return true;
    }
}
