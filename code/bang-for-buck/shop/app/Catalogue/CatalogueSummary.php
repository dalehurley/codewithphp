<?php

declare(strict_types=1);

namespace App\Catalogue;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RedisException;

final class CatalogueSummary
{
    private const VALUE_TTL = 60;
    private const GENERATION_TTL = 3600;

    public function read(int $category, string $mode): array
    {
        if ($mode === 'bypass') {
            return [$this->query($category), 'bypass'];
        }

        $summary = null;
        try {
            $cache = $this->store();
            $key = $this->key($cache, $category);
            if (($summary = $cache->get($key)) !== null) {
                return [$summary, 'hit'];
            }

            $fill = function () use ($cache, $key, $category, &$summary): array {
                $summary = $this->query($category);
                if ($summary !== null && !$cache->put($key, $summary, self::VALUE_TTL)) {
                    return [$summary, 'fallback'];
                }

                return [$summary, 'miss'];
            };

            if ($mode === 'direct') {
                return $fill();
            }

            return $cache->lock($key . ':lock', 5)->betweenBlockedAttemptsSleepFor(5)
                ->block(1, function () use ($cache, $key, $fill, &$summary): array {
                    if (($summary = $cache->get($key)) !== null) {
                        return [$summary, 'coalesced'];
                    }

                    return $fill();
                });
        } catch (RedisException | LockTimeoutException) {
            return [$summary ?? $this->query($category), 'fallback'];
        }
    }

    public function invalidateAfterCommit(array $categories): void
    {
        DB::afterCommit(function () use ($categories): void {
            $cache = $this->store();
            foreach (array_unique($categories) as $category) {
                try {
                    if (!$cache->put($this->namespace((int) $category) . ':generation', (string) Str::uuid(), self::GENERATION_TTL)) {
                        throw new RedisException('Redis did not store the new generation.');
                    }
                } catch (RedisException $exception) {
                    Log::warning('Catalogue invalidation failed after commit.', ['category' => $category, 'error' => $exception->getMessage()]);
                }
            }
        });
    }

    public function expireSoon(int $category): bool
    {
        $cache = $this->store();
        $prefix = $this->namespace($category);
        $generation = $cache->get($prefix . ':generation');

        return $generation !== null && $cache->touch($prefix . ':' . $generation, 1);
    }

    private function key(Repository $cache, int $category): string
    {
        $prefix = $this->namespace($category);
        $generationKey = $prefix . ':generation';
        $generation = $cache->get($generationKey);
        if ($generation === null) {
            $candidate = (string) Str::uuid();
            $generation = $cache->add($generationKey, $candidate, self::GENERATION_TTL)
                ? $candidate : $cache->get($generationKey);
        }
        if ($generation === null) {
            throw new RedisException('The catalogue generation is unavailable.');
        }

        return $prefix . ':' . $generation;
    }

    private function store(): Repository
    {
        return Cache::store(config('shop.catalogue_cache_store'));
    }

    private function namespace(int $category): string
    {
        return 'catalogue-summary:v1:' . $category;
    }

    private function query(int $category): ?array
    {
        $rows = DB::table('categories as c')
            ->leftJoin('products as p', 'p.category_id', '=', 'c.id')
            ->where('c.id', $category)
            ->orderBy('p.id')
            ->limit(20)
            ->get([
                'c.id as category_id', 'c.name as category', 'p.id', 'p.title', 'p.price_cents',
                DB::raw('count(p.id) over () as product_count'),
                DB::raw('min(p.price_cents) over () as min_price_cents'),
                DB::raw('max(p.price_cents) over () as max_price_cents'),
            ]);
        $first = $rows->first();
        if ($first === null) {
            return null;
        }

        return [
            'category_id' => (int) $first->category_id,
            'category' => $first->category,
            'product_count' => (int) $first->product_count,
            'min_price_cents' => $first->min_price_cents === null ? null : (int) $first->min_price_cents,
            'max_price_cents' => $first->max_price_cents === null ? null : (int) $first->max_price_cents,
            'featured' => $rows->whereNotNull('id')->map(static fn (object $row): array => [
                'id' => (int) $row->id,
                'title' => $row->title,
                'price_cents' => (int) $row->price_cents,
            ])->values()->all(),
        ];
    }
}
