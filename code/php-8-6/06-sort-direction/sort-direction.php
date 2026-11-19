<?php

// filename: sort-direction.php

declare(strict_types=1);

function orderScores(array $scores, SortDirection $direction = SortDirection::Ascending): array
{
    uasort($scores, static fn (int $left, int $right): int => match ($direction) {
        SortDirection::Ascending => $left <=> $right,
        SortDirection::Descending => $right <=> $left,
    });
    return $scores;
}

$scores = ['Ari' => 42, 'Jo' => 60];
echo implode(', ', array_keys(orderScores($scores))), PHP_EOL;
echo implode(', ', array_keys(orderScores($scores, SortDirection::Descending))), PHP_EOL;
