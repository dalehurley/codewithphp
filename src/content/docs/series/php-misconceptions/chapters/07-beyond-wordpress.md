---
title: "07: Is PHP Only for WordPress?"
description: "Share one PHP inventory report between a CLI command and an HTTP endpoint, then decide which tools your application needs."
series: "php-misconceptions"
chapter: 7
estimatedTime: "PT20M"
sidebar:
  order: 7
---

<span id="overview"></span>

## One inventory report for the warehouse

The warehouse owns the stock counts. A colleague needs a snapshot at the command line, and a dashboard needs the same data over HTTP. When both ask for items in stock, they should see the same rows. Where do you put the rule so it stays consistent if the dashboard later needs accounts?

Start with one PHP function and two entry points. Then move the dashboard endpoint to Laravel and check what an existing client would see. WordPress can still run the public catalogue and display stock from the warehouse.

## Quick Start

Use PHP 8.6, the series baseline, Bash or Zsh, and `curl` for the HTTP steps. Run `php -v` to check the PHP version your shell uses, then open a terminal at the root of a local Code with PHP checkout. The standalone inventory sample works without Composer dependencies. [Code with PHP repository](https://github.com/dalehurley/codewithphp) · [PHP command-line interface (CLI)](https://www.php.net/manual/en/features.commandline.php) · [Inventory command source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/inventory.php)

```bash
php code/php-misconceptions/inventory.php
```

```json
{"items":[{"sku":"PHP-101","stock":12},{"sku":"PHP-102","stock":0}]}
```

Add `--in-stock` to print just the row with positive stock. The command writes JSON to standard output so another program can read it. Send an unknown option and capture the two output streams separately. [PHP CLI input and output streams](https://www.php.net/manual/en/features.commandline.io-streams.php)

```bash
php code/php-misconceptions/inventory.php --in-stock
failure_dir="$(mktemp -d)"
php code/php-misconceptions/inventory.php --unknown > "$failure_dir/stdout" 2> "$failure_dir/stderr"
printf 'Exit status: %s\n' "$?"
wc -c < "$failure_dir/stdout"
cat "$failure_dir/stderr"
```

Expect exit status 1, zero bytes on standard output, and a usage message on standard error. The exit status lets a script tell whether the command produced a report.

### Request the same data over HTTP

The dashboard reads the report with HTTP GET. In the first terminal, start PHP’s development server with the inventory example’s public directory as its document root. It serves the lab on loopback. The `opcache.enable=0` setting lets you see edits to the report on the next request. If port 8027 is in use, select a free port and substitute it in the commands that follow. [PHP built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php) · [OPcache settings](https://www.php.net/manual/en/opcache.configuration.php)

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

The first returns HTTP 400 and an `error` field explaining the accepted values. The second returns HTTP 405 and `Allow: GET, HEAD`. An HTTP status describes the server’s response. With these curl flags, curl can exit successfully after receiving a 400 response. The CLI example’s exit status describes the PHP process itself. [HTTP endpoint source](https://github.com/dalehurley/codewithphp/blob/main/code/php-misconceptions/inventory/public/inventory.php) · [HTTP method and status semantics](https://www.rfc-editor.org/rfc/rfc9110.html) · [curl exit codes](https://curl.se/docs/manpage.html#EXIT-CODES)

### Compare the success bodies

While the server is running, use a terminal at the checkout root. These commands save the filtered CLI output and HTTP body to separate files. This time curl omits `-i`, so the file has just the body.

```bash
sample_dir="$(mktemp -d)"
php code/php-misconceptions/inventory.php --in-stock > "$sample_dir/cli.json"
curl -fsS 'http://127.0.0.1:8027/inventory.php?in_stock=1' > "$sample_dir/http.json"
cmp "$sample_dir/cli.json" "$sample_dir/http.json"
```

`cmp` exits successfully without printing anything when the files have the same bytes. Both files should contain `{"items":[{"sku":"PHP-101","stock":12}]}`. Keep the server running for the Laravel comparison or the exercise. Press Ctrl+C in its terminal when you finish.

## Share the report logic

The matching output comes from a shared function. Each adapter is the code that translates input and output for one entry point. The command-line adapter turns `--in-stock` into a boolean. The HTTP adapter checks the query value and turns `in_stock=1` into the same boolean. Both pass rows from `inventoryRows()` and that boolean to `inventoryReport()`. Each adapter encodes the returned PHP array as JSON.

The paths below are relative to `code/php-misconceptions/`.

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

The file has the same one-item list. The warehouse can have a scheduler launch this command each night. If inventory imports later arrive as jobs in a queue, a worker can process them and call the same report function when it needs a snapshot. [Laravel scheduling](https://laravel.com/docs/13.x/scheduling) · [Laravel queues](https://laravel.com/docs/13.x/queues)

## Choose the tool that owns the work

| Requirement | A reasonable starting point |
| --- | --- |
| The warehouse needs a nightly export and a small read-only dashboard endpoint. | Use plain PHP for the command and endpoint, as in the lab. |
| The warehouse team adds interactive commands for checking and importing counts. | Use a component such as Symfony Console for help text, options and prompts. |
| The warehouse dashboard adds user accounts and routes for managing stock and imports. | Use an application framework such as Laravel for routing, authentication and jobs. Keep the report function available to the export. |
| Editors manage products in WordPress and want to display warehouse stock on catalogue pages. | Use a WordPress plugin to read a stock snapshot and show counts alongside the products editors already manage. |

WordPress is a content management system built with PHP. Plugins extend its editorial interface and can also expose JSON. The warehouse and catalogue can each own their data and permissions while sharing a stock snapshot. [Symfony components](https://symfony.com/doc/current/components/index.html) · [Symfony Console](https://symfony.com/doc/current/components/console.html) · [Laravel routing](https://laravel.com/docs/13.x/routing) · [WordPress plugin handbook](https://developer.wordpress.org/plugins/) · [WordPress REST API](https://developer.wordpress.org/rest-api/)

### Move the HTTP adapter into Laravel

Suppose the warehouse starts moving its dashboard to Laravel. First move the existing read-only endpoint and check its behaviour before adding accounts. This optional lab uses the disposable Laravel 13 quote app from Chapter 04. Its setup needs Composer 2.2 or later, Laravel’s required PHP extensions and PDO SQLite. [Laravel quote demo setup](/series/php-misconceptions/chapters/04-clean-laravel-code/#quick-start) · [Laravel PHP requirements](https://laravel.com/docs/13.x/deployment#server-requirements)

In a new terminal at the Code with PHP checkout root, save the path to the inventory report.

```bash
inventory_report="$PWD/code/php-misconceptions/inventory/report.php"
```

In the same terminal, run Chapter 04’s Quick Start block. It leaves you in the temporary `php-quotes` app. From that directory, copy the report and append the PHP below to `routes/web.php`, which already imports `Route`. [Laravel JSON responses](https://laravel.com/docs/13.x/responses#json-responses)

```bash
cp "$inventory_report" app/inventory-report.php
```

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

The standalone script checks the values PHP parsed into `$_GET`. Laravel normally trims strings and converts empty input strings to `null`. The new adapter parses the original query string so an empty value or a space-padded `1` still returns 400. Both entry points continue to use `inventoryReport()` to select the rows. [PHP query-string parsing](https://www.php.net/manual/en/function.parse-str.php) · [Laravel input normalization](https://laravel.com/docs/13.x/requests#input-trimming-and-normalization)

From the Laravel app root, confirm the route list shows `GET|HEAD inventory.php`. Start Laravel on a different loopback port with in-memory session and cache drivers so the inventory requests can run without querying the starter database. Turn off debug output for the method probes so the error response is easy to read. Check the port Artisan prints and use it in the comparison below. [Laravel error configuration](https://laravel.com/docs/13.x/errors#configuration)

```bash
php artisan route:list --path=inventory
APP_DEBUG=false SESSION_DRIVER=array CACHE_STORE=array \
  php artisan serve --host=127.0.0.1 --port=8028 --no-reload
```

### Check the dashboard’s GET contract

With both servers running, open another terminal. The checks compare each status and decoded JSON body with what the dashboard expects. Re-encoding removes formatting differences while retaining list and object shapes and the types of the values. The expected report uses a list of rows with string SKUs and integer stock counts. [PHP JSON encoding](https://www.php.net/manual/en/function.json-encode.php)

```bash
standalone_url='http://127.0.0.1:8027/inventory.php'
laravel_url='http://127.0.0.1:8028/inventory.php'
get_body="$(mktemp)"

check_get() {
  local http_status json
  http_status="$(curl -sS -o "$get_body" -w '%{http_code}' "$1")" || return 1
  json="$(php -r '
    $decoded = json_decode(file_get_contents($argv[1]), flags: JSON_THROW_ON_ERROR);
    echo json_encode($decoded, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
  ' "$get_body")" || return 1
  if [ "$http_status" = "$2" ] && [ "$json" = "$3" ]; then
    printf 'PASS %s\n' "$1"
  else
    printf 'FAIL %s (HTTP %s, body %s)\n' "$1" "$http_status" "$json"
    return 1
  fi
}

all_items='{"items":[{"sku":"PHP-101","stock":12},{"sku":"PHP-102","stock":0}]}'
in_stock_items='{"items":[{"sku":"PHP-101","stock":12}]}'
filter_error='{"error":"in_stock must be 0 or 1."}'

get_checks_failed=0
for url in "$standalone_url" "$laravel_url"; do
  check_get "$url" 200 "$all_items" || get_checks_failed=1
  check_get "$url?in_stock=0" 200 "$all_items" || get_checks_failed=1
  check_get "$url?in_stock=1" 200 "$in_stock_items" || get_checks_failed=1
  for query in 'in_stock=yes' 'in_stock=' 'in_stock=%201%20' 'in_stock%5B%5D=1'; do
    check_get "$url?$query" 400 "$filter_error" || get_checks_failed=1
  done
done
rm "$get_body"
test "$get_checks_failed" -eq 0
```

Expect seven `PASS` lines per server. The final `test` exits with status 1 if any check fails. Check headers and the other methods separately. The filtered HEAD request returns 200 and headers without a body. Both servers use the `application/json` media type for the report. The standalone endpoint includes `charset=utf-8`, and Laravel emits `application/json` by itself. [HTTP method and status semantics](https://www.rfc-editor.org/rfc/rfc9110.html)

```bash
for url in "$standalone_url" "$laravel_url"; do
  curl -sS -I "$url?in_stock=1"
  curl -sS -i -w '\n' -X POST -H 'Accept: application/json' "$url"
  curl -sS -i -X OPTIONS "$url"
done
```

Both return 405 with `Allow: GET, HEAD` for POST. With that `Accept` header, Laravel’s JSON error uses `message` and the standalone endpoint uses `error`. For OPTIONS, Laravel returns 200 with an `Allow` header and the standalone endpoint returns 405. If an existing client relies on those details, account for them before switching. Stop Laravel with Ctrl+C. You can keep the standalone server running for the exercise below.

The copy lets the two labs run side by side. In the warehouse application, keep one source for the function that the nightly export and dashboard both call. When stock moves into a database, feed that function rows with the same fields, types and order.

## Exercise

1. In `code/php-misconceptions/inventory/report.php`, add `['sku' => 'PHP-103', 'stock' => 7]` after the zero-stock row. Repeat the CLI and HTTP body comparison for the filtered report.
2. Predict what happens if you keep the original array keys. Temporarily replace the filter assignment with the code shown below, repeat both calls, then restore the original filter and remove the sample third row.
3. Set all the sample stock counts to zero and rerun the filtered command and request. Check the type and value of `items`, then restore the original counts.
4. The warehouse needs to keep the nightly export and now wants a dashboard with accounts. Editors still manage the public catalogue in WordPress. Where do the stock counts live? Which entry point benefits from Laravel? When would Symfony Console help, and what rule should the report keep in one place?

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
3. Both responses should contain `{"items":[]}`. An empty list is a valid report when no rows qualify.
4. Keep the counts and export in the warehouse system. Use Laravel for the dashboard and its account rules. Symfony Console can help once warehouse commands need richer prompts or option handling. The export and dashboard can both call the same positive-stock filter. A WordPress plugin can display a warehouse snapshot alongside its public products, with the refresh interval set by how current the display needs to be.

</details>

## Next chapter

[Is PHP Inherently Insecure?](/series/php-misconceptions/chapters/08-php-security/)
