<?php

// filename: uri-types.php

declare(strict_types=1);

use Uri\Rfc3986\Uri;
use Uri\WhatWg\Url;

$uri = new Uri('//assets.example/logo.svg');
echo 'URI type: ', $uri->getUriType()->name, PHP_EOL;
echo 'RFC host type: ', $uri->getHostType()->name, PHP_EOL;

$url = new Url('https://192.0.2.4/logo.svg');
echo 'WHATWG host type: ', $url->getHostType()->name, PHP_EOL;
echo 'Special scheme: ', $url->isSpecialScheme() ? 'yes' : 'no', PHP_EOL;
