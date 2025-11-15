<?php
declare(strict_types=1);

/**
 * ReactPHP HTTP Server
 *
 * Build a long-running async HTTP server with ReactPHP.
 * Similar to Node.js HTTP servers.
 */

require 'vendor/autoload.php';

use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Psr\Http\Message\ServerRequestInterface;

echo "=== ReactPHP HTTP Server ===\n\n";

// Create HTTP server
$server = new HttpServer(function (ServerRequestInterface $request) {
    $path = $request->getUri()->getPath();
    $method = $request->getMethod();

    echo "[{$method}] {$path}\n";

    // Route handling
    if ($path === '/' && $method === 'GET') {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['message' => 'Hello from ReactPHP!'])
        );
    }

    if ($path === '/users' && $method === 'GET') {
        $users = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($users)
        );
    }

    if (preg_match('#^/users/(\d+)$#', $path, $matches)) {
        $id = (int) $matches[1];
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['id' => $id, 'name' => 'User ' . $id])
        );
    }

    // 404 Not Found
    return new Response(
        404,
        ['Content-Type' => 'application/json'],
        json_encode(['error' => 'Not Found'])
    );
});

// Listen on port 8080
$socket = new SocketServer('127.0.0.1:8080');
$server->listen($socket);

echo "Server running on http://127.0.0.1:8080\n";
echo "Try:\n";
echo "  curl http://127.0.0.1:8080/\n";
echo "  curl http://127.0.0.1:8080/users\n";
echo "  curl http://127.0.0.1:8080/users/1\n";
echo "\nPress Ctrl+C to stop\n\n";
