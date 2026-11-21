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

Use PHP 8.0 or later and a current browser with Fetch, FormData and URLSearchParams. Save the two files below in the same directory. The browser needs no packages or build step.

### Calculate the quote in PHP

Save as `index.php`. The form and `fetch()` both send URL-encoded data. PHP reads the quantity with an integer filter that enforces the allowed range. An invalid quantity gets HTTP 422. For this example the JSON response is selected by the exact `Accept: application/json` header sent by the script. Plain form submissions receive HTML.

```php
<?php
declare(strict_types=1);

$quantity = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 10],
    ]);

    if (is_int($quantity)) {
        $cents = $quantity * 450;
        $message = 'Estimated total $' . number_format($cents / 100, 2, '.', '');
    } else {
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
           min="1" max="10" step="1" required>
    <button type="submit">Get quote</button>
  </form>
  <p id="quote-result" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
</body>
</html>
```

### Show the result with JavaScript

Save as `quote.js`. The browser’s `submit` event is the point where the script takes over. `FormData` reads the named field and `URLSearchParams` supplies the URL-encoded request body. PHP still validates and calculates the result. The script handles 422 separately because `fetch()` resolves with an HTTP response even when the status indicates an error. It places the message in the existing status region as text. [URLSearchParams constructor](https://developer.mozilla.org/en-US/docs/Web/API/URLSearchParams/URLSearchParams) · [Fetch response status](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch#checking_response_status) · [ARIA status role](https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Reference/Roles/status_role)

```javascript
const form = document.querySelector("#quote-form");
const button = form.querySelector("button");
const result = document.querySelector("#quote-result");

form.addEventListener("submit", async (event) => {
  event.preventDefault();
  if (button.disabled) return;

  button.disabled = true;
  result.textContent = "Calculating quote…";

  try {
    const response = await fetch(form.action, {
      method: "POST",
      headers: { Accept: "application/json" },
      body: new URLSearchParams(new FormData(form)),
    });

    if (!response.ok && response.status !== 422) {
      throw new Error("Quote request failed");
    }

    const data = await response.json();
    result.textContent = data.message;
  } catch {
    result.textContent = "The quote could not be calculated. Please try again.";
  } finally {
    button.disabled = false;
  }
});
```

### Run and test both paths

Start the local development server in the directory with both files. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php)

```sh
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000/`, enter `3` and click **Get quote**. The result is **Estimated total $13.50**. In the browser’s Network panel you can see the POST and JSON response while the page stays in place. Disable JavaScript and repeat the submission to get the same quote in the new HTML response.

Now try a value the form normally stops. Send it directly to the running local server from a second terminal to check the server’s rule. The response has status `422` and a body of `{"message":"Enter a whole number from 1 to 10."}`.

```sh
curl -i -H 'Accept: application/json' --data 'quantity=11' http://127.0.0.1:8000/index.php
```

When a form starts saving data, keep the server-side validation and add the app’s authentication, permission checks and protection for state-changing requests. Test the request handler with valid and invalid inputs, then exercise the interface with and without the enhancement. [Handling forms in PHP](/series/php-basics/chapters/05-handling-html-forms-and-user-input/) · [Working with JSON and APIs](/series/php-basics/chapters/23-working-with-json-and-apis/)

## Which should you learn first?

Start with JavaScript if you want to respond to clicks and change a page in the browser. Start with PHP if you want to handle forms, read or save data, or work on a PHP application. For a full-stack project, get one form working with a regular request first. Then add an in-page JavaScript response so you can see what each side contributes. [PHP Basics](/series/php-basics/) · [JavaScript language guide](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide) · [PHP vs HTML](/compare/php-vs-html/) · [All PHP comparisons](/compare/)
