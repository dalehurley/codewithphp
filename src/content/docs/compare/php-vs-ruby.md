---
title: "PHP vs Ruby"
description: "Compare PHP and Ruby for scripts and web development, with a collection example and a guide to choosing between Laravel and Rails."
author: "Code with PHP"
datePublished: "2026-11-20"
sidebar:
  order: 11
---

PHP and Ruby can both build database-backed sites. Laravel and Rails provide conventions for routing, models, migrations and tests in their respective ecosystems. Ruby’s blocks and collection methods give it a distinctive style, while PHP often expresses similar work with a loop or a callback.

| Concern | PHP | Ruby |
| --- | --- | --- |
| Web frameworks | Laravel and Symfony are common choices. | Rails is a common integrated choice. |
| Collections | Arrays act as ordered maps and lists. | Arrays and hashes are separate types. |
| Dependency management | Composer installs PHP packages and generates an autoloader. | RubyGems distributes gems and Bundler manages groups of gems and their versions. |
| Typing | PHP checks parameter and return declarations at runtime when provided. | Idiomatic Ruby is dynamically typed. Tools such as RBS can add type signatures for tooling. |

## Group the unread messages

```php
<?php
$messages = [
    ['folder' => 'inbox', 'read' => false],
    ['folder' => 'inbox', 'read' => true],
    ['folder' => 'inbox', 'read' => false],
    ['folder' => 'updates', 'read' => false],
];

$counts = [];
foreach ($messages as $message) {
    if (!$message['read']) {
        $folder = $message['folder'];
        $counts[$folder] = ($counts[$folder] ?? 0) + 1;
    }
}
echo $counts['inbox'], PHP_EOL;
```

```ruby
messages = [
  { folder: "inbox", read: false },
  { folder: "inbox", read: true },
  { folder: "inbox", read: false },
  { folder: "updates", read: false },
]

counts = messages.reject { |message| message.fetch(:read) }
                 .each_with_object(Hash.new(0)) do |message, result|
  result[message.fetch(:folder)] += 1
end

puts counts.fetch("inbox")
```

Both print `2`. Run the first file with `php example.php` and the second with `ruby example.rb`. Ruby’s first block filters the messages and the second accumulates counts. PHP’s loop filters and counts in one pass. Use small fixtures like this to check behaviour if you move a script between languages.

## Laravel or Rails for a new project

Compare the existing skills of the people looking after the application and the libraries you need. For an existing Rails app, keeping its conventions and tests can be valuable. For a PHP site that needs a new web feature, starting with the PHP stack can let you reuse deployment, authentication and data access.

- [Laravel for Ruby on Rails developers](/series/rails-developers-love-laravel/)
- [Ruby language quickstart](https://www.ruby-lang.org/en/documentation/quickstart/)
- [Rails getting started](https://guides.rubyonrails.org/getting_started.html)
- [PHP vs Python](/compare/php-vs-python/) · [All PHP comparisons](/compare/)
