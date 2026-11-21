---
title: "PHP for HTML and CSS Developers"
description: "Turn a static HTML form into a PHP page that reads a name and renders a greeting. Follow a small, complete example that runs locally."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-21"
prev: false
next: false
---

If you can write an HTML form, you can make your first PHP page. Put a form and some PHP in one file, then submit your name and watch the server render a greeting in the same page.

## Start with the form you already know

Create an empty folder called `php-greeting`. Inside it, save this form in a file named `index.php`.

```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP greeting</title>
</head>
<body>
    <main>
        <h1>PHP greeting</h1>

        <form method="get" action="/">
            <label for="name">Your name</label>
            <input type="text" id="name" name="name" required>
            <button type="submit">Say hello</button>
        </form>
    </main>
</body>
</html>
```

Run these commands from inside the folder. If `php -v` fails, follow the [PHP setup lesson](/series/php-basics/chapters/00-setting-up-your-development-environment/).

```sh
php -v
php -S 127.0.0.1:8000
```

Open [the local greeting form](http://127.0.0.1:8000/) in your browser. Enter `Sam` and submit the form. You should see `?name=Sam` in the address bar. The form sends the name, which the next step turns into a greeting. This local example uses GET so you can see the submitted value in the URL. Use it for information you are comfortable putting in a URL. [HTML forms and user input](/series/php-basics/chapters/05-handling-html-forms-and-user-input/)

## Read the input with PHP

Add this block above `<!doctype html>` in `index.php`. Keep the existing HTML below it.

```php
<?php
declare(strict_types=1);

$submittedName = $_GET['name'] ?? '';
$name = is_string($submittedName) ? trim($submittedName) : '';
?>
```

PHP reads the form field by its HTML `name` attribute. The `?? ''` default covers the first visit. `is_string()` checks that the field is text before `trim()` removes surrounding whitespace.

Add this block directly after `</form>`.

```php
<?php if ($name !== ''): ?>
    <p>Hello, <?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>!</p>
<?php elseif (isset($_GET['name'])): ?>
    <p>Enter a name to see a greeting.</p>
<?php endif; ?>
```

The server executes the PHP between `<?php` and `?>`. The shorter `<?=` tag prints a value into the HTML response. `htmlspecialchars()` escapes characters that have a special meaning in HTML, so the browser renders the name as text. [PHP tags](https://www.php.net/manual/en/language.basic-syntax.phptags.php) · [HTML escaping in PHP](https://www.php.net/manual/en/function.htmlspecialchars.php)

## Try the form

1. Visit the page without a query string. The form appears on its own.
2. Submit `Sam`. Expect **Hello, Sam!**
3. Submit `A & B`. Expect **Hello, A & B!** In your browser's page source, the ampersand in the greeting is written as `&amp;`.
4. Submit a few spaces. Expect **Enter a name to see a greeting.** The text field accepts spaces, and PHP trims them before checking the name.

To check your work, <a href="/downloads/learn/php-greeting.txt" download="index.php">download the finished index.php</a>. If you want to run the download, save it as `index.php` in its own folder. Stop the previous server with **Ctrl+C**, then run `php -S 127.0.0.1:8000` from the new folder.

Use your usual CSS to style the form and greeting. If you want to keep results between visits, try the [PHP and SQLite reading list starter](/series/php-basics/sqlite-starter/). Stop the local server with **Ctrl+C** when you are done.
