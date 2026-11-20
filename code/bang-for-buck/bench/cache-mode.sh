#!/usr/bin/env bash

set -euo pipefail
cd "$(dirname "$0")/.."

mode=${1:-baseline}
opcache=1
case "$mode" in
    baseline) task='' ;;
    config) task='config:cache' ;;
    routes) task='route:cache' ;;
    events) task='event:cache' ;;
    views) task='view:cache' ;;
    all) task='optimize' ;;
    all-no-opcache)
        task='optimize'
        opcache=0
        ;;
    *)
        printf 'Usage: bash bench/cache-mode.sh {baseline|config|routes|events|views|all|all-no-opcache}\n' >&2
        exit 2
        ;;
esac

# A fresh PHP container starts with no generated Laravel files. This keeps
# the previous experiment's cache artifacts out of the next one.
SHOP_OPCACHE="$opcache" docker compose up -d --wait --force-recreate --no-deps php web
if [[ -n "$task" ]]; then
    docker compose exec -T --user www-data php php artisan "$task"
fi

docker compose exec -T php php artisan about --only=cache
printf 'Requested FPM OPcache setting: %s\n' "$opcache"
