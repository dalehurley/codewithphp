---
title: "PHP vs Java"
description: "Compare PHP and Java for backend web development, types, tooling and deployment. See the same order calculation written in both."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 5
---

For a new backend, compare the full web stack you would maintain. PHP is a direct fit for projects already built on WordPress, Laravel or Symfony. Java fits teams that already operate JVM services and use libraries such as Spring Boot. Both support typed models and automated tests, with different build and runtime workflows.

| Concern | PHP | Java |
| --- | --- | --- |
| First executable | Run `php order.php`. | A current JDK can launch a single source file with `java Main.java`. |
| Types | Declarations and runtime checks work alongside optional static analysis. | The compiler checks declared types before the JVM executes the program. |
| Packaging | Composer installs PHP libraries for an application or reusable package. | Maven and Gradle can resolve dependencies and build JARs. |
| Web deployment | A common setup uses a web server and PHP-FPM. | A web application can run in a persistent JVM process with an embedded server. |

## Model the total for one order

Both examples calculate the price for three items at 450 cents each. Each models one order and puts the calculation next to its data.

```php
<?php
declare(strict_types=1);

final class Order
{
    public function __construct(
        public readonly int $quantity,
        public readonly int $unitPrice,
    ) {}

    public function total(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}

echo (new Order(3, 450))->total(), PHP_EOL;
```

```java
public class Main {
    public static void main(String[] args) {
        System.out.println(new Order(3, 450).total());
    }
}

record Order(int quantity, int unitPrice) {
    int total() {
        return quantity * unitPrice;
    }
}
```

Both print `1350`. Use PHP 8.1 or newer and run `php example.php`. With JDK 17 or newer, save the Java version as `Main.java` and run `java Main.java`. Java’s record declares its state in the record header. PHP’s constructor promotes parameters into properties and marks each one readonly. For real order validation, check business rules such as a positive quantity at the application boundary.

## Transitioning from Java to PHP

Start by mapping familiar concepts to PHP’s setup. Compare autoloading with Java package conventions, then build the same small web endpoint using PDO where you know JDBC. Use the framework’s request and validation model to guide the transition, especially when porting an application built around long-lived services.

- [PHP for Java developers](/series/php-for-java-developers/)
- [Java language features](https://dev.java/learn/language-basics/)
- [PHP constructor property promotion](https://www.php.net/manual/en/language.oop5.decon.php)
- [PHP vs Kotlin](/compare/php-vs-kotlin/) · [All PHP comparisons](/compare/)
