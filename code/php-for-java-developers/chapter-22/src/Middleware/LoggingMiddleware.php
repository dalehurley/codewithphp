<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LoggingMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $startTime = microtime(true);
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        // Log request
        error_log("[$method] $uri - Start");

        // Process request
        $response = $handler->handle($request);

        // Calculate duration
        $duration = microtime(true) - $startTime;
        $statusCode = $response->getStatusCode();

        // Log response
        error_log("[$method] $uri - $statusCode - " . round($duration * 1000, 2) . "ms");

        return $response;
    }
}





