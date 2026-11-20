---
title: "PHP vs TypeScript"
description: "Compare PHP and TypeScript type checks, browser and server use, and what happens when both read untrusted JSON."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 9
---

TypeScript gives JavaScript projects a static type checker. Its type declarations are removed when the code is converted to JavaScript. PHP runs its own language and checks declared parameter and return types at runtime. In a web app with both, TypeScript can type the browser's UI while PHP implements an API or server-rendered page.

| Concern | PHP | TypeScript |
| --- | --- | --- |
| Type checking | Runtime parameter and return checks, with tools such as PHPStan for additional static analysis. | A compiler checks source code. Interfaces and type annotations normally have no runtime representation. |
| Shapes | Use classes for runtime objects and arrays for decoded JSON or flexible data. | Use interfaces, type aliases and structural typing to describe the shape of JavaScript data. |
| Execution | Run through PHP CLI or a PHP application runtime. | Compile or strip supported type syntax to run as JavaScript in the browser or a server runtime. |
| Deployment | PHP runtime, extensions and any Composer packages. | Build browser assets or deploy the server program with its JavaScript runtime. |

## Check data received from outside the program

A payment count in JSON needs runtime validation in either language. This example accepts the field only if it is an integer.

```php
<?php
declare(strict_types=1);

function readCount(string $json): int
{
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    $count = is_array($data) ? ($data['count'] ?? null) : null;
    if (!is_int($count)) {
        throw new InvalidArgumentException('Count must be an integer');
    }
    return $count;
}

echo readCount('{"count":3}'), PHP_EOL;
```

```typescript
function readCount(json: string): number {
  const data: unknown = JSON.parse(json);
  if (typeof data === "object" && data !== null && "count" in data) {
    const count = data.count;
    if (typeof count === "number" && Number.isInteger(count)) {
      return count;
    }
  }
  throw new Error("Count must be an integer");
}

console.log(readCount('{"count":3}'));
```

Both print `3` and reject `{"count":"3"}`. Compile the TypeScript example with TypeScript 4.9 or newer using `tsc --strict --target es2020 example.ts`, then run `node example.js`. PHP checks the return declaration. TypeScript checks that the return is safe after narrowing `unknown`, and the JavaScript conditions enforce the rules when the program runs.

## Share a contract between the two

Write down JSON field names, nullability and numeric limits before trying to share types. PHP integers and JavaScript numbers have different representations, so decide how to transport large identifiers. Strings are often a straightforward choice for IDs the client never needs to calculate with.

- [PHP for TypeScript developers](/series/php-typescript-developers/)
- [TypeScript handbook on narrowing](https://www.typescriptlang.org/docs/handbook/2/narrowing.html)
- [PHP json_decode](https://www.php.net/manual/en/function.json-decode.php)
- [PHP vs JavaScript](/compare/php-vs-javascript/) · [PHP vs Node.js](/compare/php-vs-nodejs/) · [All PHP comparisons](/compare/)
