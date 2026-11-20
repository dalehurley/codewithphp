<?php

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('suppliers:quote {mode=pool} {--south=ok}', function (): int {
    $mode = $this->argument('mode');
    $south = $this->option('south');
    if (! in_array($mode, ['serial', 'pool'], true) || ! in_array($south, ['ok', 'slow', 'error'], true)) {
        $this->error('Use mode serial or pool and --south=ok, slow or error.');

        return Command::INVALID;
    }

    $southPath = match ($south) {
        'slow' => '/south-slow',
        'error' => '/south-error',
        default => '/south',
    };
    $urls = [
        'north' => 'http://127.0.0.1:8031/north',
        'south' => 'http://127.0.0.1:8032'.$southPath,
    ];

    $startedAt = hrtime(true);
    $results = Http::pool(static function (Pool $pool) use ($urls): void {
        foreach ($urls as $supplier => $url) {
            $pool->as($supplier)->acceptJson()->connectTimeout(1)->timeout(1)->get($url);
        }
    }, concurrency: $mode === 'serial' ? 1 : 2);
    $elapsedMs = (hrtime(true) - $startedAt) / 1_000_000;

    $prices = [];
    foreach (array_keys($urls) as $supplier) {
        $result = $results[$supplier];
        if ($result instanceof Response && $result->successful()) {
            $cents = $result->json('cents');
            if (is_int($cents) && $cents >= 0) {
                $prices[$supplier] = $cents;
                $this->line("{$supplier}: {$cents} cents");

                continue;
            }
        }

        $reason = match (true) {
            $result instanceof Response && $result->successful() => 'invalid quote',
            $result instanceof Response => 'HTTP '.$result->status(),
            $result instanceof ConnectionException => 'connection or timeout',
            default => 'request failed',
        };
        $this->line("{$supplier}: unavailable ({$reason})");
    }

    $this->line(sprintf('Pool elapsed: %.0F ms', $elapsedMs));

    if (count($prices) !== count($urls)) {
        $this->error('Quote incomplete. No combined total.');

        return Command::FAILURE;
    }

    $this->info('Combined quote: '.array_sum($prices).' cents');

    return Command::SUCCESS;
})->purpose('Compare calls to two local example suppliers');
