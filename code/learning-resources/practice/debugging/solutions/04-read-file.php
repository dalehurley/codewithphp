<?php

$title = file_get_contents(__DIR__ . '/../data/reading-list.txt');
if ($title === false) {
    exit("Could not read the reading list.\n");
}

echo 'First pick: ', trim($title), PHP_EOL;
