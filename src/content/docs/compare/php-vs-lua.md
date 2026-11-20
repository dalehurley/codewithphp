---
title: "PHP vs Lua"
description: "Compare PHP and Lua for web apps and embedded scripting. See how each reads a list of rules and where list indexes differ."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 18
---

PHP is designed for general-purpose scripting and widely used to build websites. Lua is frequently embedded in a host application that exposes its own scripting APIs. A team might write a site in PHP for uploading and reviewing game levels, then use Lua inside the game to express how each level behaves.

| Concern | PHP | Lua |
| --- | --- | --- |
| Entry point | Run a script through the CLI, a web server or an application framework. | Run through an interpreter or inside a host such as a game engine. |
| Collections | Arrays support integer and string keys. A list literal begins at index zero. | Tables can represent records and sequences. A sequence literal begins at index one. |
| Host capabilities | Extensions and runtime configuration determine what a PHP installation exposes. | An embedding application chooses which functions and libraries scripts can call. |
| Types | Dynamic values with optional parameter, return and property declarations. | Runtime types, with no standard static type declarations in Lua 5.4. |

## Print the enabled rules for a level

Both examples print `1: Double points`. The displayed rule number starts at one in both programs. The PHP program adds one to the list position. Lua’s `ipairs` already returns one-based positions when reading a contiguous sequence.

```php
<?php
declare(strict_types=1);

$rules = [
    ['name' => 'Double points', 'enabled' => true],
    ['name' => 'Extra time', 'enabled' => false],
];

foreach ($rules as $index => $rule) {
    if ($rule['enabled']) {
        echo ($index + 1), ': ', $rule['name'], PHP_EOL;
    }
}
```

```lua
local rules = {
    { name = "Double points", enabled = true },
    { name = "Extra time", enabled = false },
}

for index, rule in ipairs(rules) do
    if rule.enabled then
        print(index .. ": " .. rule.name)
    end
end
```

Run with `php example.php` or `lua example.lua`. When adapting the Lua script to an embedded runtime, check the host’s version and available functions.

## Keep the level format portable

Exchange plain rule data and stable rule IDs between the site and game. The game can map rules to the functions it exposes to Lua, while the site validates and displays the same IDs. If you reorder the list later, the ID can stay the same even when the displayed position changes.

- [Lua 5.4 manual](https://www.lua.org/manual/5.4/manual.html)
- [PHP arrays](https://www.php.net/manual/en/language.types.array.php)
- [PHP vs JavaScript](/compare/php-vs-javascript/) · [All PHP comparisons](/compare/)
