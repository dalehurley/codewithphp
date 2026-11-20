---
title: "07: Is PHP Only for WordPress?"
description: "Share one PHP inventory report between a CLI command and an HTTP endpoint, then decide which tools your application needs."
series: "php-misconceptions"
chapter: 7
estimatedTime: "PT20M"
sidebar:
  order: 7
---

## Overview

A colleague wants an inventory snapshot from the terminal. A dashboard needs the same data over HTTP. You can run both from the PHP runtime and share the rules that select the data. WordPress is a content management system built with PHP. It supplies an editorial interface and plugin hooks. A component supplies a narrower tool such as command-line argument handling. An application framework gives your own code conventions for routes, responses and tests. [WordPress plugin handbook](https://developer.wordpress.org/plugins/) · [PHP CLI](https://www.php.net/manual/en/features.commandline.php) · [Symfony components](https://symfony.com/doc/current/components/index.html) · [Laravel routing](https://laravel.com/docs/13.x/routing)

## Quick Start

Use PHP 8.6, the series baseline, Bash or Zsh, and `curl` for the HTTP steps. Open a terminal at the root of the Code with PHP checkout. The inventory sample works without Composer dependencies. [Inventory command source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/inventory.php)

```bash
php code/php-misconceptions/inventory.php
```

```json
{"items":[{"sku":"PHP-101","stock":12},{"sku":"PHP-102","stock":0}]}
```

Add `--in-stock` to print just the row with positive stock. The command prints JSON to standard output, so another program can read it. An unknown option prints usage to standard error, leaves standard output empty and exits with status 1. That lets a script distinguish valid JSON from a failed command. [PHP CLI input and output streams](https://www.php.net/manual/en/features.commandline.io-streams.php)

```bash
php code/php-misconceptions/inventory.php --in-stock
php code/php-misconceptions/inventory.php --unknown
```

### Request the same data over HTTP

In the first terminal, start PHP’s development server with the inventory example’s public directory as its document root. It serves the lab on loopback. The `opcache.enable=0` setting lets you see edits to the report on the next request. If port 8027 is in use, select a free port and substitute it in the commands that follow. [PHP built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php) · [OPcache settings](https://www.php.net/manual/en/opcache.configuration.php)

```bash
php -d opcache.enable=0 -S 127.0.0.1:8027 -t code/php-misconceptions/inventory/public
```

Request the report in a second terminal.

```bash
curl -sS -i 'http://127.0.0.1:8027/inventory.php'
curl -sS -i 'http://127.0.0.1:8027/inventory.php?in_stock=1'
```

Both responses should have HTTP status 200 and `Content-Type: application/json; charset=utf-8`. The first body has both items. The second has the same one-item list as the command with `--in-stock`. Omitting the parameter or passing `in_stock=0` includes every item.

Try a malformed filter and a method this route doesn’t support.

```bash
curl -sS -i 'http://127.0.0.1:8027/inventory.php?in_stock=yes'
curl -sS -i -X POST 'http://127.0.0.1:8027/inventory.php'
```

The first returns HTTP 400 and an `error` field explaining the accepted values. The second returns HTTP 405 and `Allow: GET, HEAD`. A HEAD request reports status and headers without sending a body. These HTTP signals serve a similar purpose to the command’s usage message and exit status. [HTTP endpoint source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/inventory/public/inventory.php) · [HTTP method and status semantics](https://www.rfc-editor.org/rfc/rfc9110.html)

### Compare the success bodies

While the server is running, use a terminal at the checkout root. These commands save the filtered CLI output and HTTP body to separate files. This time curl omits `-i`, so the file has just the body.

```bash
sample_dir="$(mktemp -d)"
php code/php-misconceptions/inventory.php --in-stock > "$sample_dir/cli.json"
curl -fsS 'http://127.0.0.1:8027/inventory.php?in_stock=1' > "$sample_dir/http.json"
cmp "$sample_dir/cli.json" "$sample_dir/http.json"
```

`cmp` exits successfully without printing anything when the files have the same bytes. Both files should contain `{"items":[{"sku":"PHP-101","stock":12}]}`. Keep the server running if you want to do the exercise now, or stop it with Ctrl+C and restart it for the exercise.

## Share the report logic

The CLI adapter turns `--in-stock` into a boolean. The HTTP adapter checks the query value and turns `in_stock=1` into the same boolean. Both pass rows from `inventoryRows()` and that boolean to `inventoryReport()`. The report returns a PHP array. Each adapter encodes it as JSON, and the HTTP adapter sets the response metadata.

| File | Responsibility |
| --- | --- |
| `inventory.php` | Reads command-line options, prints JSON and chooses an exit status. |
| `inventory/public/inventory.php` | Reads the request, chooses the HTTP status and headers, and sends the JSON body. |
| `inventory/report.php` | Supplies the sample rows and applies the report rule that positive stock counts as in stock. |

The report uses `array_filter()` to select rows and `array_values()` to reindex them. PHP preserves keys when filtering. An array with numeric gaps can become a JSON object instead of a JSON list, so reindexing preserves the structure clients expect. [Shared report source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/inventory/report.php) · [PHP array filtering](https://www.php.net/manual/en/function.array-filter.php) · [PHP JSON encoding](https://www.php.net/manual/en/function.json-encode.php)

### Use the report without an HTTP request

A scheduled task can run the command and redirect the report to a file for another process. Try that from the checkout root.

```bash
export_dir="$(mktemp -d)"
php code/php-misconceptions/inventory.php --in-stock > "$export_dir/inventory.json"
cat "$export_dir/inventory.json"
```

The output has the same one-item list. A scheduler launches a command at set times. A queue worker takes jobs from a queue as they arrive. Either can call the report logic when it needs a snapshot. [Laravel scheduling](https://laravel.com/docs/13.x/scheduling) · [Laravel queues](https://laravel.com/docs/13.x/queues)

## Choose the tool that owns the work

| Requirement | A reasonable starting point |
| --- | --- |
| A warehouse colleague needs one local snapshot supplied by an existing data source. | Plain PHP and a CLI command. |
| The warehouse team has several interactive commands and needs help text, option handling and prompts. | A focused package such as Symfony Console around the report logic. |
| An inventory application owns stock in a database and needs accounts, several routes and background imports. | A framework such as Laravel for the web application and its jobs. |
| Editors already manage product records in WordPress and want an inventory view of those records. | A plugin that reads the existing records and uses WordPress hooks or its REST API. |

A WordPress plugin can also expose JSON. Choose based on where the data and permissions belong. [Symfony Console](https://symfony.com/doc/current/components/console.html) · [Laravel responses](https://laravel.com/docs/13.x/responses#json-responses) · [WordPress REST API](https://developer.wordpress.org/rest-api/)

### Move the HTTP adapter into Laravel

To try the handoff, use a disposable copy of the Laravel quote app. Copy `code/php-misconceptions/inventory/report.php` from this checkout to `app/inventory-report.php` in the demo app. Append the route below to the demo’s `routes/web.php`, which already imports `Route`. [Laravel quote demo](/series/php-misconceptions/chapters/04-clean-laravel-code/) · [Laravel JSON responses](https://laravel.com/docs/13.x/responses#json-responses)

```php
require_once app_path('inventory-report.php');

Route::get('/inventory.php', static function (\Illuminate\Http\Request $request) {
    parse_str($request->server('QUERY_STRING', ''), $query);
    $inStock = $query['in_stock'] ?? '0';

    if ($inStock !== '0' && $inStock !== '1') {
        return response()->json(['error' => 'in_stock must be 0 or 1.'], 400);
    }

    return response()->json(inventoryReport(inventoryRows(), $inStock === '1'));
});
```

The route reads the raw query string before applying its own filter check. Laravel normally trims strings and converts empty input strings to `null`. Using the raw values here preserves the existing filter’s treatment of empty or padded values. [Laravel input normalization](https://laravel.com/docs/13.x/requests#input-trimming-and-normalization)

Start the Laravel server as in the quote chapter, using that server’s port in the inventory requests. Check that `/inventory.php` still gives HTTP 200 and the same two rows, `?in_stock=1` still gives the one-item list, and `?in_stock=yes` and `?in_stock=` still give HTTP 400 and the `error` field. Compare the decoded JSON and status. A framework response may use different whitespace.

Keep method behavior in your HTTP tests. The standalone endpoint and the Laravel demo both return 405 with `Allow: GET, HEAD` for `POST`. For a `POST` with `Accept: application/json`, Laravel puts the method error in `message` and the standalone endpoint uses `error`. A plain `OPTIONS` request returns 200 with an `Allow` header in the Laravel demo and 405 in the standalone endpoint. Check those differences against what your clients expect before swapping adapters.

When you add a database, replace the sample `inventoryRows()` call with a query that yields the same row shape and order. Keep `inventoryReport()` if the CLI and HTTP adapters still share its filter. On a WordPress site consuming a snapshot from a warehouse, a plugin can read that snapshot and render stock alongside the catalogue it already owns.

## Exercise

1. In `code/php-misconceptions/inventory/report.php`, add `['sku' => 'PHP-103', 'stock' => 7]` after the zero-stock row. Repeat the CLI and HTTP body comparison for the filtered report.
2. Predict what happens if you keep the original array keys. Temporarily replace the filter assignment with the code shown below, repeat both calls, then restore the original filter and remove the sample third row.
3. A WordPress site manages the public catalogue. A separate warehouse system owns the stock counts and needs a nightly export for its own operations. Where would you put the export and where would you display the stock?

```php
$items = array_filter(
    $items,
    static fn (array $item): bool => $item['stock'] > 0,
);
```

<details>
<summary>Check your reasoning</summary>

1. Both filtered responses should contain `{"items":[{"sku":"PHP-101","stock":12},{"sku":"PHP-103","stock":7}]}` in that order.
2. The remaining PHP keys are 0 and 2. With those keys the encoder writes an object inside `items`, even though the CLI and HTTP bodies still match each other. Check the expected JSON structure as well as the equality between the two entry points.
3. Run the export with the warehouse system because it owns the stock data. A WordPress plugin could consume a published stock snapshot for the catalogue. Keep access to the warehouse records with the service that owns them and decide how fresh the public display needs to be.

</details>

## Next chapter

[Is PHP Inherently Insecure?](/series/php-misconceptions/chapters/08-php-security/)
