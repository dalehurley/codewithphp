---
title: "PHP vs Kotlin"
description: "Compare Kotlin and PHP for Android, backend APIs, null safety and deployment. Try the same optional profile lookup in both."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 16
---

Kotlin is a natural choice when the project includes a native Android app or needs to work with JVM libraries. PHP can serve the app’s API or build a separate web product with an established PHP framework. Kotlin is also a backend option on the JVM, so the decision for an API often comes down to which ecosystem the team needs to integrate with and maintain.

| Concern | PHP | Kotlin |
| --- | --- | --- |
| Common target | Web applications and server-side jobs. | Android apps and JVM backends, among other targets. |
| Nullable values | A type such as `?Profile` permits a profile or `null`. | A type such as `Profile?` permits a profile or `null`. |
| Default value | `??` supplies a fallback for `null`. | The Elvis operator `?:` supplies a fallback for `null`. |
| Deployment | Deploy PHP code and dependencies with a compatible runtime. | Package a JVM server app or build an Android app with the appropriate SDK. |

## Display a name when a profile exists

The PHP example uses the nullsafe operator introduced in PHP 8.0 and can run with `php example.php`. Compile the Kotlin version with `kotlinc Example.kt -include-runtime -d example.jar` and run `java -jar example.jar`.

```php
<?php
declare(strict_types=1);

final class Profile
{
    public function __construct(public ?string $name) {}
}

function displayName(?Profile $profile): string
{
    return $profile?->name ?? 'Guest';
}

echo displayName(new Profile('Mika')), PHP_EOL;
echo displayName(null), PHP_EOL;
```

```kotlin
data class Profile(val name: String?)

fun displayName(profile: Profile?): String = profile?.name ?: "Guest"

fun main() {
    println(displayName(Profile("Mika")))
    println(displayName(null))
}
```

Both print `Mika` and `Guest`. They also return `Guest` when a profile exists with a null name. In Kotlin the compiler checks that nullable references are handled. PHP’s nullsafe access and nullable type declarations express the same behavior in this example.

## Share a profile contract across an app and an API

Decide whether a missing profile, a profile with a null name and an empty name mean different things. Write those cases into the contract and test how the Android client renders each of them. Turning every case into `Guest` at the API would make it harder for the app to offer the right next step.

- [Kotlin null safety](https://kotlinlang.org/docs/null-safety.html)
- [PHP nullsafe property access](https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.nullsafe)
- [PHP vs Java](/compare/php-vs-java/) · [PHP vs Swift](/compare/php-vs-swift/) · [All PHP comparisons](/compare/)
