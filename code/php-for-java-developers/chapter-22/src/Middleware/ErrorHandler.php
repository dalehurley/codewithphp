<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpBadRequestException;

class ErrorHandler
{
    public function __invoke(
        Request $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $response = new \Slim\Psr7\Response();
        $statusCode = 500;
        $message = 'Internal Server Error';

        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
            $message = 'Resource not found';
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $statusCode = 405;
            $message = 'Method not allowed';
        } elseif ($exception instanceof HttpBadRequestException) {
            $statusCode = 400;
            $message = 'Bad request';
        }

        $error = [
            'error' => [
                'status' => $statusCode,
                'message' => $message,
                'path' => $request->getUri()->getPath()
            ]
        ];

        if ($displayErrorDetails) {
            $error['error']['details'] = $exception->getMessage();
            $error['error']['trace'] = $exception->getTraceAsString();
        }

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}



