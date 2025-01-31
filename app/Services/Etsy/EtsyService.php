<?php

namespace App\Services\Etsy;

use App\Models\ShopOwner;
use Etsy\EtsyClient;
use Etsy\OAuthHelper;
use Exception;
use Illuminate\Support\Facades\Log;

class EtsyService
{
    public function __construct(private ShopOwner $shopOwner)
    {

    }

    public function auth(string $consumerKey, string $consumerSecret)
    {
        $client = new EtsyClient($consumerKey, $consumerSecret);
        $helper = new OAuthHelper($client);

        try {
            // In case you want to setup specific permissions pass a space separated) list of permissions
            // Example: $helper->requestPermissionUrl('email_r profile_w recommend_rw')
            // List of all allowed permissions: https://www.etsy.com/developers/documentation/getting_started/oauth#section_permission_scopes
            $url = $helper->requestPermissionUrl();

            // read user input for verifier
            var_dump("Please sign in to this url and paste the verifier below: $url \n");

            // on Mac OSX
            if (PHP_OS === 'Darwin')
            {
                exec("open '" . $url . "'");
            }

            //print '$ ';
            $verifier = trim(fgets(STDIN));

            $helper->getAccessToken($verifier);
            dd($helper);

            $this->shopOwner->shopOwnerAuth()->create([
                'shop' => 'etsy',
                'shop_oauth' => var_export($helper->getAuth(), true),
                'active' => true,
            ]);
            //var_export($helper->getAuth(), true)

            //file_put_contents($destination_file, "<?php\n return " . var_export($helper->getAuth(), true) . ";");

           // echo "Success! auth file '{$destination_file}' created.\n";
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
