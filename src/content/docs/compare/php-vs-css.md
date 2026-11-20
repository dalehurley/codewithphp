---
title: "PHP vs CSS"
description: "Understand how PHP generates a page and CSS styles it. Put both to work on a status label that reflects server-side data."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 14
---

PHP can decide what a page should say based on a request or database result. CSS describes how the resulting HTML should look in the browser. A ticket page can use PHP to determine whether the ticket is open and CSS to give that status a consistent visual style.

| Task | PHP | CSS |
| --- | --- | --- |
| Choose the status | Read the stored record and decide which text and class to render. | Select an element using its class and style it. |
| Control layout | Emit elements and any HTML attributes the page needs. | Use layout features such as grid, flexbox and media queries. |
| Reuse styles | Reference a shared stylesheet from a template. | Define the same class for use across many pages. |
| Run the code | A PHP runtime produces the HTML response. | The browser applies rules to the HTML it has received. |

## Render a status label

```php
<?php
$shipped = true; // In an application this can come from a database record.
$class = $shipped ? 'status--shipped' : 'status--pending';
$label = $shipped ? 'Shipped' : 'Pending';
?>
<!doctype html>
<html lang="en">
  <meta charset="utf-8">
  <title>Order status</title>
  <link rel="stylesheet" href="status.css">
  <p class="status <?= $class ?>"><?= $label ?></p>
</html>
```

```css
.status {
  width: fit-content;
  padding: 0.35rem 0.7rem;
  border-radius: 0.35rem;
}

.status--shipped {
  color: #164f27;
  background: #def3e3;
}

.status--pending {
  color: #423417;
  background: #fbeabe;
}
```

Save the files as `index.php` and `status.css` in the same folder. Start `php -S 127.0.0.1:8000` and open it in a browser. The label reads `Shipped` and has a green background. Change `$shipped` to `false` and reload to see `Pending` with a different background. The text carries the meaning even if someone cannot distinguish the colours.

## Keep the jobs clear

Use PHP to render safe, meaningful markup and a small set of status classes. Use a static stylesheet for design rules that can be shared and cached. When a developer needs to change the colour of every pending item, they can edit one CSS rule without changing how the application calculates the status.

- [CSS cascade and conflicting rules](https://developer.mozilla.org/en-US/docs/Learn_web_development/Core/Styling_basics/Handling_conflicts)
- [PHP vs HTML](/compare/php-vs-html/) · [PHP vs JavaScript](/compare/php-vs-javascript/) · [All PHP comparisons](/compare/)
