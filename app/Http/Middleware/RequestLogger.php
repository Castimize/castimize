<?php

namespace App\Http\Middleware;

use App\Models\LogRequest;
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
        $logRequest = LogRequestService::logRequest($request);

        if ($logRequest instanceof LogRequest) {
            $request->request->add(['log_request_id' => $logRequest->id]);
        }

        return $next($request);
    }
}
