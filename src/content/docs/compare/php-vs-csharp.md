---
title: "PHP vs C#"
description: "Compare PHP and C# for web APIs, typing, NuGet and Composer. See how a small PHP class maps to a C# record."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 10
---

For a web API, compare the PHP framework you plan to use with ASP.NET Core on .NET. PHP is a natural extension of an existing Laravel or WordPress site. C# fits a .NET codebase that already shares libraries or infrastructure across services. C# is the language, .NET is its common runtime and libraries, and ASP.NET Core is a web framework.

| Concern | PHP | C# |
| --- | --- | --- |
| Compiler and checks | PHP validates declarations at runtime. Static analysers can check code ahead of deployment. | The C# compiler checks the program and produces assemblies for .NET. |
| Data models | Classes and readonly properties are common for data passed between parts of an application. | Records are an option for data models and provide value equality by default. |
| Dependency tool | Composer. | NuGet, often used through the `dotnet` CLI. |
| Background work | Run queue workers or other CLI processes. | .NET applications can host background services or run dedicated worker processes. |

## Calculate the remaining stock

```php
<?php
declare(strict_types=1);

final class Stock
{
    public function __construct(
        public readonly int $received,
        public readonly int $reserved,
    ) {}

    public function available(): int
    {
        return $this->received - $this->reserved;
    }
}

echo (new Stock(12, 5))->available(), PHP_EOL;
```

```csharp
using System;

Console.WriteLine(new Stock(12, 5).Available);

record Stock(int Received, int Reserved)
{
    public int Available => Received - Reserved;
}
```

Both print `7`. The PHP example needs PHP 8.1 or newer. Paste the C# example into the `Program.cs` of a console project created with `dotnet new console` and run `dotnet run`. The record gives C# a concise way to define the two values. The PHP class makes its constructor and calculation explicit.

## Compare the web application around the example

Look beyond the language before moving a web project. Check how each framework handles request validation, database migrations and production logs. Plain PHP applications commonly use PDO or a framework ORM. A .NET application can use ADO.NET or Entity Framework Core. Verify that your database provider, hosting and existing authentication setup fit the framework you choose.

- [C# records](https://learn.microsoft.com/en-us/dotnet/csharp/fundamentals/types/records)
- [ASP.NET Core fundamentals](https://learn.microsoft.com/en-us/aspnet/core/fundamentals/)
- [PHP objects and classes](/series/php-basics/chapters/08-introduction-to-object-oriented-programming/)
- [PHP vs Java](/compare/php-vs-java/) · [All PHP comparisons](/compare/)
