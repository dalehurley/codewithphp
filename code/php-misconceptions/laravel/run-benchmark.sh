#!/usr/bin/env bash

set -euo pipefail

if [[ $# -lt 2 || $# -gt 3 || ( "$1" != 0 && "$1" != 1 ) || ( $# == 3 && "$3" != --quick ) ]]; then
    printf 'Usage: bash run-benchmark.sh <expected OPcache: 0 or 1> <new results directory> [--quick]\n' >&2
    exit 1
fi

expected_opcache=$1
results_dir=$2
profile=full
trials=3
warmup=200
requests=2500
concurrency=4

if [[ ${3:-} == --quick ]]; then
    profile=quick
    trials=1
    warmup=10
    requests=50
fi
benchmark_host=http://127.0.0.1
scratch=$(mktemp -d)
trap 'rm -rf "$scratch"' EXIT
report="$scratch/report.txt"
classic_url="$benchmark_host:8010/_benchmark"
worker_url="$benchmark_host:8011/_benchmark"

fetch_json() {
    local url=$1
    local destination=$2
    local status

    status=$(curl --noproxy '*' --max-time 15 -fsS -o "$destination" -w '%{http_code}' "$url")
    if [[ "$status" != 200 ]]; then
        printf 'Expected HTTP 200 at %s, got %s\n' "$url" "$status" >&2
        return 1
    fi
}

fetch_json "$classic_url/runtime" "$scratch/classic.json"
fetch_json "$worker_url/runtime" "$scratch/worker.json"

php -r '
    $expectedOpcache = $argv[1] === "1";
    $runtimes = [];

    foreach (["classic" => "index.php", "worker" => "frankenphp-worker.php"] as $mode => $entrypoint) {
        $body = file_get_contents("$argv[2]/$mode.json");
        $runtime = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (
            ($runtime["sapi"] ?? null) !== "frankenphp"
            || ($runtime["entrypoint"] ?? null) !== $entrypoint
            || ($runtime["opcache_available"] ?? null) !== true
            || ($runtime["opcache"] ?? null) !== $expectedOpcache
            || !in_array($runtime["jit_config"] ?? null, ["disable", false], true)
            || ($runtime["jit"] ?? null) !== false
            || !is_string($runtime["php"] ?? null)
        ) {
            fwrite(STDERR, "Unexpected $mode runtime. Expected FrankenPHP, $entrypoint, OPcache=$argv[1] and JIT off. Got $body\n");
            exit(1);
        }

        $runtimes[$mode] = $runtime;
    }

    if ($runtimes["classic"]["php"] !== $runtimes["worker"]["php"]) {
        fwrite(STDERR, "The two modes report different PHP versions.\n");
        exit(1);
    }
' "$expected_opcache" "$scratch"

fetch_json "$classic_url/orders/per-row" "$scratch/orders.json"
php -r '
    $orders = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
    $expected = array_map(
        static fn (int $id): array => ["id" => $id, "customer" => "Customer $id"],
        range(1, 40),
    );

    if ($orders !== $expected) {
        fwrite(STDERR, "Order response does not match the 40 seeded orders. Check the benchmark database.\n");
        exit(1);
    }
' "$scratch/orders.json"
printf '{"ok":true}' > "$scratch/ping.json"
for url in "$classic_url" "$worker_url"; do
    fetch_json "$url/ping" "$scratch/response.json"
    if ! cmp -s "$scratch/ping.json" "$scratch/response.json"; then
        printf 'Unexpected ping response at %s/ping\n' "$url" >&2
        exit 1
    fi

    for path in orders/per-row orders/join; do
        fetch_json "$url/$path" "$scratch/response.json"
        if ! cmp -s "$scratch/orders.json" "$scratch/response.json"; then
            printf 'Order response mismatch at %s/%s\n' "$url" "$path" >&2
            exit 1
        fi
    done
done

mkdir -p "$(dirname "$results_dir")"
mkdir "$results_dir"
cp composer.json composer.lock "$results_dir/"
{
    uname -smr
    php --version
    composer --version
    ab -V
    printf 'Profile: %s, trials: %s, warm-up: %s, requests: %s, concurrency: %s\n' \
        "$profile" "$trials" "$warmup" "$requests" "$concurrency"
    printf 'classic runtime  '
    cat "$scratch/classic.json"
    printf '\nworker runtime   '
    cat "$scratch/worker.json"
    printf '\n'
} > "$results_dir/environment.txt"

run_requests() {
    local count=$1
    local url=$2
    local label=$3
    local ab_status=0

    ab -q -n "$count" -c "$concurrency" "$url" > "$report" 2>&1 || ab_status=$?
    {
        printf '\n=== %s (%s requests, concurrency %s) ===\n' "$label" "$count" "$concurrency"
        cat "$report"
    } >> "$results_dir/apachebench.txt"

    if (( ab_status != 0 )) || ! awk -v expected="$count" '
        /^Complete requests:/ { complete = $3 }
        /^Failed requests:/ { failed = $3 }
        /^Non-2xx responses:/ { non_success = $3 }
        END { exit !(complete == expected && failed == "0" && non_success == 0) }
    ' "$report"; then
        cat "$report" >&2
        return 1
    fi
}

printf 'trial\tmode\tendpoint\trequests/s\tp50 ms\tp95 ms\n' | tee "$results_dir/summary.tsv"
for ((trial = 1; trial <= trials; trial++)); do
    modes=(classic worker)
    if (( trial % 2 == 0 )); then
        modes=(worker classic)
    fi

    for mode in "${modes[@]}"; do
        base_url=$classic_url
        if [[ "$mode" == worker ]]; then
            base_url=$worker_url
        fi

        for path in ping orders/per-row orders/join; do
            url="$base_url/$path"
            printf 'Trial %s, %s, %s\n' "$trial" "$mode" "$path" >&2
            run_requests "$warmup" "$url" "trial $trial $mode $path warm-up"
            run_requests "$requests" "$url" "trial $trial $mode $path measurement"

            awk -v trial="$trial" -v mode="$mode" -v path="$path" '
                /^Requests per second:/ { rate = $4 }
                /^  50%/ { median = $2 }
                /^  95%/ { tail = $2 }
                END {
                    if (rate == "" || median == "" || tail == "") exit 1
                    printf "%s\t%s\t%s\t%s\t%s\t%s\n", trial, mode, path, rate, median, tail
                }
            ' "$report" | tee -a "$results_dir/summary.tsv"
        done
    done
done
