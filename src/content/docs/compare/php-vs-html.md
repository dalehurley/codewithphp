---
title: "PHP vs HTML"
description: "Learn when static HTML is enough and when to add PHP. Build a workshop form that validates a request and generates the next page on the server."
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-22"
sidebar:
  order: 3
---

**HTML** gives a web page its structure. Use it for headings, links, lists and forms. **PHP** runs on the server and can generate HTML using data from a request, a file or a database. Use plain HTML for a page that sends the same content to every visitor. Add PHP when the server needs to choose what to show or process a submission. The browser renders HTML in both cases. [PHP introduction](https://www.php.net/whatisphp)

[Run the workshop example](#build-a-workshop-schedule) · [Choose what to learn first](#what-to-learn-first)

| What are you building? | Where HTML fits | Where PHP fits |
| --- | --- | --- |
| A welcome page with the same text for everyone | Write the headings, links and copy in an `.html` file. | Add a server-side template when the page needs content chosen for each request. |
| A form that asks for an input | Define the label, fields and submit button. The browser can send their values. | Read and validate the submitted values and decide what response to send. |
| A results page that changes with the input | Define how the result is structured, such as a heading and a list. | Choose the data and generate the markup for that result. |
| A page to open on your computer | Open a static HTML file in the browser or serve it over HTTP. | Run the script through a local PHP server so the browser receives its output. |

## When static HTML is enough

Use a static page for material such as an event introduction, contact details or a fixed timetable you update by publishing a new file. A static site generator can also assemble HTML from templates at build time and publish the resulting files to static hosting. Choose server rendering when the input or data needed to assemble the page is only known when the request arrives. A single site can serve its welcome page as a static file and use PHP for the timetable. [Client and server overview](https://developer.mozilla.org/en-US/docs/Learn_web_development/Extensions/Server-side/First_steps/Client-Server_overview)

## How an HTML form and PHP work together

The HTML form defines where to send the request with `action` and how to send it with `method`. The browser sends the value under the field’s `name`. HTML attributes such as `required` help visitors correct mistakes in the browser. PHP validates what actually arrived before using it. For a schedule filter, a `GET` request puts the selected day in the URL so readers can bookmark a result. For an action that saves a booking, use `POST` and apply the app’s validation, authorisation and form protection. [HTML forms standard](https://html.spec.whatwg.org/multipage/forms.html) · [HTTP GET semantics](https://www.rfc-editor.org/rfc/rfc9110.html#name-get) · [PHP form security](/series/php-basics/chapters/15b-csrf-protection-and-form-security/)

## Build a workshop schedule

Build a static welcome page with a day picker, then add a PHP page that looks up the selected day and renders the schedule. The example uses a small read-only set of workshop titles in the script. Monday is a valid choice with no workshops.

### Get the files and check PHP

Use PHP 8.0 or later and a browser. Save both files in the same new directory, either by downloading them or by copying the code below. Run the commands from that directory. [index.html](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-html/index.html) · [workshops.php](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-html/workshops.php)

```sh
php -v
```

This should print the installed PHP CLI version. If your shell cannot find PHP, start with the installation lesson. [Install PHP on macOS, Windows or Linux](/series/php-basics/chapters/00-setting-up-your-development-environment/)

### Start with the static welcome page

Save as `index.html`. You can open this file directly to read the welcome text and see the labelled form. Run the local server before submitting it. The form’s `name="day"` sends the chosen value to `workshops.php`.

```html title="index.html"
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Web workshops</title>
</head>
<body>
  <main>
    <h1>Web workshops</h1>
    <p>Explore short sessions on building pages and working with forms.</p>

    <form action="workshops.php" method="get">
      <label for="day">Workshop day</label>
      <select id="day" name="day" aria-describedby="day-hint" required>
        <option value="">Choose a day</option>
        <option value="saturday">Saturday</option>
        <option value="sunday">Sunday</option>
        <option value="monday">Monday</option>
      </select>
      <p id="day-hint">Choose a day to see its scheduled workshops.</p>
      <button type="submit">Show workshops</button>
    </form>
  </main>
</body>
</html>
```

### Render the schedule in PHP

Save as `workshops.php` in the same directory. PHP reads `day` from `$_GET` and checks that it is one of the known day names. It returns the selected list, an empty result for Monday, or an error with a link back to the form. [PHP query parameters](https://www.php.net/manual/en/reserved.variables.get.php)

```php title="workshops.php"
<?php
declare(strict_types=1);

function escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET' && $method !== 'HEAD') {
    header('Allow: GET, HEAD');
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(405);
    exit('Use GET or HEAD for the workshop schedule.');
}

$workshops = [
    'saturday' => ['HTML & accessibility', 'From <form> to PHP'],
    'sunday' => ['Write your first PHP page'],
    'monday' => [],
];

$day = $_GET['day'] ?? null;
$error = null;
$title = 'Choose a workshop day';
$sessions = [];

if ($day === null || $day === '') {
    $error = 'Choose a day on the workshop welcome page.';
} elseif (!is_string($day) || !array_key_exists($day, $workshops)) {
    $error = 'Choose Saturday, Sunday or Monday.';
} else {
    $title = 'Workshops on ' . ucfirst($day);
    $sessions = $workshops[$day];
}

header('Content-Type: text/html; charset=UTF-8');
if ($error !== null) {
    http_response_code(400);
}
if ($method === 'HEAD') {
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= escape($title) ?></title>
</head>
<body>
  <main>
    <h1><?= escape($title) ?></h1>
    <?php if ($error !== null): ?>
      <p><?= escape($error) ?></p>
    <?php elseif ($sessions === []): ?>
      <p>No workshops are scheduled for this day.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($sessions as $session): ?>
          <li><?= escape($session) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <p><a href="index.html">Back to the workshop form</a></p>
  </main>
</body>
</html>
```

The HTML template handles the heading, list and link. PHP fills in the heading and uses a loop to render the workshop titles. `htmlspecialchars()` escapes characters such as `&` and `<` for this HTML output. The browser displays `From <form> to PHP` as ordinary text. In the response source you can find `From &lt;form&gt; to PHP`. [PHP htmlspecialchars](https://www.php.net/manual/en/function.htmlspecialchars.php)

### Run the project and check the responses

Start the PHP development server from the example directory. [PHP development server](https://www.php.net/manual/en/features.commandline.webserver.php)

```sh
php -S 127.0.0.1:8000
```

Open [the welcome page](http://127.0.0.1:8000/) and choose Saturday. Submit to see the browser navigate to `/workshops.php?day=saturday` and display two workshops. Try Monday, then use the link back to the form and choose Sunday. The form also works with JavaScript disabled.

From a second terminal, inspect the headers and HTML of the follow-up request. On Windows PowerShell, use `curl.exe` for these commands.

```sh
curl -i 'http://127.0.0.1:8000/workshops.php?day=saturday'
curl -i 'http://127.0.0.1:8000/workshops.php?day=monday'
curl -i 'http://127.0.0.1:8000/workshops.php'
curl -i 'http://127.0.0.1:8000/workshops.php?day=friday'
curl -I 'http://127.0.0.1:8000/workshops.php?day=saturday'
curl -i -X POST 'http://127.0.0.1:8000/workshops.php'
```

| Request | Expected result |
| --- | --- |
| GET with `day=saturday` | `200` and an HTML list with two workshops. |
| GET with `day=monday` | `200` and **No workshops are scheduled for this day.** |
| GET with a missing or blank day | `400` and **Choose a day on the workshop welcome page.** |
| GET with an unknown day or an array-shaped value | `400` and **Choose Saturday, Sunday or Monday.** |
| HEAD with `day=saturday` | `200` with HTML response headers and an empty body. |
| POST to the schedule endpoint | `405` with `Allow: GET, HEAD`. |

The GET responses use `Content-Type: text/html; charset=UTF-8`. Saturday and Monday differ because PHP selects different data for the same template. Monday’s message is a valid result. A missing or invalid day needs a new selection. [HTTP HEAD and errors](https://www.rfc-editor.org/rfc/rfc9110.html#name-head) · [405 Method Not Allowed](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/405)

## What to learn first

Start with enough HTML to build a page you can navigate and a form with a label for each field. Learn PHP next when you need to read a request and use it to produce a result. Follow one request in your browser’s Network panel and compare the returned HTML with the page the browser renders. For an interface that updates in place, continue to the JavaScript comparison. [Write your first PHP script](/series/php-basics/chapters/01-your-first-php-script/) · [Trace a PHP request](/teach/php-request-lifecycle/) · [PHP vs JavaScript](/compare/php-vs-javascript/) · [PHP vs CSS](/compare/php-vs-css/) · [All PHP comparisons](/compare/)
