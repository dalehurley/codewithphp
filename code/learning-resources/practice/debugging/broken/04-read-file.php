<?php

$title = file_get_contents('../data/reading-list.txt');

echo 'First pick: ', trim((string) $title), PHP_EOL;
