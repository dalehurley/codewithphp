<?php

// filename: metaphone-migration.php

declare(strict_types=1);

function groupNames(array $names, callable $encode): array
{
    $groups = [];
    foreach ($names as $name) {
        $groups[$encode($name)][] = $name;
    }
    return $groups;
}

$names = ['Smith', 'Smyth', 'Jones'];
echo json_encode(groupNames($names, soundex(...)), JSON_THROW_ON_ERROR), PHP_EOL;
