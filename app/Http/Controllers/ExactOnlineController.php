<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JsonException;
use App\Services\Exact\LaravelExactOnline;

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
     * @param Request $request
     * @throws JsonException
     */
    public function appCallback(Request $request)
    {
        // When getting the access token and refresh token for the first time, you need to refresh the token instead of using the access token. Else the refresh token does not work!
        $config = LaravelExactOnline::loadConfig();
        $config->exact_authorisationCode = request()->get('code');
        LaravelExactOnline::storeConfig($config);

        app()->make('Exact\Connection');

        dd('Exact Online connected.');
    }
}
