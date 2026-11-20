<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateExport;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Http::preventStrayRequests();
    }

    public function test_catalogue_seed_and_page_are_repeatable(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->getJson('/api/lab')->assertOk()->assertJsonPath('products', 5000);
        $this->getJson('/api/catalogue?category=2&page=3')
            ->assertOk()
            ->assertJsonCount(20, 'items')
            ->assertJsonPath('items.0.id', 291)
            ->assertJsonPath('items.19.id', 310)
            ->assertJsonPath('items.0.category', 'Category 2');
    }

    public function test_catalogue_trace_is_opt_in_and_counts_only_this_request(): void
    {
        $url = '/api/catalogue?category=1&page=1';
        $default = $this->getJson($url)->assertOk()->assertHeaderMissing('Server-Timing');

        foreach (['&variant=lazy' => 22, '&variant=eager' => 3, '&variant=projected' => 3, '' => 3] as $suffix => $queries) {
            $response = $this->getJson($url . $suffix, ['X-Shop-Trace' => '1'])
                ->assertOk()->assertExactJson($default->json());
            $timing = $response->headers->get('Server-Timing');
            $this->assertStringContainsString("desc=\"{$queries} queries\"", $timing);
            $this->assertStringContainsString('shipping;dur=0.00', $timing);
            $this->assertStringContainsString('other;dur=', $timing);
        }
    }

    public function test_cursor_preserves_page_order_and_reaches_the_end(): void
    {
        $url = '/api/catalogue?category=1&variant=cursor';
        $first = $this->getJson($url)->assertOk()->assertJsonPath('items.0.id', 1)
            ->assertJsonPath('items.19.id', 20)->assertJsonPath('has_more', true);

        $second = $this->getJson($url . '&cursor=' . rawurlencode($first->json('next_cursor')))
            ->assertOk()->assertJsonPath('items.0.id', 21)->assertJsonPath('items.19.id', 40);
        $offset = $this->getJson('/api/catalogue?category=1&page=2')->assertOk();
        $this->assertSame($offset->json('items'), $second->json('items'));

        $lastCursor = (new Cursor(['id' => 240], true))->encode();
        $this->getJson($url . '&cursor=' . $lastCursor)
            ->assertOk()->assertJsonCount(10, 'items')->assertJsonPath('items.9.id', 250)
            ->assertJsonPath('has_more', false)->assertJsonPath('next_cursor', null);
    }

    public function test_cursor_rejects_invalid_tokens_and_offset_parameters(): void
    {
        $url = '/api/catalogue?category=1&variant=cursor';
        $this->getJson($url . '&cursor=invalid')->assertUnprocessable()->assertJsonValidationErrors('cursor');
        $cursorWithoutId = (new Cursor(['price_cents' => 500], true))->encode();
        $this->getJson($url . '&cursor=' . $cursorWithoutId)->assertUnprocessable()->assertJsonValidationErrors('cursor');
        $this->getJson($url . '&page=2')->assertUnprocessable()->assertJsonValidationErrors('page');
    }

    public function test_catalogue_preview_renders_seed_data_and_a_discovered_listener(): void
    {
        $this->get('/api/catalogue-preview')
            ->assertOk()
            ->assertSee('<h1>First 20 products</h1>', false)
            ->assertSee('<li data-product="1">Product 1 — 537 cents</li>', false)
            ->assertSee('<li data-product="20">Product 20 — 1240 cents</li>', false)
            ->assertDontSee('data-product="21"', false);
    }

    public function test_catalogue_summary_serves_hits_without_sql_and_refreshes_after_a_price_change(): void
    {
        $url = '/api/catalogue-summary?category=1';
        $headers = ['X-Shop-Trace' => '1'];
        $before = $this->getJson($url, $headers)->assertOk()->assertHeader('X-Catalogue-Cache', 'miss')
            ->assertJsonPath('product_count', 250)->assertJsonPath('featured.0.price_cents', 537);
        $this->assertStringContainsString('desc="1 queries"', $before->headers->get('Server-Timing'));
        $hit = $this->getJson($url, $headers)->assertOk()->assertHeader('X-Catalogue-Cache', 'hit')->assertExactJson($before->json());
        $this->assertStringContainsString('desc="0 queries"', $hit->headers->get('Server-Timing'));
        $this->getJson('/api/catalogue-summary?category=2')->assertHeader('X-Catalogue-Cache', 'miss');

        $this->artisan('shop:product-price', ['product' => 1, 'price_cents' => 99])->assertSuccessful();

        $this->getJson($url)->assertOk()->assertHeader('X-Catalogue-Cache', 'miss')
            ->assertJsonPath('featured.0.price_cents', 99)->assertJsonPath('min_price_cents', 99);
        $this->getJson($url . '&cache=bypass')->assertOk()->assertHeader('X-Catalogue-Cache', 'bypass')
            ->assertJsonPath('featured.0.price_cents', 99);
        $this->getJson('/api/catalogue-summary?category=2')->assertHeader('X-Catalogue-Cache', 'hit');
    }

    public function test_catalogue_summary_keeps_its_generation_when_a_product_change_rolls_back(): void
    {
        $url = '/api/catalogue-summary?category=1';
        $this->getJson($url)->assertHeader('X-Catalogue-Cache', 'miss');

        DB::beginTransaction();
        try {
            $product = Product::query()->findOrFail(1);
            $product->price_cents = 99;
            $product->save();
            $this->getJson($url)->assertHeader('X-Catalogue-Cache', 'hit')->assertJsonPath('featured.0.price_cents', 537);
        } finally {
            DB::rollBack();
        }

        $this->getJson($url)->assertHeader('X-Catalogue-Cache', 'hit')->assertJsonPath('featured.0.price_cents', 537);
    }

    public function test_retrying_an_order_returns_the_same_order_and_rejects_changed_input(): void
    {
        Http::fake(['http://shipping:8081*' => Http::response(['shipping_cents' => 449])]);

        $input = ['run_id' => 'test', 'reference' => 'order-1', 'product_id' => 1, 'quantity' => 2];
        $headers = ['X-Shop-Trace' => '1'];
        $original = $this->postJson('/api/orders', $input, $headers)->assertCreated()->assertJsonPath('total_cents', 1523);
        $this->assertStringContainsString('desc="4 queries"', $original->headers->get('Server-Timing'));

        $replay = $this->postJson('/api/orders', $input, $headers)->assertOk()->assertExactJson($original->json());
        $this->assertStringContainsString('desc="1 queries"', $replay->headers->get('Server-Timing'));
        $this->assertStringContainsString('shipping;dur=0.00', $replay->headers->get('Server-Timing'));
        $this->postJson('/api/orders', array_replace($input, ['quantity' => 3]))->assertConflict();

        Http::assertSentCount(1);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_shipping_failure_leaves_no_partial_order(): void
    {
        Http::fake(['http://shipping:8081*' => Http::response([], 500)]);

        $this->postJson('/api/orders', ['run_id' => 'test', 'reference' => 'order-1', 'product_id' => 1, 'quantity' => 2])
            ->assertServiceUnavailable();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_history_filters_the_window_and_breaks_timestamp_ties_by_id(): void
    {
        foreach ([
            ['too-early', 42, '2025-03-31 23:59:59+00'],
            ['first-in-window', 42, '2025-04-01 00:00:00+00'],
            ['older-id', 42, '2025-05-19 13:41:00+00'],
            ['newer-id', 42, '2025-05-19 13:41:00+00'],
            ['exclusive-end', 42, '2025-06-01 00:00:00+00'],
            ['other-product', 43, '2025-05-21 12:00:00+00'],
        ] as [$reference, $product, $placedAt]) {
            DB::table('orders')->insert([
                'run_id' => 'test-history',
                'reference' => $reference,
                'product_id' => $product,
                'quantity' => 1,
                'unit_price_cents' => 500,
                'shipping_cents' => 399,
                'total_cents' => 899,
                'created_at' => $placedAt,
            ]);
        }

        $history = $this->getJson('/api/order-history?product_id=42&from=2025-04-01&before=2025-06-01', ['X-Shop-Trace' => '1'])
            ->assertOk()->assertJsonCount(3, 'items');
        $this->assertSame(['newer-id', 'older-id', 'first-in-window'], array_column($history->json('items'), 'reference'));
        $this->assertStringContainsString('desc="1 queries"', $history->headers->get('Server-Timing'));
        $this->getJson('/api/order-history?product_id=42&from=2025-06-01&before=2025-04-01')
            ->assertUnprocessable()->assertJsonValidationErrors('before');
    }

    public function test_an_export_is_queued_once_and_downloads_the_seeded_rows(): void
    {
        Storage::fake('local');
        $input = ['run_id' => 'test', 'reference' => 'export-1', 'category_id' => 2];
        $export = $this->postJson('/api/exports', $input)->assertAccepted()->assertJsonPath('state', 'pending');
        $id = $export->json('id');

        $this->postJson('/api/exports', $input)->assertOk()->assertHeaderMissing('Location')->assertJsonPath('id', $id);
        $this->postJson('/api/exports', array_replace($input, ['category_id' => 1]))->assertConflict();
        $this->getJson("/api/exports/{$id}/file")->assertNotFound();
        $this->assertDatabaseCount('jobs', 1);

        $this->assertSame(0, Artisan::call('queue:work', ['connection' => 'database', '--once' => true]));
        $this->getJson("/api/exports/{$id}")->assertOk()->assertJsonPath('state', 'ready')->assertJsonPath('rows', 250);
        $this->postJson('/api/exports', $input)->assertOk()->assertJsonPath('id', $id)->assertJsonPath('state', 'ready');
        $download = $this->get("/api/exports/{$id}/file")->assertOk()->assertDownload("{$id}.csv");
        $lines = explode("\n", trim($download->streamedContent()));
        $this->assertCount(251, $lines);
        $this->assertSame('id,title,price_cents', $lines[0]);
        $this->assertSame('251,"Product 251",2313', $lines[1]);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('exports', 1);
    }

    public function test_sync_export_returns_the_same_csv_as_the_worker_and_reuses_completed_files(): void
    {
        Storage::fake('local');
        $input = ['run_id' => 'test', 'reference' => 'inline', 'category_id' => 2];
        $inline = $this->postJson('/api/exports/sync', $input)->assertOk();
        $id = (int) $inline->headers->get('X-Export-ID');
        $inline->assertDownload("{$id}.csv");
        $this->assertDatabaseCount('jobs', 0);
        $this->getJson("/api/exports/{$id}")->assertOk()->assertJsonPath('state', 'ready')
            ->assertJsonPath('rows', 250)->assertJsonPath('queue_wait_ms', null);
        $this->postJson('/api/exports', $input)->assertConflict();

        $queued = $this->postJson('/api/exports', array_replace($input, ['reference' => 'queued']))->assertAccepted();
        $queuedId = $queued->json('id');
        $queued->assertHeader('Location', "/api/exports/{$queuedId}");
        $this->assertSame(0, Artisan::call('queue:work', ['connection' => 'database', '--once' => true]));
        $file = Storage::disk('local')->get("exports/{$queuedId}.csv");
        $this->assertSame($file, $inline->streamedContent());

        DB::table('products')->where('id', 251)->update(['price_cents' => 99]);
        GenerateExport::dispatchSync($queuedId);
        $retry = $this->postJson('/api/exports/sync', $input)->assertHeader('X-Export-ID', (string) $id);
        $this->assertSame($file, $retry->streamedContent());
        $this->assertSame($file, Storage::disk('local')->get("exports/{$queuedId}.csv"));
        $this->assertDatabaseCount('exports', 2);
        $this->assertDatabaseCount('jobs', 0);
    }
}
