---
title: "PHP vs Node.js"
description: "Compare PHP and Node.js for backend APIs, request handling, dependencies and deployment. Run a matching JSON endpoint in both."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 4
---

PHP and Node.js can both serve the same JSON API. A common PHP deployment handles each request in a PHP-FPM worker behind a web server. A typical Node.js service keeps a JavaScript process and its HTTP server running across requests. That difference matters more than syntax when you deploy the app.

| Concern | PHP | Node.js |
| --- | --- | --- |
| Typical HTTP setup | A web server forwards PHP requests to workers. | Your application starts an HTTP server that listens on a port. |
| Request state | Applications normally rebuild request-local state on each request in PHP-FPM. | Module variables can live across requests until the process restarts. |
| Concurrency | PHP-FPM can run multiple workers. A worker can serve a request while others handle separate ones. | Asynchronous I/O lets requests overlap in one event loop. CPU-heavy synchronous work can block that loop. |
| Packages | Composer and `composer.lock`. | npm and `package-lock.json`. |

PHP also has long-running application server options. Compare the runtime you plan to deploy, especially if you depend on worker memory or shared state.

## Return the same JSON response

Save as `index.php` and run `php -S 127.0.0.1:8000` for this local example.

```php
<?php
header('Content-Type: application/json');
echo json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR);
```

Save as `server.mjs` and run `node server.mjs`.

```javascript
import { createServer } from "node:http";

createServer((_request, response) => {
  response.writeHead(200, { "Content-Type": "application/json" });
  response.end(JSON.stringify({ status: "ok" }));
}).listen(3000, "127.0.0.1");
```

Request the PHP server on port `8000` and the Node.js server on port `3000` with `curl`. Each returns `{"status":"ok"}`. In the PHP file you work with the request already in progress. In Node.js you register the function the server calls when a request arrives. These are local examples. For production, decide how you will restart workers, persist shared data, and observe failed requests.

## Choosing for your backend

If you already maintain a Laravel or WordPress application, extending it in PHP uses the same authentication, database and deployment setup. If your team ships TypeScript across the front end and API, Node.js lets the team share more of the toolchain. Put streaming, slow database calls and CPU-heavy tasks into a realistic test before drawing performance conclusions.

- [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php)
- [PHP-FPM](https://www.php.net/manual/en/install.fpm.php)
- [Node.js HTTP API](https://nodejs.org/api/http.html)
- [PHP vs JavaScript](/compare/php-vs-javascript/) · [PHP vs Go](/compare/php-vs-go/) · [All PHP comparisons](/compare/)
