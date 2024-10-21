<?php

namespace App\Http\Middleware;

use App\Services\Admin\LogRequestService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $logRequestService = new LogRequestService();
        $logRequestService->logRequest($request);

        return $next($request);
    }
}
