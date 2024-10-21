<?php

namespace App\Http\Middleware;

use App\Models\LogRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $requestToLog = $request;
            $requestToLog->headers->remove('authorization');

            LogRequest::create([
                'path_info' => $requestToLog->path(),
                'request_uri' => $requestToLog->getRequestUri(),
                'method' => $requestToLog->method(),
                'remote_address' => $requestToLog->ip(),
                'user_agent' => $requestToLog->userAgent(),
                'server' => $requestToLog->server(),
                'headers' => $requestToLog->header(),
                'request' => $requestToLog->all(),
            ]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }

        return $next($request);
    }
}
