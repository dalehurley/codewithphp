---
title: "PHP vs JavaScript"
description: "Choose where PHP and JavaScript fit in a web app. Build a form that works with a normal PHP response and updates in place with JavaScript fetch."
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-22"
sidebar:
  order: 2
---

Use **PHP** when the server needs to render pages, save data or apply rules that users cannot change. Use **JavaScript in the browser** for interactions such as updating a result, filtering a list or showing progress without navigating away. Use both when a PHP application needs an interactive interface. If you are choosing a language for the server, compare PHP with a server runtime such as Node.js.

[Run the print quote form](#build-a-print-quote-form) · [Choose what to learn first](#which-should-you-learn-first)

| What are you building? | Where the work belongs |
| --- | --- |
| A page with content from a database | PHP can read the data and render the first response. The browser receives HTML and any scripts the page needs. |
| A cart that gives feedback as quantities change | Browser JavaScript can update the interface immediately. The server recalculates and validates the total when an order is placed. |
| A form that should submit when JavaScript is unavailable | HTML can send a regular request to PHP. JavaScript can enhance it with an in-page result. |
| A backend using an existing JavaScript toolchain | Node.js can handle HTTP, database access and server-side validation. Compare it with PHP on a representative feature and its deployment. |

## Where browser code and server code meet

The browser loads HTML, CSS and JavaScript. A JavaScript event handler can read the value of an input. Send an HTTP request to get fresh data from PHP. Think in terms of a method, a URL, an input format and a response. For example, a `POST` can send a quantity to PHP, and PHP can return either an HTML page or a JSON result. A browser can inspect the JavaScript and responses it receives. Keep private credentials and order rules on the server. [HTML forms](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/form) · [Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch)

A required field and an input range help users fix errors before they submit. PHP still has to validate the received value because requests can also come from scripts, saved forms and other clients. For a real order, the server looks up the price and checks the user’s permission before saving. Returning a quote has no side effect in the example below. [Form validation](https://developer.mozilla.org/en-US/docs/Learn_web_development/Extensions/Forms/Form_validation) · [PHP filter_input](https://www.php.net/manual/en/function.filter-input.php)

## Working with data and tools

PHP arrays can represent either a list or a map. JavaScript usually expresses those shapes as an array and an object, respectively. This matters at the JSON boundary. PHP’s `array_filter()` keeps the original keys, so filtering positions `0`, `1` and `2` down to `1` and `2` can produce a JSON object with numeric keys. JavaScript’s `filter()` creates a compact array. Use `array_values()` on the PHP result before encoding when the client expects a JSON list. [PHP array_filter](https://www.php.net/manual/en/function.array-filter.php) · [PHP json_encode](https://www.php.net/manual/en/function.json-encode.php) · [JavaScript Array.filter](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/filter)

In a small PHP app, you can serve a plain JavaScript file alongside templates and test requests with the browser and `curl`. If the frontend grows, evaluate tools for organising and building its modules. If you choose Node.js for the backend, you can use JavaScript across the app and share pure functions, such as calculating a display label. Browser APIs such as the DOM and server APIs such as file access still have different jobs. Account for both the deployment environment and the code your team already maintains. [Node.js and browser differences](https://nodejs.org/en/learn/getting-started/differences-between-nodejs-and-the-browser) · [PHP vs Node.js](/compare/php-vs-nodejs/)

## Build a print quote form

A print shop charges $4.50 per copy for orders of 1 to 10 copies. Build a form that POSTs a quantity to PHP. A regular submission comes back as a new HTML page. With JavaScript enabled, the same form asks for JSON and displays the result in place. The price and calculation live in one place on the server.

### Get the files and check PHP

Use PHP 8.0 or later and a current browser. The enhanced path uses Fetch, FormData, URLSearchParams and AbortController. The native form also works when those APIs are unavailable. Download the two files or copy the code below into the same directory. The browser needs no packages or build step. [index.php](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-javascript/index.php) · [quote.js](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-javascript/quote.js)

```sh
php -v
```

If your shell cannot find PHP, follow the setup guide before starting the server. [Install PHP on macOS, Windows or Linux](/series/php-basics/chapters/00-setting-up-your-development-environment/)

### Calculate the quote in PHP

Save as `index.php`. The native form and `fetch()` send the same URL-encoded quantity. PHP accepts whole numbers from 1 to 10 and returns `422` for invalid data. This small handler selects JSON for the exact `Accept: application/json` sent by the script and uses HTML for regular form submissions. `Vary: Accept` identifies the header that selects the response. Unsupported methods get `405` with the supported methods in `Allow`. [PHP form inputs](https://www.php.net/manual/en/reserved.variables.post.php) · [Vary header](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Vary) · [405 Method Not Allowed](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/405)

```php title="index.php"
<?php
declare(strict_types=1);

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) {
    header('Allow: GET, HEAD, POST');
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(405);
    exit('Method not allowed.');
}

header('Content-Type: text/html; charset=UTF-8');
if ($method === 'HEAD') {
    exit;
}

$quantity = null;
$message = '';
$invalid = false;

if ($method === 'POST') {
    header('Vary: Accept');
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 10],
    ]);

    if (is_int($quantity)) {
        $unit = $quantity === 1 ? 'copy' : 'copies';
        $total = number_format($quantity * 450 / 100, 2, '.', '');
        $message = "Estimated total for $quantity $unit is \$$total.";
    } else {
        $invalid = true;
        http_response_code(422);
        $message = 'Enter a whole number from 1 to 10.';
    }

    if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') {
        header('Content-Type: application/json');
        echo json_encode(['message' => $message], JSON_THROW_ON_ERROR);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Print quote</title>
  <script src="quote.js" defer></script>
</head>
<body>
  <h1>Print quote</h1>
  <form id="quote-form" action="index.php" method="post">
    <label for="quantity">Number of copies</label>
    <input id="quantity" name="quantity" type="number"
           value="<?= is_int($quantity) ? $quantity : '' ?>"
           min="1" max="10" step="1" required
           aria-describedby="quantity-hint quote-result"
           aria-invalid="<?= $invalid ? 'true' : 'false' ?>">
    <p id="quantity-hint">Enter a whole number from 1 to 10.</p>
    <button type="submit">Get quote</button>
  </form>
  <p id="quote-result" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
</body>
</html>
```

### Show the result with JavaScript

Save as `quote.js`. When the needed APIs are available, the submit handler uses `FormData` to read the named field and `URLSearchParams` to encode it. The browser can announce the changing text in the status region. The input refers to its range hint and result, and the script marks it invalid if the server rejects the value. [URLSearchParams constructor](https://developer.mozilla.org/en-US/docs/Web/API/URLSearchParams/URLSearchParams) · [ARIA status role](https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Reference/Roles/status_role) · [ARIA invalid state](https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Reference/Attributes/aria-invalid)

The submitted quantity stays fixed while the request is running. When the user edits the field after a response, the old quote clears. The handler checks HTTP status, content type and message before it displays the response. An aborted request has its own message, and each response or failure restores the controls. Here the browser stops waiting after five seconds. [Fetch response status](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch#checking_response_status) · [AbortController](https://developer.mozilla.org/en-US/docs/Web/API/AbortController)

```javascript title="quote.js"
const form = document.querySelector("#quote-form");
const quantity = document.querySelector("#quantity");
const button = form.querySelector("button");
const result = document.querySelector("#quote-result");

if ([window.fetch, window.FormData, window.URLSearchParams, window.AbortController]
    .every((api) => typeof api === "function")) {
  quantity.addEventListener("input", () => {
    quantity.setAttribute("aria-invalid", "false");
    result.textContent = "";
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (button.disabled) return;

    const body = new URLSearchParams(new FormData(form));
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 5000);
    button.disabled = true;
    quantity.readOnly = true;
    result.textContent = "Calculating quote…";

    try {
      const response = await fetch(form.action, {
        method: "POST",
        headers: { Accept: "application/json" },
        signal: controller.signal,
        body,
      });
      const contentType = response.headers.get("content-type")?.split(";")[0].trim();
      if (contentType !== "application/json" || (!response.ok && response.status !== 422)) {
        throw new Error("Unexpected quote response");
      }

      const { message } = await response.json();
      if (typeof message !== "string" || message.trim() === "") {
        throw new Error("Missing quote message");
      }

      quantity.setAttribute("aria-invalid", String(response.status === 422));
      result.textContent = message;
    } catch {
      result.textContent = controller.signal.aborted
        ? "The quote request timed out. Try again."
        : "The quote could not be calculated. Please try again.";
    } finally {
      clearTimeout(timeout);
      quantity.readOnly = false;
      button.disabled = false;
    }
  });
}
```

### Run the form and check both paths

Start the local development server in the directory with both files. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php)

```sh
php -S 127.0.0.1:8000
```

Open [the quote form](http://127.0.0.1:8000/), enter `3` and click **Get quote**. In the browser’s Network panel, inspect the POST, the submitted quantity and the response type. Change the quantity to clear the displayed quote. Disable JavaScript and repeat the submission to see the same quoted amount in a new HTML response.

From a second terminal on macOS or Linux, run these commands to check the server directly. On Windows, use `curl.exe`. The `-i` flag prints the response headers along with the body.

```sh
curl -i -H 'Accept: application/json' --data 'quantity=3' http://127.0.0.1:8000/index.php
curl -i -H 'Accept: application/json' --data 'quantity=11' http://127.0.0.1:8000/index.php
curl -i -X PUT http://127.0.0.1:8000/index.php
```

| Try | Expected result |
| --- | --- |
| Submit `3` in the browser with JavaScript enabled | HTTP `200` with JSON. The page stays put and displays `Estimated total for 3 copies is $13.50.` |
| Submit `3` with JavaScript disabled | HTTP `200` with HTML. The new page displays the same quote. |
| POST `11` directly with the JSON header | HTTP `422` with `{"message":"Enter a whole number from 1 to 10."}`. |
| Send a PUT request | HTTP `405` with `Allow: GET, HEAD, POST`. |

For a failure test, load the form with JavaScript enabled and stop the local server before submitting. The browser displays a retry message when the request fails and makes the controls available again. Restart PHP and submit again to confirm recovery.

When a form starts saving data, keep the server-side validation and add the app’s authentication, permission checks and a CSRF token for state-changing requests. Test the request handler with valid and invalid inputs, then exercise the interface with and without the enhancement. [Handling forms in PHP](/series/php-basics/chapters/05-handling-html-forms-and-user-input/) · [Working with JSON and APIs](/series/php-basics/chapters/23-working-with-json-and-apis/) · [CSRF protection for forms](/series/php-basics/chapters/15b-csrf-protection-and-form-security/)

## Which should you learn first?

Start with JavaScript if you want to respond to clicks and change a page in the browser. Start with PHP if you want to handle forms, read or save data, or work on a PHP application. For a full-stack project, get one form working with a regular request first. Then add an in-page JavaScript response so you can see what each side contributes. [PHP Basics](/series/php-basics/) · [JavaScript language guide](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide) · [PHP vs HTML](/compare/php-vs-html/) · [All PHP comparisons](/compare/)
