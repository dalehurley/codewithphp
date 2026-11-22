---
title: "PHP vs Java"
description: "Compare PHP and Java for backend web development, type checking, database access and deployment. Build the same order lookup with both."
author: "Code with PHP"
datePublished: "2026-11-20"
dateModified: "2026-11-23"
sidebar:
  order: 5
---

Use **PHP** when the work extends a Laravel, Symfony or WordPress application, or when you want to learn by building and deploying one with PHP. Choose **Java** when you are joining a JVM team or your project depends on Spring libraries and services your team already operates. For a new backend with no stack commitment, try one feature against the same data and compare how you validate it, ship it and recover when a dependency fails.

[Run the same order lookup](#build-the-same-order-lookup) · [Try the backend decision exercise](#choose-by-porting-one-feature)

| Decision | PHP | Java |
| --- | --- | --- |
| When does type checking happen? | PHP enforces declarations at runtime. A tool such as PHPStan can flag more issues before execution. | The compiler checks declared types before execution. The program still validates values supplied by users, files and databases. |
| How do I start the same example? | Run `php order-total.php 101` with the CLI. | Compile with `javac OrderTotal.java`, then run `java OrderTotal 101`. |
| How do I ship a web application? | Install application libraries with Composer and serve the app through a configured PHP runtime such as PHP-FPM. | A typical Spring Boot build produces an executable JAR with an embedded web server. Run it on a compatible Java runtime. |
| How do I access a database? | Use PDO or a framework database layer such as Laravel's query builder or Eloquent. | Use JDBC or a framework database layer such as Spring's JDBC support or Spring Data JPA. |

## Types and validation at the data boundary

In PHP, typed parameters and properties let the runtime enforce a contract. `declare(strict_types=1)` makes scalar argument checks stricter for calls made from that PHP file. In Java, the compiler can reject passing a `String` to a parameter declared as `int`. The compiler still accepts a negative integer, and a command-line argument arrives as text in both examples. A parser checks the text and converts it. The model then enforces business rules such as a positive quantity and a non-negative price. [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php) · [PHPStan getting started](https://phpstan.org/user-guide/getting-started) · [Java record classes](https://docs.oracle.com/en/java/javase/17/language/records.html)

A PHP constructor can promote its parameters into properties and make them `readonly`. A Java record generates accessors for its components and lets you add checks in a compact constructor. With the integer fields below, either approach gives you an order whose ID, quantity and price stay fixed after construction. Both models hold the unit price in whole cents. With a ceiling of 1,000 units at 1,000,000 cents each, multiplication stays within the signed 32-bit range. [PHP constructor promotion](https://www.php.net/manual/en/language.oop5.decon.php) · [Java record constructors](https://docs.oracle.com/en/java/javase/17/language/records.html)

## Database access and deployment

For a web version of the same lookup, keep the order and the database code separate. Use a parameterized query by ID through PDO or JDBC, and keep the primary key and field constraints in the schema. A missing row can become a `404`. If the query fails, log the database error for operators and return an appropriate server error to the caller. Where prices span currencies, store the currency and define the units and rounding rules before expanding the model. [PDO prepared statements](https://www.php.net/manual/en/pdo.prepared-statements.php) · [JDBC prepared statements](https://docs.oracle.com/javase/tutorial/jdbc/basics/prepared.html) · [PHP databases for Java developers](/series/php-for-java-developers/chapters/09-working-with-databases/)

For a typical Laravel deployment, use Composer to install the locked dependencies and point the web server at the application's `public` directory. Configure PHP-FPM workers and check how your environment reloads the application and restarts any separate queue workers. Spring Boot commonly uses Maven or Gradle to package the application. For an executable JAR, manage the service as a long-running process and configure how it reports health and handles restarts. Compare how each stack deploys a change and exposes a database failure using the infrastructure you can actually maintain. Measure response times and memory on the same realistic workload if you need to make a capacity decision. [Composer install workflow](https://getcomposer.org/doc/01-basic-usage.md) · [Laravel deployment](https://laravel.com/framework/docs/13.x/deployment) · [PHP-FPM settings](https://www.php.net/manual/en/install.fpm.configuration.php) · [Spring Boot packaging](https://docs.spring.io/spring-boot/reference/packaging/index.html) · [Spring Boot health endpoint](https://docs.spring.io/spring-boot/reference/actuator/endpoints.html)

## Build the same order lookup

Both programs take an order ID, read the same small fixture and print the order's total in cents. You'll test a paid order, a free order, a missing ID and a broken fixture. The file stays read-only during each run.

### Get the files and check your tools

Use PHP 8.1 or later and a JDK 17 or later. The JDK provides both the runtime and `javac`. Save these three files in one directory and run the commands from that directory in your terminal. The example uses built-in APIs and needs no Composer, Maven or Gradle dependencies. [orders.csv](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-java/orders.csv) · [order-total.php](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-java/order-total.php) · [OrderTotal.java](https://raw.githubusercontent.com/dalehurley/codewithphp/main/code/comparisons/php-vs-java/OrderTotal.java)

```sh
php -v
java --version
javac --version
```

If `javac` is missing, install a JDK and make sure its `bin` directory is on your `PATH`. [PHP and Java setup lesson](/series/php-for-java-developers/chapters/00-setup-and-first-comparison/) · [Eclipse Temurin JDK downloads](https://adoptium.net/temurin/releases/)

### Use the same order data

Each line contains an ID, a quantity and a unit price in cents, in that order. Put only the data rows in the file. Use digits with no leading zeros for IDs from 1 to 9,999,999, quantities from 1 to 1,000 and prices from 0 to 1,000,000 cents. Duplicate IDs are data errors. This fixture uses a fixed format with three unquoted numeric fields.

```text title="orders.csv"
101,3,450
102,1,0
103,1000,1000000
```

### Model the order in PHP

The constructor validates the three integer properties. The CLI and file reader convert their text with the same number parser, then the programme looks up the requested ID only after it has validated the fixture.

```php title="order-total.php"
<?php
declare(strict_types=1);

final class Order
{
    public function __construct(
        public readonly int $id,
        public readonly int $quantity,
        public readonly int $unitCents,
    ) {
        if ($id < 1 || $id > 9_999_999 || $quantity < 1 || $quantity > 1000 ||
            $unitCents < 0 || $unitCents > 1_000_000) {
            throw new InvalidArgumentException('Order fields are out of range.');
        }
    }

    public function totalCents(): int
    {
        return $this->quantity * $this->unitCents;
    }
}

function wholeNumber(string $value): int
{
    if (preg_match('/\A(?:0|[1-9][0-9]{0,6})\z/', $value) !== 1) {
        throw new InvalidArgumentException('Expected digits for a whole number.');
    }
    return (int) $value;
}

function fail(int $status, string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($status);
}

try {
    $id = $argc === 2 ? wholeNumber($argv[1]) : 0;
    if ($id === 0) {
        throw new InvalidArgumentException('Expected a positive order ID.');
    }
} catch (InvalidArgumentException) {
    fail(2, 'Enter one order ID from 1 to 9999999.');
}

$fixture = 'orders.csv';
$source = is_file($fixture) ? @file_get_contents($fixture) : false;
if ($source === false) {
    fail(4, 'Cannot read orders.csv. Run from the directory with the fixture.');
}

$orders = [];
$lines = explode("\n", str_replace("\r\n", "\n", $source));
foreach ($lines as $index => $line) {
    if ($line === '') {
        continue;
    }
    try {
        $fields = explode(',', $line);
        if (count($fields) !== 3) {
            throw new InvalidArgumentException('Expected three fields.');
        }
        $order = new Order(...array_map(wholeNumber(...), $fields));
        if (isset($orders[$order->id])) {
            throw new InvalidArgumentException('Duplicate order ID.');
        }
        $orders[$order->id] = $order;
    } catch (InvalidArgumentException $error) {
        $lineNumber = $index + 1;
        fail(4, "Invalid orders.csv on line {$lineNumber}. " . $error->getMessage());
    }
}

$order = $orders[$id] ?? null;
if ($order === null) {
    fail(3, "Order {$id} was not found.");
}

echo "Order {$id}: {$order->totalCents()} cents", PHP_EOL;
```

### Model the same order in Java

The record's compact constructor enforces the same constraints. The reader builds a map with integer keys, and the compiler checks that each record is constructed with three integers. As in PHP, inputs are checked before they become model values.

```java title="OrderTotal.java"
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.HashMap;
import java.util.Map;

public class OrderTotal {
    record Order(int id, int quantity, int unitCents) {
        Order {
            if (id < 1 || id > 9_999_999 || quantity < 1 || quantity > 1000 ||
                unitCents < 0 || unitCents > 1_000_000) {
                throw new IllegalArgumentException("Order fields are out of range.");
            }
        }

        int totalCents() {
            return quantity * unitCents;
        }
    }

    static int wholeNumber(String value) {
        if (!value.matches("0|[1-9][0-9]{0,6}")) {
            throw new IllegalArgumentException("Expected digits for a whole number.");
        }
        return Integer.parseInt(value);
    }

    static int fail(int status, String message) {
        System.err.println(message);
        return status;
    }

    public static void main(String[] args) {
        System.exit(run(args));
    }

    static int run(String[] args) {
        int id;
        try {
            id = args.length == 1 ? wholeNumber(args[0]) : 0;
            if (id == 0) {
                throw new IllegalArgumentException("Expected a positive order ID.");
            }
        } catch (IllegalArgumentException error) {
            return fail(2, "Enter one order ID from 1 to 9999999.");
        }

        String source;
        try {
            source = Files.readString(Path.of("orders.csv"));
        } catch (IOException error) {
            return fail(4, "Cannot read orders.csv. Run from the directory with the fixture.");
        }

        Map<Integer, Order> orders = new HashMap<>();
        String[] lines = source.replace("\r\n", "\n").split("\n", -1);
        for (int index = 0; index < lines.length; index++) {
            if (lines[index].isEmpty()) {
                continue;
            }
            try {
                String[] fields = lines[index].split(",", -1);
                if (fields.length != 3) {
                    throw new IllegalArgumentException("Expected three fields.");
                }
                var order = new Order(wholeNumber(fields[0]), wholeNumber(fields[1]), wholeNumber(fields[2]));
                if (orders.putIfAbsent(order.id(), order) != null) {
                    throw new IllegalArgumentException("Duplicate order ID.");
                }
            } catch (IllegalArgumentException error) {
                int lineNumber = index + 1;
                return fail(4, "Invalid orders.csv on line " + lineNumber + ". " + error.getMessage());
            }
        }

        Order order = orders.get(id);
        if (order == null) {
            return fail(3, "Order " + id + " was not found.");
        }

        System.out.println("Order " + id + ": " + order.totalCents() + " cents");
        return 0;
    }
}
```

### Run both versions

Compile the Java source before launching it. Rerun `javac` after a Java source change. Updating `orders.csv` takes effect on the next run of either programme. [Java compiler](https://docs.oracle.com/en/java/javase/17/docs/specs/man/javac.html)

```sh
php order-total.php 101
javac OrderTotal.java
java OrderTotal 101
```

Both lookup commands print `Order 101: 1350 cents`. Try the remaining IDs by passing them to each of those lookup commands.

| Input or fixture change | Result in both versions | Exit status |
| --- | --- | --- |
| ID `102` | `Order 102: 0 cents`. The free order exists. | `0` |
| ID `103` | `Order 103: 1000000000 cents`. The example accepts the upper price and quantity limits. | `0` |
| ID `999` | `Order 999 was not found.` | `3` |
| ID `1.5` | `Enter one order ID from 1 to 9999999.` | `2` |
| Fixture missing | `Cannot read orders.csv. Run from the directory with the fixture.` | `4` |
| Change the second row to `102,0,0` | `Invalid orders.csv on line 2. Order fields are out of range.` | `4` |

An unknown ID and a free order are different outcomes. The first goes to the error stream and the second goes to standard output. For the fixture checks, run each lookup with ID `101`, first with the file temporarily renamed, then with the invalid second row. Check the exit status immediately after each command with `echo $?` in a POSIX shell or `$LASTEXITCODE` in PowerShell. Restore the original fixture and confirm both lookups succeed again.

## Choose by porting one feature

Take an order lookup from the application you intend to build. Keep the same input rules, expected totals and failure cases. Add a database and a web route in Laravel and Spring Boot, or in the frameworks your team already uses. Compare what it takes to write a test, deploy a change and diagnose a missing row or database outage. If you are joining a team with an existing stack, start with the version you will maintain and use the second implementation to map familiar concepts. [PHP for Java developers](/series/php-for-java-developers/) · [Test a PHP app with PHPUnit](/series/php-for-java-developers/chapters/12-unit-testing-with-phpunit/) · [PHP vs Kotlin](/compare/php-vs-kotlin/) · [All PHP comparisons](/compare/)
