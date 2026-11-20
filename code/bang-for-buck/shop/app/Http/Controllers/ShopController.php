<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Catalogue\CatalogueSummary;
use App\Events\BuildCatalogueHeadline;
use App\Jobs\GenerateExport;
use App\Models\Product;
use App\Profiling\RequestTrace;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ShopController
{
    public function lab(): JsonResponse
    {
        $opcache = opcache_get_status(false);
        $benchmark = config('shop.catalogue_benchmark');

        return response()->json([
            'lab' => 'bang-for-buck-shop',
            'php' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'laravel' => app()->version(),
            'opcache' => (bool) ($opcache['opcache_enabled'] ?? false),
            'opcache_cached_scripts' => $opcache['opcache_statistics']['num_cached_scripts'] ?? 0,
            'laravel_caches' => [
                'config' => app()->configurationIsCached(),
                'routes' => app()->routesAreCached(),
                'events' => app()->eventsAreCached(),
                'preview_compiled' => !app('blade.compiler')->isExpired(resource_path('views/catalogue-preview.blade.php')),
            ],
            'database' => DB::connection()->getDriverName(),
            'categories' => DB::table('categories')->count(),
            'products_per_category' => (int) config('shop.products_per_category'),
            'products' => DB::table('products')->count(),
            'catalogue_benchmark' => $benchmark + [
                'products' => DB::table('products')->where('category_id', $benchmark['category'])->count(),
            ],
        ]);
    }

    public function cataloguePreview(): View
    {
        $products = DB::table('products')->orderBy('id')->limit(20)->get(['id', 'title', 'price_cents']);

        return view('catalogue-preview', [
            'headline' => Event::until(new BuildCatalogueHeadline($products->count())),
            'products' => $products,
        ]);
    }

    public function catalogue(Request $request): JsonResponse
    {
        $input = $request->validate([
            'category' => ['required', 'integer', 'exists:categories,id'],
            'variant' => ['sometimes', 'in:lazy,eager,projected,cursor'],
            'page' => ['sometimes', 'prohibited_if:variant,cursor', 'integer', 'min:1', 'max:1000'],
            'cursor' => ['sometimes', 'prohibited_unless:variant,cursor', 'string', 'max:200'],
        ]);
        $variant = $input['variant'] ?? 'projected';

        $query = Product::query()
            ->where('category_id', $input['category'])
            ->orderBy('id');

        if ($variant === 'eager') {
            $query->with('category');
        } elseif ($variant !== 'lazy') {
            $query->select('id', 'category_id', 'title', 'price_cents')
                ->with('category:id,name');
        }

        // The lazy variant keeps Chapter 1's original workload reproducible.
        $products = $variant === 'cursor'
            ? $query->cursorPaginate(20, ['*'], 'cursor', $this->nextCatalogueCursor($input['cursor'] ?? null))
            : $query->simplePaginate(20);
        $items = $products->getCollection()->map(static fn (Product $product): array => [
            'id' => $product->id,
            'title' => $product->title,
            'price_cents' => $product->price_cents,
            'category' => $product->category->name,
        ]);

        $response = [
            'items' => $items,
            'has_more' => $products->hasMorePages(),
        ];
        if ($products instanceof CursorPaginator) {
            $response['next_cursor'] = $products->nextCursor()?->encode();
        }

        return response()->json($response);
    }

    public function catalogueSummary(Request $request, CatalogueSummary $catalogue): JsonResponse
    {
        $input = $request->validate([
            'category' => ['required', 'integer', 'min:1', 'max:' . config('shop.catalogue_benchmark.category')],
            'cache' => ['sometimes', 'in:bypass,direct,locked'],
        ]);
        $mode = app()->environment('lab', 'testing') ? ($input['cache'] ?? 'locked') : 'locked';
        [$summary, $status] = $catalogue->read((int) $input['category'], $mode);
        abort_if($summary === null, 404);

        return response()->json($summary)->header('X-Catalogue-Cache', $status);
    }

    public function order(Request $request): JsonResponse
    {
        $input = $request->validate([
            'run_id' => ['required', 'alpha_dash:ascii', 'max:64'],
            'reference' => ['required', 'alpha_dash:ascii', 'max:64'],
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'between:1,5'],
        ]);
        $identity = ['run_id' => $input['run_id'], 'reference' => $input['reference']];
        $existing = DB::table('orders')->where($identity)->first();
        if ($existing !== null) {
            return $this->orderResponse($existing, $input, 200);
        }

        $product = Product::query()->findOrFail($input['product_id']);
        $trace = $request->attributes->get(RequestTrace::class);
        $shippingStartedAt = $trace instanceof RequestTrace ? hrtime(true) : null;
        try {
            $quote = Http::acceptJson()->connectTimeout(1)->timeout(2)
                ->get(config('shop.shipping_url'), ['product' => $product->id]);
        } catch (ConnectionException) {
            abort(503, 'Shipping quotes are temporarily unavailable.');
        } finally {
            if ($shippingStartedAt !== null) {
                $trace->recordShipping($shippingStartedAt);
            }
        }

        $shipping = $quote->json('shipping_cents');
        abort_unless($quote->successful() && is_int($shipping) && $shipping >= 0, 503, 'Shipping quotes are temporarily unavailable.');

        $quantity = (int) $input['quantity'];
        $price = (int) $product->price_cents;
        $inserted = DB::table('orders')->insertOrIgnore($identity + [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price_cents' => $price,
            'shipping_cents' => $shipping,
            'total_cents' => $price * $quantity + $shipping,
            'created_at' => now()->format('Y-m-d H:i:s.uP'),
        ]);
        $order = DB::table('orders')->where($identity)->firstOrFail();

        return $this->orderResponse($order, $input, $inserted === 1 ? 201 : 200);
    }

    public function orderHistory(Request $request): JsonResponse
    {
        $input = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'from' => ['required', 'date_format:Y-m-d'],
            'before' => ['required', 'date_format:Y-m-d', 'after:from'],
        ]);

        $orders = DB::table('orders')
            ->where('product_id', $input['product_id'])
            ->where('created_at', '>=', $input['from'] . ' 00:00:00+00')
            ->where('created_at', '<', $input['before'] . ' 00:00:00+00')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'reference', 'product_id', 'total_cents', 'created_at']);

        return response()->json(['items' => $orders]);
    }

    public function requestExport(Request $request): JsonResponse|StreamedResponse
    {
        $input = $request->validate([
            'run_id' => ['required', 'alpha_dash:ascii', 'max:64'],
            'reference' => ['required', 'alpha_dash:ascii', 'max:64'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);
        $identity = ['run_id' => $input['run_id'], 'reference' => $input['reference']];
        $mode = $request->routeIs('shop.exports.sync') ? 'sync' : 'queued';

        [$export, $inserted] = DB::transaction(static function () use ($identity, $input, $mode): array {
            $inserted = DB::table('exports')->insertOrIgnore($identity + [
                'category_id' => $input['category_id'],
                'mode' => $mode,
                'state' => 'pending',
                'accepted_at' => now()->format('Y-m-d H:i:s.uP'),
            ]);
            $export = DB::table('exports')->where($identity)->firstOrFail();

            abort_unless(
                (int) $export->category_id === (int) $input['category_id'] && $export->mode === $mode,
                409,
                'The reference already belongs to a different export.',
            );

            if ($inserted === 1) {
                if ($mode === 'sync') {
                    GenerateExport::dispatchSync((int) $export->id);
                } else {
                    // The database queue uses the same connection and commits with the export.
                    GenerateExport::dispatch((int) $export->id)->onConnection('database')->beforeCommit();
                }
            }

            return [$export, $inserted];
        });

        if ($mode === 'sync') {
            $response = $this->download((int) $export->id);
            $response->headers->set('X-Export-ID', (string) $export->id);

            return $response;
        }

        return response()->json([
            'id' => $export->id,
            'state' => $export->state,
        ], $inserted === 1 ? 202 : 200, $inserted === 1 ? ['Location' => "/api/exports/{$export->id}"] : []);
    }

    public function exportStatus(int $export): JsonResponse
    {
        $record = DB::table('exports')->find($export);
        abort_if($record === null, 404);

        $accepted = CarbonImmutable::parse($record->accepted_at);
        $start = $record->started_at ? CarbonImmutable::parse($record->started_at) : null;
        $finish = $record->finished_at ? CarbonImmutable::parse($record->finished_at) : null;

        return response()->json([
            'id' => $record->id,
            'state' => $record->state,
            'rows' => $record->rows_written,
            'queue_wait_ms' => $record->mode === 'queued' && $start ? round($accepted->diffInMilliseconds($start), 2) : null,
            'processing_ms' => $start && $finish ? round($start->diffInMilliseconds($finish), 2) : null,
            'elapsed_ms' => $finish ? round($accepted->diffInMilliseconds($finish), 2) : null,
        ]);
    }

    public function download(int $export): StreamedResponse
    {
        abort_unless(DB::table('exports')->where('id', $export)->where('state', 'ready')->exists(), 404);

        return Storage::disk('local')->download("exports/{$export}.csv", headers: ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function nextCatalogueCursor(?string $encoded): ?Cursor
    {
        if ($encoded === null) {
            return null;
        }

        $cursor = Cursor::fromEncoded($encoded);
        $position = $cursor?->toArray();
        $id = $position['id'] ?? null;
        if (!is_int($id) || $id < 1 || ($position['_pointsToNextItems'] ?? null) !== true) {
            throw ValidationException::withMessages(['cursor' => 'Use the next cursor returned by the catalogue.']);
        }

        return $cursor;
    }

    private function orderResponse(object $order, array $input, int $status): JsonResponse
    {
        abort_unless(
            (int) $order->product_id === (int) $input['product_id'] && (int) $order->quantity === (int) $input['quantity'],
            409,
            'The reference already belongs to a different order.',
        );

        return response()->json([
            'id' => $order->id,
            'product_id' => $order->product_id,
            'quantity' => $order->quantity,
            'unit_price_cents' => $order->unit_price_cents,
            'shipping_cents' => $order->shipping_cents,
            'total_cents' => $order->total_cents,
        ], $status);
    }
}
