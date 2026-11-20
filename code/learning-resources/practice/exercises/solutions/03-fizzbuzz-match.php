<?php

declare(strict_types=1);

for ($number = 1; $number <= 100; $number++) {
    $label = match (true) {
        $number % 15 === 0 => 'FizzBuzz',
        $number % 3 === 0 => 'Fizz',
        $number % 5 === 0 => 'Buzz',
        default => (string) $number,
    };

    echo $label, PHP_EOL;
}
