<?php

namespace App\Services\Admin;

use App\Models\LogRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogRequestService
{
    public static function logRequest($request): LogRequest|null
    {
        try {
            $requestToLog = $request;
            $requestToLog->headers->remove('authorization');

            return LogRequest::create([
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
            return null;
        }
    }

    public static function logRequestOutgoing(string $pathInfo, string $requestUri, string $userAgent, string $method, array $headers, $request, $response): LogRequest|null
    {
        try {
            return LogRequest::create([
                'type' => 'outgoing',
                'path_info' => $pathInfo,
                'request_uri' => $requestUri,
                'method' => $method,
                'remote_address' => null,
                'user_agent' => $userAgent,
                'server' => null,
                'headers' => $headers,
                'request' => $request,
                'response' => $response,
            ]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            return null;
        }
    }

    public static function addResponse($request, $response, int $httpCode = 200): LogRequest|null
    {
        if ($request->has('log_request_id')) {
            $logRequest = LogRequest::find($request->log_request_id);
            if ($logRequest) {
                $logRequest->response = $response;
                $logRequest->http_code = $httpCode;
                $logRequest->save();
                return $logRequest;
            }
        }
        return null;
    }
}
