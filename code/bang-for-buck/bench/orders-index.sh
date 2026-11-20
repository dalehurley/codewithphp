#!/usr/bin/env bash

set -euo pipefail
cd "$(dirname "$0")/.."

case "${1:-}" in
    on)
        ddl='CREATE INDEX IF NOT EXISTS orders_product_recent_idx
             ON orders (product_id, created_at DESC, id DESC)'
        ;;
    off)
        ddl='DROP INDEX IF EXISTS orders_product_recent_idx'
        ;;
    *)
        printf 'Usage: bash bench/orders-index.sh {on|off}\n' >&2
        exit 2
        ;;
esac

docker compose exec -T db psql -X --set=ON_ERROR_STOP=1 -U shop -d shop \
    -c "$ddl" \
    -c "SELECT COALESCE(pg_size_pretty(pg_relation_size(to_regclass('public.orders_product_recent_idx'))), 'absent') AS experiment_index;"
