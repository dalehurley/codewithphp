<?php

declare(strict_types=1);

$query = $argv[1] ?? 'lar';
$enabledOnly = match ($argv[2] ?? '1') {
    '1' => true,
    '0' => false,
    default => null,
};

if ($argc > 3 || $query === '' || $enabledOnly === null) {
    fwrite(STDERR, 'Usage: php catalog-filter.php [non-empty query] [1 for enabled only or 0 for all]' . PHP_EOL);
    exit(1);
}

$catalog = [
    ['code' => '08', 'name' => 'laravel starter', 'enabled' => true],
    ['code' => '8', 'name' => 'laravel archive', 'enabled' => false],
    ['code' => '0', 'name' => 'learn laravel', 'enabled' => true],
];

$matches = array_filter(
    $catalog,
    static fn (array $entry): bool => (!$enabledOnly || $entry['enabled'])
        && strpos($entry['name'], $query) !== false,
);

echo json_encode(array_values($matches), JSON_THROW_ON_ERROR), PHP_EOL;
