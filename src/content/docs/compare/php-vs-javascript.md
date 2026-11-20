---
title: "PHP vs JavaScript"
description: "See what PHP does on a server and what JavaScript does in a browser, with matching examples and a guide to using them together."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 2
---

Use PHP to process a web request and produce a response. Use JavaScript in the browser when the interface needs to react after the response arrives. For example, PHP can save an order to a database while JavaScript updates the cart total as the customer edits its quantities.

| Question | PHP | JavaScript |
| --- | --- | --- |
| Where does this code run? | On a server or through a local CLI runtime. | In browsers and in server runtimes such as Node.js. |
| How do I represent a list of records? | An array of arrays or objects. | An array of objects. |
| Who can inspect the code? | Server-side source stays on the server when PHP is configured to execute it. | Browser scripts and responses are available to the browser user. |
| How do they communicate? | An HTTP endpoint can return HTML or JSON. | A browser can display HTML and request JSON through `fetch`. |

## Count the unfinished tasks

```php
<?php
$tasks = [
    ['title' => 'Read', 'done' => true],
    ['title' => 'Build', 'done' => false],
    ['title' => 'Test', 'done' => false],
];

$unfinished = array_filter($tasks, fn ($task) => !$task['done']);
echo count($unfinished), PHP_EOL;
```

```javascript
const tasks = [
  { title: "Read", done: true },
  { title: "Build", done: false },
  { title: "Test", done: false },
];

const unfinished = tasks.filter(task => !task.done);
console.log(unfinished.length);
```

Both print `2`. Run the first with `php example.php` and the second in a browser console or with `node example.js`. One useful difference appears when you inspect the filtered collections. PHP’s `array_filter` keeps the original keys, so these tasks keep keys `1` and `2`. JavaScript’s `filter` returns a compact array with positions `0` and `1`. Call PHP’s `array_values` before encoding the result when your JSON consumer expects a JSON array.

## Build a small application with both

Start with a form that works through a regular POST and redirect. Let PHP validate permissions, persist the change and render the next page. Add JavaScript when a particular interaction benefits from a faster response, such as filtering a table without reloading. Use the same server validation when adding a JSON endpoint for that interface.

- [PHP Basics](/series/php-basics/)
- [JavaScript language guide](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide)
- [PHP array_filter documentation](https://www.php.net/manual/en/function.array-filter.php)
- [PHP vs Node.js for backend work](/compare/php-vs-nodejs/) · [All PHP comparisons](/compare/)
