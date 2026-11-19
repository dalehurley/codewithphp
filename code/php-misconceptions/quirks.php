<?php

declare(strict_types=1);

echo '0 == "php": ', json_encode(0 == 'php', JSON_THROW_ON_ERROR), PHP_EOL;
echo '0 == "0": ', json_encode(0 == '0', JSON_THROW_ON_ERROR), PHP_EOL;
echo '0 === "0": ', json_encode(0 === '0', JSON_THROW_ON_ERROR), PHP_EOL;

$position = strpos('laravel', 'lar');
if ($position !== false) {
    echo "Match at offset {$position}", PHP_EOL;
}

$names = ['Ada', 'Grace', 'Linus'];
unset($names[1]);
echo json_encode($names, JSON_THROW_ON_ERROR), PHP_EOL;
echo json_encode(array_values($names), JSON_THROW_ON_ERROR), PHP_EOL;
