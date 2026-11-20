<?php

$book = ['title' => 'Moon Atlas'];
$subtitle = $book['subtitle'] ?? 'no subtitle';

printf("%s (%s)\n", $book['title'], $subtitle);
