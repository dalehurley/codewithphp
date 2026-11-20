#!/usr/bin/env bash

set -euo pipefail

vendor/bin/pint --test \
    app/Quotes/SeatPrice.php \
    app/Http/Requests/QuoteRequest.php \
    app/Http/Controllers/QuoteController.php \
    routes/web.php \
    tests/Feature/QuoteTest.php

vendor/bin/phpstan analyse --configuration=phpstan.neon --no-progress
php artisan test --filter=QuoteTest
