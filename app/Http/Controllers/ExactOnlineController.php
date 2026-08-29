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
        // Exact returns an error parameter when authorisation fails (e.g. user denied,
        // session expired, or a state mismatch). Abort early so we do not store null
        // as the auth code and do not trigger another redirect loop.
        if ($request->has('error')) {
            abort(400, 'Exact Online authorisation failed: '.$request->get('error').' – '.$request->get('error_description', ''));
        }

        $code = $request->get('code');

        if (empty($code)) {
            abort(400, 'Exact Online callback received without an authorisation code.');
        }

        $config = LaravelExactOnline::loadConfig();
        $config->exact_authorisationCode = $code;

        // Store first to avoid another redirect to exact online
        LaravelExactOnline::storeConfig($config);

        // Forget the existing singleton: it was initialised before the auth code
        // was available and has no knowledge of the new code. A fresh instance
        // will read credentials.json with the code and exchange it for tokens.
        app()->forgetInstance('Exact\Connection');

        $connection = app()->make('Exact\Connection');

        $config->exact_accessToken = serialize($connection->getAccessToken());
        $config->exact_refreshToken = $connection->getRefreshToken();
        $config->exact_tokenExpires = $connection->getTokenExpires() - 60;

        LaravelExactOnline::storeConfig($config);

        dd('Exact Online connected.');
    }
}
