---
title: "Why PHP Works Locally but Fails on Your Host"
description: "Compare your local and hosted PHP versions, extensions, configuration and paths to explain a failure after deployment."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-23"
prev: false
next: false
---

Start with one request or command that works on your machine and fails on the host. Write down the URL path or command, the reported status, and the first relevant error in the host's application or PHP error log. Strip credentials and private data from anything you share.

## Compare the PHP processes

Run these commands locally and in the shell or PHP container for your staging deployment.

```sh
php -v
php --ini
php -m
```

The terminal, web server, and background workers can use different PHP binaries and configuration files. For a browser request, check the PHP version and enabled extensions for the specific site in the host's control panel or the deployed PHP-FPM container configuration. For a queued job, check the worker's service configuration. Compare the process that raised the error with the process that worked. [PHP CLI options](https://www.php.net/manual/en/features.commandline.options.php) · [PHP configuration files](https://www.php.net/manual/en/configuration.file.php)

| Symptom | First comparison |
| --- | --- |
| Parse error after deploying a new language feature | Compare the deployed PHP branch with the feature's minimum version. [Choose a PHP version](/learn/which-php-version/) |
| `could not find driver` | Match the PDO driver to the database type. SQLite requires `pdo_sqlite`. A shell check is `php -r 'print_r(PDO::getAvailableDrivers());'`. Confirm the same driver in the web runtime. |
| `Class not found` | Check that the deployed release installed its Composer dependencies. Compare class, namespace, and filename case with the actual files on the host. |
| 404 on an app route | Check the document root and the host's route handling. If the project uses `public/index.php` as its entry point, configure the host to serve `public/` and pass application routes to that file. |
| Permission error writing a file | Verify that the web process can write to the intended storage directory. Keep writable data outside the document root. |

For Composer projects, run `composer check-platform-reqs --no-dev` in the release directory after installing production dependencies. It checks the real CLI PHP and extension versions against the installed packages. Run it in the same container image as the service when you can. [Composer platform checks](https://getcomposer.org/doc/03-cli.md#check-platform-reqs)

## Reproduce one difference before editing the app

Suppose the [reading list](/series/php-basics/sqlite-starter/) opens locally but the host reports `could not find driver`. On your own machine, save this as `runtime.php` in a new directory. It reports the PHP version, how PHP was invoked, and whether that process loaded PDO SQLite.

```php
<?php

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=UTF-8');
}

printf("PHP %s (%s)\n", PHP_VERSION, PHP_SAPI);
printf("PDO SQLite: %s\n", extension_loaded('pdo_sqlite') ? 'loaded' : 'missing');
```

From the directory containing `runtime.php`, run the file, then start the local development server.

```sh
php runtime.php
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000/runtime.php`. The terminal reports `cli` and the browser reports `cli-server`. With PDO SQLite installed, both should report `PDO SQLite: loaded`. A runtime without that extension reports `PDO SQLite: missing`. Press Ctrl+C to stop the server. Keep this file on your own machine. [PHP runtime name](https://www.php.net/manual/en/function.php-sapi-name.php) · [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php)

If the hosted request fails, use the hosting panel or ask your provider to confirm `pdo_sqlite` for the PHP runtime serving that site. A successful shell check confirms the shell’s runtime only. If the web runtime lacks the extension, enable it on staging using the provider’s instructions, restart or redeploy if required, and retry the same request. [PDO SQLite manual](https://www.php.net/manual/en/ref.pdo-sqlite.php)

Record the difference you found, the setting you changed, and the response you expect. When PHP configuration already matches, compare the working directory, required environment variable names, database connection, and writable storage paths. Use application and host logs for diagnostics. Keep detailed error output in development and staging. [PHP error reporting settings](https://www.php.net/manual/en/errorfunc.configuration.php) · [PHP troubleshooting guide](/series/php-basics/troubleshooting/)
