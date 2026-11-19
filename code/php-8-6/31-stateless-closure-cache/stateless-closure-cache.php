<?php

// filename: stateless-closure-cache.php

declare(strict_types=1);

function incrementer(): Closure
{
    return static fn (int $value): int => $value + 1;
}

function stepper(int $step): Closure
{
    return static fn (int $value): int => $value + $step;
}

$first = incrementer();
$second = incrementer();
echo 'Stateless reused: ', $first === $second ? 'yes' : 'no', PHP_EOL;
echo 'Captured reused: ', stepper(2) === stepper(2) ? 'yes' : 'no', PHP_EOL;
echo 'Stepped value: ', stepper(3)(4), PHP_EOL;
