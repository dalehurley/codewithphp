---
title: "Learn PHP Through WordPress"
description: "Make a small WordPress shortcode plugin to practise PHP functions, arrays, namespaces, hooks, and escaping in a familiar setting."
difficulty: "Beginner"
author: "Code with PHP"
datePublished: "2026-11-20"
prev: false
next: false
---

Build a one-file WordPress plugin that turns `[cwp_greeting name="Avery"]` into **Hello, Avery!**. You will use a function, an array of defaults, a namespace, and a hook to connect them to WordPress.

## Make a shortcode plugin

In a local or disposable test installation, create `wp-content/plugins/cwp-greeting/cwp-greeting.php` and paste in this code.

```php
<?php
/**
 * Plugin Name: Code with PHP Greeting
 */

namespace CodeWithPhp\Greeting;

function render_greeting(array $attributes): string
{
    $defaultName = 'reader';
    $options = \shortcode_atts(['name' => $defaultName], $attributes);
    $name = trim($options['name']);

    return 'Hello, ' . \esc_html($name === '' ? $defaultName : $name) . '!';
}

\add_action('init', static function (): void {
    \add_shortcode('cwp_greeting', __NAMESPACE__ . '\\render_greeting');
});
```

If you need a test installation and already have Node.js, save the same file in a standalone `cwp-greeting` folder and run the free Playground CLI from inside that folder. It opens a local WordPress site and mounts your plugin. [WordPress Playground CLI](https://developer.wordpress.org/playground/developers/local-development/wp-playground-cli/)

```sh
npx @wp-playground/cli@latest start
```

On the test site's Plugins screen, activate **Code with PHP Greeting** if it is inactive. Add a Shortcode block to a draft page, paste `[cwp_greeting name="Avery"]`, and preview the page. Add a second block with `[cwp_greeting]` and check that it renders **Hello, reader!**. Try `[cwp_greeting name="A & B"]` to see text that contains an ampersand render as intended. [WordPress plugin basics](https://developer.wordpress.org/plugins/plugin-basics/) · [Shortcode callbacks](https://developer.wordpress.org/reference/functions/add_shortcode/)

## Follow what WordPress calls

- **Action hook.** `add_action('init', ...)` registers a callback that WordPress runs while it sets up the request. Your callback registers the shortcode handler. A theme or plugin can also use a filter hook to receive, change, and return a value. [WordPress hooks](https://developer.wordpress.org/plugins/hooks/)
- **Function.** WordPress calls `render_greeting()` when it renders the shortcode. The function returns a string that replaces the shortcode. Try returning a different greeting and reload the preview. [PHP functions](/series/php-basics/chapters/04-understanding-and-using-functions/)
- **Associative array.** `['name' => $defaultName]` gives the option a key and a default. Change `$defaultName` to `'friend'` to see the default in action. [PHP arrays](/series/php-basics/chapters/06-deep-dive-into-arrays/) · [WordPress shortcode defaults](https://developer.wordpress.org/reference/functions/shortcode_atts/)
- **Namespace.** `CodeWithPhp\Greeting` keeps the function's full name distinct from any other plugin's `render_greeting()`. `__NAMESPACE__` builds the name WordPress calls. A leading backslash, such as in `\esc_html()`, refers to a global function. [PHP namespaces](/series/php-basics/chapters/10-oop-traits-and-namespaces/)
- **Escaping.** The shortcode inserts the name into HTML text. `esc_html()` keeps the name in that context. In a theme template, you can use the same function before printing a string inside a heading or paragraph. [WordPress HTML escaping](https://developer.wordpress.org/reference/functions/esc_html/)

For a next exercise, use the built-in `the_content` filter as a reading example. Look at how WordPress passes the content string to each callback and uses its return value. [WordPress content filter](https://developer.wordpress.org/reference/hooks/the_content/)
