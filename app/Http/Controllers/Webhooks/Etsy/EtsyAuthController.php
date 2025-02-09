<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks\Etsy;

use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EtsyAuthController
{
    use ValidatesRequests;

    public function __invoke(Request $request, EtsyService $etsyService)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $data = $this->validate($request, [
            'shop_owner_auth_id' => 'required',
        ]);

        try {
            $redirectUri = $request->fullUrlWithQuery([
                'shop_owner_auth_id' => $data['shop_owner_auth_id'],
            ]);
            $etsyService->requestAccessToken($data, $redirectUri);
        } catch (Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response('Success', 200);

        // ToDo: When saving
//        $clientId = '98lip7kiicbc8hf0rfdruhfi';
    }
}
