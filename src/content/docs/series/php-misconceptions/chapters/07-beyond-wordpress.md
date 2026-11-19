---
title: "07: Is PHP Only for WordPress?"
description: "Run a PHP inventory report as a command and as a JSON endpoint, then see where frameworks and CMSs fit."
series: "php-misconceptions"
chapter: 7
estimatedTime: "PT10M"
sidebar:
  order: 7
---

## Overview

WordPress is a PHP application with its own plugin ecosystem. PHP can also run a CLI tool, return JSON from an API or process jobs as a background worker. You can choose plain PHP for a small program, individual Symfony components for selected features or a framework like Laravel for routing, authentication and testing conventions. [WordPress plugin handbook](https://developer.wordpress.org/plugins/) · [PHP CLI](https://www.php.net/manual/en/features.commandline.php) · [Symfony components](https://symfony.com/doc/current/components/index.html) · [Laravel routing](https://laravel.com/docs/13.x/routing)

## Quick Start

With PHP 8.4 or later, run the inventory report from the repository root.

```bash
php code/php-misconceptions/inventory.php
```

```json
{"items":[{"sku":"PHP-101","stock":12},{"sku":"PHP-102","stock":0}]}
```

Now start PHP’s development server in the first terminal. Keep it local.

```bash
php -S 127.0.0.1:8027 -t code/php-misconceptions
```

Inspect the response from a second terminal. Then stop the server with Ctrl+C.

```bash
curl -i http://127.0.0.1:8027/inventory.php
```

You should see HTTP 200, a JSON content type and the same inventory data. The script sends a header in its web environment and prints the JSON in both environments. The built-in server is for development. [PHP built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php)

## Exercise

Add a third product and check that both calls return the same three items. If you later add accounts, persistent inventory and several routes, the same report can become part of a Laravel application with migrations and HTTP tests. [Laravel responses](https://laravel.com/docs/13.x/responses)

## Next chapter

[Is PHP Inherently Insecure?](/series/php-misconceptions/chapters/08-php-security/)
