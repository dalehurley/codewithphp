<?php
declare(strict_types=1);

function respond(int $status, array $body): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
        echo json_encode($body, JSON_THROW_ON_ERROR);
    }
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = explode('?', $_SERVER['REQUEST_URI'], 2)[0];

if (!preg_match('~\A/api/jobs/([a-z]+)\z~', $path, $route)) {
    respond(404, ['error' => 'route_not_found']);
}

if ($method !== 'GET' && $method !== 'HEAD') {
    header('Allow: GET, HEAD');
    respond(405, ['error' => 'method_not_allowed']);
}

$state = $route[1];
if (!in_array($state, ['queued', 'running', 'failed'], true)) {
    respond(400, ['error' => 'invalid_state']);
}

try {
    $source = @file_get_contents(__DIR__ . '/jobs.json');
    if ($source === false) {
        throw new RuntimeException('Could not read the file.');
    }

    $fixture = json_decode($source, flags: JSON_THROW_ON_ERROR);
    $jobs = $fixture->{$state} ?? null;
    if (!is_array($jobs)) {
        throw new RuntimeException('The requested state needs an array.');
    }
} catch (JsonException | RuntimeException $error) {
    error_log('Could not load jobs.json: ' . $error->getMessage());
    respond(500, ['error' => 'jobs_unavailable']);
}

respond(200, ['state' => $state, 'jobs' => $jobs]);
