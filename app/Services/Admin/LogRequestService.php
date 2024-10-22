<?php

namespace App\Services\Admin;

use App\Models\LogRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogRequestService
{
    /**
     * @param $request
     * @return null|LogRequest
     */
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

    /**
     * @param $request
     * @return null|LogRequest
     */
    public static function logRequestOutgoing($request): LogRequest|null
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

    /**
     * @param $request
     * @param $response
     * @return LogRequest|null
     */
    public static function addResponse($request, $response): LogRequest|null
    {
        if ($request->has('log_request_id')) {
            $logRequest = LogRequest::find($request->log_request_id);
            if ($logRequest) {
                $logRequest->response = $response;
                $logRequest->save();
                return $logRequest;
            }
        }
        return null;
    }
}
