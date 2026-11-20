---
title: "PHP vs Rust"
description: "Compare PHP and Rust for web apps, command-line tools, memory and errors. See how two programs find an item that may be missing."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 13
---

PHP is a practical fit for building a web application with established CMS or framework features. Rust is a practical fit when you need a compiled program and want the compiler to check how the code owns and borrows memory. A PHP app can call a separate Rust tool or service when a particular task benefits from it.

| Concern | PHP | Rust |
| --- | --- | --- |
| Execution | Deploy PHP code, extensions and dependencies with a PHP runtime. | Build a binary for the deployment target with Cargo and the Rust compiler. |
| Memory | PHP manages ordinary memory allocation and reclamation at runtime. | The compiler checks ownership and borrowing in safe Rust. |
| Absent result | `null` can represent a missing value in a nullable declaration. | `Option<T>` represents `Some(T)` or `None`. |
| Failure | Individual APIs may throw exceptions or return a failure value. | `Result<T, E>` models a successful value or an error. |

## Find the first unfinished lesson

```php
<?php
declare(strict_types=1);

function firstUnfinished(array $lessons): ?string
{
    foreach ($lessons as $lesson) {
        if (!$lesson['done']) {
            return $lesson['title'];
        }
    }
    return null;
}

echo firstUnfinished([
    ['title' => 'Setup', 'done' => true],
    ['title' => 'Arrays', 'done' => false],
]) ?? 'All done', PHP_EOL;
```

```rust
struct Lesson {
    title: String,
    done: bool,
}

fn first_unfinished(lessons: &[Lesson]) -> Option<&str> {
    lessons.iter().find(|lesson| !lesson.done)
        .map(|lesson| lesson.title.as_str())
}

fn main() {
    let lessons = [
        Lesson { title: "Setup".into(), done: true },
        Lesson { title: "Arrays".into(), done: false },
    ];
    println!("{}", first_unfinished(&lessons).unwrap_or("All done"));
}
```

Both print `Arrays` and would show `All done` if every lesson were finished. Run the PHP version with `php example.php`. Compile `example.rs` with `rustc example.rs`, then run the resulting executable. Rust borrows the title from the input slice. The returned string slice can only be used while the data it refers to is valid.

## Pick a useful boundary

For a site with accounts and content management, start with the web stack you already know. For a high-volume parser or a small tool people need to download, evaluate the compiled Rust option with real files and deployment targets. An isolated command or service often provides a manageable test of the idea before you decide whether to change the rest of the application.

- [Ownership in the Rust Book](https://doc.rust-lang.org/book/ch04-01-what-is-ownership.html)
- [PHP type declarations](https://www.php.net/manual/en/language.types.declarations.php)
- [PHP vs Go](/compare/php-vs-go/) · [PHP vs C++](/compare/php-vs-cpp/) · [All PHP comparisons](/compare/)
