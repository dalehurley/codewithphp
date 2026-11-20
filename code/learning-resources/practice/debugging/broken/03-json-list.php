<?php

$tags = ['draft', 'php'];
$published = array_filter($tags, static fn (string $tag): bool => $tag !== 'draft');

echo json_encode($published, JSON_THROW_ON_ERROR), PHP_EOL;
