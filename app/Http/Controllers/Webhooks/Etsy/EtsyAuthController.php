<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks\Etsy;

use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EtsyAuthController
{
    use ValidatesRequests;

    public function __invoke(Request $request, EtsyService $etsyService)
    {
        try {
            $etsyService->requestAccessToken($request);
        } catch (Exception $e) {
            throw $e;
            return new Response($e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getTraceAsString(), Response::HTTP_BAD_REQUEST);
        }

        return new Response('Success', 200);

        // ToDo: When saving
//        $clientId = '98lip7kiicbc8hf0rfdruhfi';
    }
}
