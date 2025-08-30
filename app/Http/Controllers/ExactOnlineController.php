<?php

namespace App\Http\Controllers;

use App\Services\Exact\LaravelExactOnline;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JsonException;

class ExactOnlineController extends Controller
{
    /**
     * Connect Exact app
     *
     * @return Factory|View
     */
    public function appConnect()
    {
        if (! auth()->check()) {
            abort(403);
        }

        return view('exact-online.connect');
    }

    /**
     * Authorize to Exact
     * Sends an oAuth request to the Exact App to get tokens
     */
    public function appAuthorize()
    {
        if (! auth()->check()) {
            abort(403);
        }

        $connection = app()->make('Exact\Connection');
        $connection->redirectForAuthorization();
    }

    /**
     * Exact Callback
     * Saves the authorisation and refresh tokens
     *
     * @throws JsonException
     */
    public function appCallback(Request $request)
    {
        $config = LaravelExactOnline::loadConfig();
        $config->exact_authorisationCode = request()->get('code');

        // Store first to avoid another redirect to exact online
        LaravelExactOnline::storeConfig($config);

        $connection = app()->make('Exact\Connection');

        $config->exact_accessToken = serialize($connection->getAccessToken());
        $config->exact_refreshToken = $connection->getRefreshToken();
        $config->exact_tokenExpires = $connection->getTokenExpires() - 60;

        LaravelExactOnline::storeConfig($config);

        dd('Exact Online connected.');
    }
}
