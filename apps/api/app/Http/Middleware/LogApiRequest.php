<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $incomingRequestId = (string) $request->header('X-Request-ID', '');
        $requestId = preg_match('/\A[a-zA-Z0-9._-]{1,100}\z/', $incomingRequestId) === 1
            ? $incomingRequestId
            : (string) Str::uuid();

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        Log::info('API request completed', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
            'user_id' => $request->user()?->getAuthIdentifier(),
            'ip' => $request->ip(),
        ]);

        return $response;
    }
}
