<?php

declare(strict_types=1);

/** @param list<int> $quantities */
function countQuantities(array $quantities): int
{
    return count($quantities);
}

echo countQuantities(['3']), PHP_EOL;
