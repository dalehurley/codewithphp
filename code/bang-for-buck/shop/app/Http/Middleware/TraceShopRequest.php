<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Profiling\RequestTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TraceShopRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-Shop-Trace') !== '1' || !app()->environment('lab', 'testing')) {
            return $next($request);
        }

        $trace = new RequestTrace();
        $request->attributes->set(RequestTrace::class, $trace);

        try {
            $response = $next($request);
            $response->headers->set('Server-Timing', $trace->serverTiming(), false);

            return $response;
        } finally {
            $request->attributes->remove(RequestTrace::class);
        }
    }
}
