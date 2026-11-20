#!/usr/bin/env bash

set -euo pipefail
cd "$(dirname "$0")/.."

profile=${1:-steady}
workload=${WORKLOAD:-mixed}
catalogue_variant=${CATALOGUE_VARIANT:-projected}
catalogue_page=${CATALOGUE_PAGE:-1}
cache_mode=${CACHE_MODE:-locked}
cache_traffic=${CACHE_TRAFFIC:-warm}
export_mode=${EXPORT_MODE:-queued}
rate=${RATE:-20}
seconds=${DURATION_SECONDS:-30}
vus=${VUS:-4}
max_vus=${MAX_VUS:-80}
port=${SHOP_PORT:-8088}

case "$profile" in
    smoke|steady|burst|closed|cold) ;;
    *)
        printf 'Usage: bash bench/run.sh {smoke|steady|burst|closed|cold}\n' >&2
        exit 2
        ;;
esac

case "$workload:$profile" in
    mixed:*|preview:cold|preview:steady|catalogue:steady|orders:steady|cache:steady|exports:steady) ;;
    *)
        printf 'Use WORKLOAD=mixed for normal profiles, preview with cold or steady, or catalogue, cache, orders or exports with steady.\n' >&2
        exit 2
        ;;
esac

if [[ "$workload" == catalogue ]]; then
    case "$catalogue_variant" in
        lazy|eager|projected|cursor) ;;
        *) printf 'Use CATALOGUE_VARIANT=lazy, eager, projected or cursor.\n' >&2; exit 2 ;;
    esac
    if ! [[ "$catalogue_page" =~ ^[1-9][0-9]{0,3}$ ]] || (( catalogue_page > 1000 )); then
        printf 'Use a CATALOGUE_PAGE between 1 and 1000.\n' >&2
        exit 2
    fi
fi

if [[ "$workload" == cache ]]; then
    case "$cache_mode:$cache_traffic" in
        bypass:warm|direct:warm|locked:warm|direct:expiry|locked:expiry) ;;
        *) printf 'Use CACHE_MODE=bypass, direct or locked and CACHE_TRAFFIC=warm or expiry. Expiry needs direct or locked.\n' >&2; exit 2 ;;
    esac
fi

for number in "$rate" "$seconds" "$vus" "$max_vus" "$port"; do
    if ! [[ "$number" =~ ^[1-9][0-9]{0,4}$ ]]; then
        printf 'Use positive whole numbers for RATE, DURATION_SECONDS, VUS, MAX_VUS and SHOP_PORT.\n' >&2
        exit 2
    fi
done
if (( max_vus < 16 || rate > 2000 || seconds > 3600 || vus > max_vus || port > 65535 )); then
    printf 'Use RATE <= 2000, DURATION_SECONDS <= 3600, 16 <= MAX_VUS and VUS <= MAX_VUS.\n' >&2
    exit 2
fi
if [[ "$workload:$profile" == mixed:steady ]] && (( rate * seconds < 20 )); then
    printf 'A mixed workload needs at least 20 planned iterations to exercise all three workflows.\n' >&2
    exit 2
fi
if [[ "$workload" == orders ]] && (( rate * seconds < 5 )); then
    printf 'The orders mix needs at least five planned iterations to exercise reads and writes.\n' >&2
    exit 2
fi
if [[ "$workload" == exports ]]; then
    case "$export_mode" in
        sync|queued) ;;
        *) printf 'Use EXPORT_MODE=sync or queued.\n' >&2; exit 2 ;;
    esac
    if (( rate * seconds < 10 )); then
        printf 'The export mix needs at least ten planned iterations to exercise all three workflows.\n' >&2
        exit 2
    fi
fi

container_ids=()
while IFS= read -r id; do
    container_ids+=("$id")
done < <(docker compose ps -q php web db worker shipping)
if [[ ${#container_ids[@]} -ne 5 ]]; then
    printf 'Start all five shop services before running the benchmark.\n' >&2
    exit 1
fi
if [[ "$workload" == cache ]]; then
    redis_id=$(docker compose ps -q redis)
    if [[ -n "$redis_id" ]]; then
        container_ids+=("$redis_id")
    fi
fi

mkdir -p results
directory=$(mktemp -d "$PWD/results/${profile}-$(date -u +%Y%m%dT%H%M%SZ)-XXXXXX")
run_id=${directory##*/}
printf 'Results will be written to %s\n' "$directory"

{
    printf 'run=%s\nprofile=%s\nworkload=%s\n' "$run_id" "$profile" "$workload"
    if [[ "$workload" == catalogue ]]; then
        printf 'catalogue_variant=%s\ncatalogue_page=%s\n' "$catalogue_variant" "$catalogue_page"
    fi
    if [[ "$workload" == cache ]]; then
        printf 'cache_mode=%s\ncache_traffic=%s\n' "$cache_mode" "$cache_traffic"
    fi
    if [[ "$workload" == exports ]]; then
        printf 'export_mode=%s\nexport_category=21\nexport_poll_interval_ms=250\n' "$export_mode"
    fi
    if [[ "$workload:$cache_traffic" == cache:expiry ]]; then
        printf 'one_request_per_user=%s\n' "$vus"
    else
        case "$profile" in
            smoke) printf 'offered_workflows_per_second=5\nduration_seconds=4\n' ;;
            steady) printf 'offered_workflows_per_second=%s\nduration_seconds=%s\n' "$rate" "$seconds" ;;
            burst) printf 'baseline_workflows_per_second=%s\npeak_workflows_per_second=%s\nduration_seconds=60\n' "$rate" "$((rate * 3))" ;;
            closed) printf 'virtual_users=%s\nduration_seconds=%s\n' "$vus" "$seconds" ;;
            cold) printf 'catalogue_samples=2\n' ;;
        esac
    fi
    printf 'open_model_max_vus=%s\n' "$max_vus"
    printf '\nHost\n'
    uname -srm
    docker info --format 'Engine OS={{.OperatingSystem}} CPUs={{.NCPU}} MemoryBytes={{.MemTotal}}'
    docker compose version
    printf '\nContainers\n'
    docker compose ps
    docker compose images
    printf '\nImage IDs and limits\n'
    docker inspect --format '{{.Name}} {{.Image}} NanoCPUs={{.HostConfig.NanoCpus}} MemoryBytes={{.HostConfig.Memory}}' "${container_ids[@]}"
    printf '\nShipping delay override in milliseconds\n'
    docker compose exec -T shipping sh -c 'printf "%s\n" "${SHOP_SHIPPING_EXTRA_MS:-0}"'
    printf '\nDataset and HTTP runtime\n'
    if [[ "$profile" == cold ]]; then
        printf 'See runtime.json after the cold sample.\n'
    else
        curl -fsS --max-time 5 "http://127.0.0.1:$port/api/lab"
        printf '\n'
    fi
} > "$directory/environment.txt"

if [[ "$workload" == preview ]]; then
    # Artisan runs with CLI OPcache off and does not render the view.
    docker compose exec -T --user www-data php php artisan about --only=cache --json \
        > "$directory/laravel-caches-before.json"
fi
if [[ "$workload" == orders ]]; then
    docker compose exec -T db psql -X -qAt --set=ON_ERROR_STOP=1 -U shop -d shop -c "
        SELECT json_build_object(
            'historical_orders', count(*),
            'index_bytes', COALESCE(pg_relation_size(to_regclass('public.orders_product_recent_idx')), 0)
        )
        FROM orders WHERE run_id = 'orders-history-fixture';
    " > "$directory/order-index.json"
fi

if [[ "$workload" == cache ]]; then
    summary_url="http://127.0.0.1:$port/api/catalogue-summary?category=21"
    curl -fsS --max-time 5 "$summary_url&cache=bypass" > /dev/null
    if [[ "$cache_mode" != bypass ]]; then
        curl -fsS --max-time 5 "$summary_url&cache=$cache_mode" > /dev/null
    fi
    if [[ "$cache_traffic" == expiry ]]; then
        docker compose exec -T php php artisan shop:catalogue-expire 21
        sleep 2
    fi
    if [[ -n "$redis_id" ]]; then
        docker compose exec -T redis redis-cli --raw INFO server memory stats > "$directory/redis-before.txt"
    fi
fi

fpm_ready() {
    docker compose exec -T php php -r 'exit(@stream_socket_client("tcp://127.0.0.1:9000", $errno, $message, 0.3) ? 0 : 1);'
}

if [[ "$profile" == cold ]]; then
    # Open the socket to check readiness without sending a PHP request.
    docker compose restart php
    for attempt in {1..20}; do
        if fpm_ready; then break; fi
        sleep 1
    done
    if ! fpm_ready; then
        printf 'PHP-FPM did not start listening after the restart.\n' >&2
        exit 1
    fi
fi

sample_resources() {
    while true; do
        stamp=$(date -u +%Y-%m-%dT%H:%M:%SZ)
        curl -fsS --max-time 3 "http://127.0.0.1:$port/fpm-status?json" \
            | awk -v stamp="$stamp" '{ print stamp "\t" $0 }' >> "$directory/fpm.tsv" || true
        docker compose exec -T db psql -X -qAt -U shop -d shop -c "
            SELECT json_build_object(
                'connections', count(*),
                'active', count(*) FILTER (WHERE state = 'active'),
                'lock_waits', count(*) FILTER (WHERE wait_event_type = 'Lock'),
                'max_connections', current_setting('max_connections')::integer,
                'jobs_pending', (SELECT count(*) FROM jobs WHERE reserved_at IS NULL),
                'jobs_reserved', (SELECT count(*) FROM jobs WHERE reserved_at IS NOT NULL),
                'oldest_available_seconds', (
                    SELECT COALESCE(floor(extract(epoch FROM now())) - min(available_at), 0)
                    FROM jobs
                    WHERE reserved_at IS NULL AND available_at <= extract(epoch FROM now())
                )
            )
            FROM pg_stat_activity
            WHERE datname = current_database() AND pid <> pg_backend_pid();
        " | awk -v stamp="$stamp" '{ print stamp "\t" $0 }' >> "$directory/database.tsv" || true
        docker stats --no-stream --format '{{json .}}' "${container_ids[@]}" \
            | awk -v stamp="$stamp" '{ print stamp "\t" $0 }' >> "$directory/resources.tsv" || true
        sleep 3
    done
}

sampler=''
stop_sampler() {
    if [[ -n "$sampler" ]]; then
        kill "$sampler" 2>/dev/null || true
        wait "$sampler" 2>/dev/null || true
        sampler=''
    fi
}
trap stop_sampler EXIT

if [[ "$profile" != cold ]]; then
    sample_resources 2> "$directory/sampler.log" &
    sampler=$!
fi

status=0
docker compose --profile bench run -T --rm \
    --user "$(id -u):$(id -g)" \
    --volume "$directory:/results" \
    -e PROFILE="$profile" -e WORKLOAD="$workload" -e RATE="$rate" -e SECONDS="$seconds" \
    -e CATALOGUE_VARIANT="$catalogue_variant" -e CATALOGUE_PAGE="$catalogue_page" \
    -e CACHE_MODE="$cache_mode" -e CACHE_TRAFFIC="$cache_traffic" \
    -e EXPORT_MODE="$export_mode" \
    -e VUS="$vus" -e MAX_VUS="$max_vus" -e RUN_ID="$run_id" \
    k6 run --quiet --no-color /lab/workload.js 2>&1 | tee "$directory/k6.log" || status=$?

stop_sampler
if [[ "$workload" == cache && -n "$redis_id" ]]; then
    docker compose exec -T redis redis-cli --raw INFO server memory stats > "$directory/redis-after.txt" || status=1
fi
if [[ "$profile" == cold ]]; then
    curl -fsS --max-time 5 "http://127.0.0.1:$port/api/lab" > "$directory/runtime.json" || status=1
fi
docker compose exec -T php php artisan shop:report "$run_id" > "$directory/persisted.json" || status=1
if [[ ! -s "$directory/summary.json" ]]; then
    printf 'No k6 summary was produced. See %s/k6.log.\n' "$directory" >&2
    exit 1
fi
printf 'Summary: %s/summary.json\n' "$directory"
printf 'Database check: %s/persisted.json\n' "$directory"
exit "$status"
