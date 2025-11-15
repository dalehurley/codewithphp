<?php

declare(strict_types=1);

class ArrayUtils
{
    /**
     * Extract a specific key from array of arrays
     *
     * @param array<int, array<string, mixed>> $array
     */
    public static function pluck(array $array, string $key): array
    {
        return array_map(fn($item) => $item[$key] ?? null, $array);
    }

    /**
     * Group array elements by key value
     *
     * @param array<int, array<string, mixed>> $array
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function groupBy(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $item) {
            $groupKey = $item[$key] ?? 'unknown';
            $result[$groupKey][] = $item;
        }
        return $result;
    }

    /**
     * Get unique elements by key
     *
     * @param array<int, array<string, mixed>> $array
     * @return array<int, array<string, mixed>>
     */
    public static function unique(array $array, string $key): array
    {
        $seen = [];
        $result = [];

        foreach ($array as $item) {
            $value = $item[$key] ?? null;
            if (!in_array($value, $seen, true)) {
                $seen[] = $value;
                $result[] = $item;
            }
        }

        return $result;
    }
}

// Test
$users = [
    ['name' => 'Alice', 'role' => 'admin', 'age' => 30],
    ['name' => 'Bob', 'role' => 'user', 'age' => 25],
    ['name' => 'Charlie', 'role' => 'admin', 'age' => 35],
    ['name' => 'David', 'role' => 'user', 'age' => 28]
];

echo "=== Pluck names ===\n";
print_r(ArrayUtils::pluck($users, 'name'));

echo "\n=== Group by role ===\n";
print_r(ArrayUtils::groupBy($users, 'role'));

echo "\n=== Unique by role ===\n";
print_r(ArrayUtils::unique($users, 'role'));
