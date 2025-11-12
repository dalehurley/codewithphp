<?php

declare(strict_types=1);

/**
 * filename: composer.json
 * PHP package dependencies file
 * This file demonstrates PHP package management with Composer
 */

// Example composer.json structure:
/*
{
    "require": {
        "guzzlehttp/guzzle": "^7.5",
        "laravel/framework": "^11.0",
        "spatie/laravel-permission": "^6.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0"
    }
}
*/

// Install packages:
// composer install

// Install single package:
// composer require guzzlehttp/guzzle

// Install with version constraint:
// composer require "guzzlehttp/guzzle:^7.5"

// Update package:
// composer update guzzlehttp/guzzle

// Remove package:
// composer remove guzzlehttp/guzzle

// Show installed packages:
// composer show

// Show package info:
// composer show guzzlehttp/guzzle

// Version constraints:
// Exact: "7.5.0"
// Caret (^7.5.0 means >=7.5.0,<8.0.0): "^7.5.0"
// Tilde (~7.5.0 means >=7.5.0,<7.6.0): "~7.5.0"
// Greater than or equal: ">=7.5.0"
// Less than: "<8.0.0"
// Range: ">=7.5.0,<8.0.0"

