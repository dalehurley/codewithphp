---
title: "PHP vs Elixir"
description: "Compare PHP and Elixir for web apps, background work and long-lived connections. See how both label accepted and queued jobs."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 19
---

Elixir is a good language to evaluate for a product that handles many long-lived connections or needs independently managed concurrent processes. PHP suits web applications where most work naturally follows a request or runs in a separate background worker. You can combine them when the product has both a conventional web app and a specialized real-time service.

| Concern | PHP | Elixir |
| --- | --- | --- |
| Execution | Commonly handles short web requests. Long-lived PHP workers are also available. | Runs on the BEAM virtual machine with lightweight processes that communicate by messages. |
| Fault handling | A framework, queue or process manager can retry or restart workers. | OTP supervisors can restart child processes under a defined strategy. |
| Data | Variables can be reassigned and objects can have mutable state. | Values are immutable, with processes able to hold and update their own state over time. |
| Web ecosystem | Frameworks include Laravel and Symfony. | Phoenix provides web tooling including channels for real-time communication. |

## Label jobs by state and retry count

Run with `php example.php` or `elixir example.exs`. Both print `Retrying` and `Accepted`. PHP checks a series of boolean expressions. Elixir dispatches to a matching function clause, with a guard for a positive retry count.

```php
<?php
declare(strict_types=1);

function label(string $state, int $attempts): string
{
    return match (true) {
        $state === 'accepted' => 'Accepted',
        $state === 'queued' && $attempts > 0 => 'Retrying',
        $state === 'queued' => 'Queued',
        default => 'Unknown',
    };
}

echo label('queued', 2), PHP_EOL;
echo label('accepted', 0), PHP_EOL;
```

```elixir
defmodule Jobs do
  def label(:accepted, _attempts), do: "Accepted"
  def label(:queued, attempts) when attempts > 0, do: "Retrying"
  def label(:queued, _attempts), do: "Queued"
  def label(_state, _attempts), do: "Unknown"
end

IO.puts(Jobs.label(:queued, 2))
IO.puts(Jobs.label(:accepted, 0))
```

## Decide who owns a job

For a PHP app that publishes updates through an Elixir service, make one component responsible for the durable job state. Decide when a retry is safe and which job ID subscribers use to recognize repeated messages. Those choices determine what users see after a disconnect or worker restart.

- [Elixir patterns and guards](https://hexdocs.pm/elixir/patterns-and-guards.html)
- [Elixir Supervisor documentation](https://hexdocs.pm/elixir/Supervisor.html)
- [PHP vs Go](/compare/php-vs-go/) · [PHP vs Node.js](/compare/php-vs-nodejs/) · [All PHP comparisons](/compare/)
