<?php

// filename: uri-builders.php

declare(strict_types=1);

use Uri\Rfc3986\Uri;
use Uri\Rfc3986\UriBuilder;
use Uri\WhatWg\UrlBuilder;

$query = http_build_query(['city' => 'New Town'], encoding_type: PHP_QUERY_RFC3986);
$url = new UrlBuilder()
    ->setScheme('https')
    ->setHost('Travel.Example')
    ->setPort(443)
    ->setPath('/search')
    ->setQuery($query)
    ->build();

$reference = new UriBuilder()
    ->setPath('guides/tasmania')
    ->build(new Uri('https://travel.example/catalog/'));

echo $url->toAsciiString(), PHP_EOL;
echo $reference->toString(), PHP_EOL;
