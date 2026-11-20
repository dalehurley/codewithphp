---
title: "PHP vs Dart"
description: "Compare Dart and PHP for Flutter apps, server APIs and asynchronous work. Try the same lesson-label example in both languages."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 20
---

Choose Dart when you want to build a Flutter client. Choose PHP when you need a server-side application that handles accounts, business rules or data storage and fits the PHP ecosystem. A Flutter client can call a PHP API and render its responses using Dart types, so many products can use both.

| Concern | PHP | Dart |
| --- | --- | --- |
| Typical responsibilities | Web requests, server-rendered pages, APIs and background work. | Flutter user interfaces, as well as standalone programs and server code. |
| Nullable values | `?string` permits either a string or `null`. | `String?` permits either a string or `null`. |
| Asynchronous work | Ordinary PHP application code is often synchronous. Async tools and runtimes are available. | `Future<T>` represents a result that completes later and can be awaited. |
| Deployment | Publish PHP code, dependencies and runtime configuration to the server. | Build the app or executable for its supported targets. |

## Show a lesson title

Both examples print `Start with arrays`. PHP calls a synchronous function. Dart awaits a function whose contract returns `Future<String?>`, a common shape for a later result that can be missing. This tiny example uses a fixed value to make it runnable without a network service.

```php
<?php
declare(strict_types=1);

function loadLessonTitle(): ?string
{
    return 'Start with arrays';
}

echo loadLessonTitle() ?? 'No lesson available', PHP_EOL;
```

```dart
Future<String?> loadLessonTitle() async {
  return 'Start with arrays';
}

Future<void> main() async {
  final title = await loadLessonTitle();
  print(title ?? 'No lesson available');
}
```

Run the examples using `php example.php` and `dart run example.dart`. In a Flutter app, an API client can fill in the Dart function. The UI can then show a loading state while it waits and an error state if the request fails.

## Put authorization on the server

Both a web page and a Flutter app might display a lesson only to the user who owns it. Have the PHP API make that authorization decision. Define which response means “no lesson is available” and which means “you need to sign in.” That gives the client enough information to show the appropriate message and action.

- [Dart asynchronous programming](https://dart.dev/libraries/async/async-await)
- [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php)
- [PHP vs Kotlin](/compare/php-vs-kotlin/) · [PHP vs Swift](/compare/php-vs-swift/) · [All PHP comparisons](/compare/)
