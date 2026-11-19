#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../.."
php_bin=${PHP_BIN:-php}

if ! "$php_bin" -r 'exit(PHP_VERSION_ID >= 80600 && PHP_VERSION_ID < 80700 ? 0 : 1);'; then
    echo 'Select a PHP 8.6 CLI with PHP_BIN.' >&2
    exit 1
fi

if [[ $# -gt 1 || (${1:-} != '' && ${1:-} != --with-mysql) ]]; then
    echo 'Usage: run-examples.sh [--with-mysql]' >&2
    exit 1
fi

diagnostics=$(mktemp)
trap 'rm -f "$diagnostics"' EXIT

passed=0
skipped=0
failed=0
for example in code/php-8-6/[0-9][0-9]-*/*.php; do
    case "$example" in
        */mysqli-*.php)
            if [[ ${1:-} != --with-mysql ]]; then
                printf 'SKIP %s (requires --with-mysql)\n' "$example"
                skipped=$((skipped + 1))
                continue
            fi
            ;;
    esac

    flags=(-d error_reporting=-1 -d display_errors=stderr -d log_errors=0)
    if [[ $example == */session-defaults.php ]]; then
        flags=(-n "${flags[@]}")
    fi

    if "$php_bin" "${flags[@]}" -l "$example" >/dev/null 2>"$diagnostics" &&
        [[ ! -s $diagnostics ]] &&
        output=$("$php_bin" "${flags[@]}" "$example" 2>"$diagnostics") &&
        [[ -n $output && ! -s $diagnostics ]]; then
        printf 'PASS %s\n' "$example"
        passed=$((passed + 1))
    else
        printf 'FAIL %s\n' "$example" >&2
        cat "$diagnostics" >&2
        failed=$((failed + 1))
    fi
done

printf '%d passed, %d skipped, %d failed\n' "$passed" "$skipped" "$failed"
[[ $failed -eq 0 ]]
