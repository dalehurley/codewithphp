---
title: "PHP vs Node.js"
description: "Compare PHP and Node.js for backend APIs, concurrency, background jobs and deployment. Run the same job status endpoint in both runtimes."
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-22"
sidebar:
  order: 4
---

Use **PHP** when the API belongs inside an existing Laravel or WordPress application, or when your team already operates a PHP stack. Choose **Node.js** when the backend needs the JavaScript or TypeScript ecosystem and your team is equipped to run a long-lived service. Both can return the same API responses. Compare how they handle requests, waiting, background work and deployment before choosing.

[Run the matching endpoint](#build-the-same-job-status-endpoint) · [Try a decision exercise](#choose-by-building-one-feature)

| Concern | PHP with PHP-FPM | Node.js with its HTTP server |
| --- | --- | --- |
| Who handles an HTTP request? | A web server forwards PHP requests to an FPM worker. | The Node.js process listens for HTTP connections and calls the application’s request handler. |
| What happens while a request waits on I/O? | A synchronous call occupies that worker while it waits. Other workers can serve requests. | When a handler awaits asynchronous I/O, the event loop can handle other ready work. |
| Where should jobs and shared state live? | Store durable jobs and status in a database or queue. | Store durable jobs and status in a database or queue. |
| How do you repeat dependency installs? | Commit `composer.lock` and install from it. | Commit `package-lock.json` and use `npm ci` for a clean install. |

## Request lifetime and concurrency

PHP-FPM reuses worker processes across requests. Ordinary PHP request variables are set up again for each request. A setting such as `pm.max_children` limits how many requests a pool can serve at once. A Node.js server registers its request handler at startup, and module-level values can survive from one request to the next in that process. A restart or another process gives you a separate copy of that memory. PHP also has long-lived server options, so compare the deployment you will actually run. [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) · [FPM pool settings](https://www.php.net/manual/en/install.fpm.configuration.php) · [Node.js HTTP](https://nodejs.org/api/http.html)

Asynchronous I/O lets a Node.js request handler yield while an operation is pending. The JavaScript work before and after the wait still runs on the event loop. A long synchronous calculation can delay unrelated requests in the same process. In the examples below, PHP reads a file synchronously and Node.js awaits a promise from its file system API. Use this difference to understand where a request waits. For a capacity decision, measure the actual endpoint on comparable infrastructure with realistic inputs and concurrent requests. [Node.js event loop guidance](https://nodejs.org/en/learn/asynchronous-work/dont-block-the-event-loop) · [Node.js promises file API](https://nodejs.org/api/fs.html#fspromisesreadfilepath-options)

## Background work and deployment

If a report takes several seconds to build, the API can record a job and return its ID while a separate worker does the work. Store the state somewhere that survives a process restart, and decide what happens on a retry and after the final failure. Laravel provides a queue abstraction and worker commands. On a Node.js stack you can choose a durable queue and run a consumer separately from the HTTP service. Node’s worker threads can move CPU-intensive JavaScript off the main thread, while durable queuing still requires persistent storage. [Laravel queues](https://laravel.com/docs/13.x/queues) · [Node.js worker threads](https://nodejs.org/api/worker_threads.html) · [Laravel background jobs lesson](/series/build-crm-laravel-12/chapters/29-queues-background-jobs/)

For a production PHP-FPM deployment, configure the web server, worker pool and request time limits together. For a production Node.js service, arrange how the service listens, restarts and handles in-flight requests during a release. Check both stacks for how they expose health, log failures, bound upstream waits and restart job consumers. These operational tasks can matter more than how short either handler is. [PHP-FPM configuration](https://www.php.net/manual/en/install.fpm.configuration.php) · [Node.js server lifecycle](https://nodejs.org/api/http.html#serverclosecallback)

## Build the same job status endpoint

The endpoint is `GET /api/jobs/{state}`, where `state` is one of `queued`, `running` or `failed`. Both servers read the same fixture and return the same HTTP status and JSON fields. A recognised state can have zero jobs. An unrecognised state gets an error. The example reads data without creating or running any jobs.

### Get the files and check the runtimes

Use PHP 8.1 or later and Node.js 22 or later for this walkthrough. Choose a maintained release when deploying a real service. Save all three files in the same new directory and run the commands from there. Both examples use built-in APIs and need no Composer or npm install. [jobs.json](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-nodejs/jobs.json) · [router.php](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-nodejs/router.php) · [server.mjs](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-nodejs/server.mjs)

```sh
php -v
node --version
curl --version
```

The commands print the installed runtimes and HTTP client. On Windows PowerShell, use `curl.exe` wherever the example calls `curl`. [Install PHP](/series/php-basics/chapters/00-setting-up-your-development-environment/) · [Node.js release status](https://nodejs.org/en/about/previous-releases)

### Define the shared fixture

Save as `jobs.json`. The keys are the known states, and each value is a list of jobs with an ID and title. `failed` represents a valid list with no entries.

```json title="jobs.json"
{
  "queued": [
    { "id": 101, "title": "Export monthly report" }
  ],
  "running": [
    { "id": 102, "title": "Resize catalogue photos" }
  ],
  "failed": []
}
```

### Serve it with PHP

Save as `router.php`. The PHP development server calls the router for each request. The router checks the path and method, validates the state and reads the fixture next to the script. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php) · [PHP file_get_contents](https://www.php.net/manual/en/function.file-get-contents.php)

```php title="router.php"
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
```

Start it in one terminal.

```sh
php -S 127.0.0.1:8000 router.php
```

### Serve it with Node.js

Save as `server.mjs`. Node.js starts the HTTP server once. The registered handler runs for each request and awaits the file read before returning JSON. Both examples expect a small UTF-8 JSON fixture. [Node.js HTTP](https://nodejs.org/api/http.html) · [Node.js file reads](https://nodejs.org/api/fs.html#fspromisesreadfilepath-options)

```javascript title="server.mjs"
import { readFile } from "node:fs/promises";
import { createServer } from "node:http";

const fixturePath = new URL("./jobs.json", import.meta.url);
const allowedStates = new Set(["queued", "running", "failed"]);

createServer(async (request, response) => {
  function respond(status, body, headers = {}) {
    response.writeHead(status, { "Content-Type": "application/json", ...headers });
    response.end(request.method === "HEAD" ? undefined : JSON.stringify(body));
  }

  const path = request.url.split("?", 1)[0];
  const route = /^\/api\/jobs\/([a-z]+)$/.exec(path);
  if (!route) {
    respond(404, { error: "route_not_found" });
    return;
  }

  if (request.method !== "GET" && request.method !== "HEAD") {
    respond(405, { error: "method_not_allowed" }, { Allow: "GET, HEAD" });
    return;
  }

  const state = route[1];
  if (!allowedStates.has(state)) {
    respond(400, { error: "invalid_state" });
    return;
  }

  let jobs;
  try {
    const bytes = await readFile(fixturePath);
    const source = new TextDecoder("utf-8", { fatal: true, ignoreBOM: true }).decode(bytes);
    jobs = JSON.parse(source)?.[state];
    if (!Array.isArray(jobs)) {
      throw new Error("The requested state needs an array.");
    }
  } catch (error) {
    console.error("Could not load jobs.json:", error.message);
    respond(500, { error: "jobs_unavailable" });
    return;
  }

  respond(200, { state, jobs });
}).listen(3000, "127.0.0.1", () => {
  console.log("Job API listening on http://127.0.0.1:3000");
});
```

Start it in a second terminal.

```sh
node server.mjs
```

### Compare the HTTP responses

From a third terminal on macOS or Linux, send the same requests to both servers. You can also run individual commands with either port in the URL. The client waits up to five seconds per request.

```sh
for port in 8000 3000; do
  base="http://127.0.0.1:$port"
  curl --max-time 5 -i "$base/api/jobs/queued"
  curl --max-time 5 -i "$base/api/jobs/failed"
  curl --max-time 5 -i "$base/api/jobs/paused"
  curl --max-time 5 -i "$base/api/jobs"
  curl --max-time 5 -i -X POST "$base/api/jobs/queued"
  curl --max-time 5 -I "$base/api/jobs/queued"
done
```

| Request | Expected response from both |
| --- | --- |
| `GET /api/jobs/queued` | `200` with `{"state":"queued","jobs":[{"id":101,"title":"Export monthly report"}]}`. |
| `GET /api/jobs/failed` | `200` with `{"state":"failed","jobs":[]}`. |
| `GET /api/jobs/paused` | `400` with `{"error":"invalid_state"}`. |
| `GET /api/jobs` | `404` with `{"error":"route_not_found"}`. |
| `POST /api/jobs/queued` | `405` with `Allow: GET, HEAD` and `{"error":"method_not_allowed"}`. |
| `HEAD /api/jobs/queued` | `200` with `Content-Type: application/json` and an empty body. |

`"failed": []` is a valid empty list. If the fixture contains `"failed": {}`, PHP decodes that value to an object and `is_array()` rejects it. Node.js makes the same check with `Array.isArray()`. A request to `/api/jobs/failed` then returns `500` in both versions.

Both servers accept lowercase state labels in the exact route shape shown above. The response has `Content-Type: application/json`. For a setup failure, start both servers in a new directory with only the two server files. Request `/api/jobs/queued` and look for `500` with `{"error":"jobs_unavailable"}`. The terminal logs identify `jobs.json`. Add the fixture beside the server files and repeat the request to get `200`. In a real service, replace the fixture with an authorised data lookup and use the same response tests. [HTTP HEAD](https://www.rfc-editor.org/rfc/rfc9110.html#name-head) · [HTTP 405](https://www.rfc-editor.org/rfc/rfc9110.html#name-405-method-not-allowed) · [PHP JSON and APIs lesson](/series/php-basics/chapters/23-working-with-json-and-apis/)

## Choose by building one feature

Start with one endpoint from the project you intend to ship. Give both implementations the same request, fixture and expected response. Add the project’s authentication and storage, then run the same tests and deploy each to the environment you would maintain. Pay attention to how you deploy a change, diagnose a timeout and recover a failed job. That evidence gives you a better basis for the choice than a bare response benchmark. [PHP vs JavaScript](/compare/php-vs-javascript/) · [PHP vs Go](/compare/php-vs-go/) · [All PHP comparisons](/compare/)
