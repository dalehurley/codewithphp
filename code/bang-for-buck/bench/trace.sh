#!/usr/bin/env bash

set -euo pipefail

route=${1:-order}
variant=${2:-lazy}
port=${SHOP_PORT:-8088}
url="http://127.0.0.1:$port"
set -- --header 'Accept: application/json' --header 'X-Shop-Trace: 1'

case "$route" in
    order)
        # A new reference forces checkout to ask for a shipping quote.
        run_id="profile-$(date -u +%Y%m%dT%H%M%SZ)-$$"
        set -- "$@" --header 'Content-Type: application/json' \
            --data "{\"run_id\":\"$run_id\",\"reference\":\"order\",\"product_id\":1,\"quantity\":2}"
        url="$url/api/orders"
        ;;
    catalogue)
        case "$variant" in
            lazy|eager|projected) ;;
            *) printf 'Use a catalogue trace variant of lazy, eager or projected.\n' >&2; exit 2 ;;
        esac
        url="$url/api/catalogue?category=1&page=1&variant=$variant"
        ;;
    *)
        printf 'Usage: bash bench/trace.sh order | catalogue [lazy|eager|projected]\n' >&2
        exit 2
        ;;
esac

if [[ -n "${XDEBUG_TRIGGER:-}" ]]; then
    set -- "$@" --cookie "XDEBUG_TRIGGER=$XDEBUG_TRIGGER"
fi

curl --silent --show-error --fail-with-body --max-time 5 --include "$@" \
    --write-out '\nClient time to first byte: %{time_starttransfer}s\nClient total: %{time_total}s\n' "$url"
