---
title: "PHP vs HTML"
description: "Learn how HTML describes a page and PHP generates its response. Build a small form and return a personalised greeting."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 3
---

HTML describes what a browser should display. PHP runs code that can generate that HTML before the browser receives it. When a site needs a personalised greeting, a list of database records or a form submission, PHP can calculate the result and return it as ordinary HTML.

| Task | HTML | PHP |
| --- | --- | --- |
| Describe a heading, link or form | Use elements such as `<h1>`, `<a>` and `<form>`. | Produce those elements in the response, often using a template. |
| Get input | A form names fields and describes how the browser submits them. | Read the values received by the server and decide what to do. |
| Show data that changes | The browser renders the markup it receives. | Query data or apply logic to generate different markup for different requests. |
| Run locally | A basic HTML file can open directly in a browser. | Run a PHP server to execute a `.php` file. |

## Send a name from an HTML form to PHP

Save this as `index.html`.

```html
<!doctype html>
<html lang="en">
  <meta charset="utf-8">
  <title>Greeting form</title>
  <form action="greeting.php" method="get">
    <label for="name">Name</label>
    <input id="name" name="name">
    <button>Say hello</button>
  </form>
</html>
```

Save this beside it as `greeting.php`.

```php
<?php
$name = $_GET['name'] ?? '';
$name = is_string($name) ? trim($name) : '';
$name = htmlspecialchars($name === '' ? 'friend' : $name, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
  <meta charset="utf-8">
  <title>Greeting</title>
  <p>Hello, <?= $name ?>!</p>
</html>
```

Run `php -S 127.0.0.1:8000` and open `http://127.0.0.1:8000/`. Type `Sam` and submit to see **Hello, Sam!**. The form sends the name in the query string. PHP reads it, supplies a default for a blank name, and escapes the value before placing it in the HTML.

## What to learn first

Learn enough HTML to build a readable page and an accessible form. Add PHP when the browser needs the server to save, retrieve, or calculate something. Once you can follow one request from form to PHP and back, server rendering and frameworks become easier to understand.

- [Write your first PHP script](/series/php-basics/chapters/01-your-first-php-script/)
- [HTML form specification](https://html.spec.whatwg.org/multipage/forms.html)
- [PHP htmlspecialchars documentation](https://www.php.net/manual/en/function.htmlspecialchars.php)
- [PHP vs CSS](/compare/php-vs-css/) · [All PHP comparisons](/compare/)
