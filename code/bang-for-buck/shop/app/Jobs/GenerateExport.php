<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class GenerateExport implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $exportId)
    {
    }

    public function handle(): void
    {
        $export = DB::table('exports')->find($this->exportId);
        if ($export === null || $export->state === 'ready') {
            return;
        }

        DB::table('exports')->where('id', $this->exportId)->update([
            'state' => 'running',
            'started_at' => $export->started_at ?? now()->format('Y-m-d H:i:s.uP'),
        ]);

        $products = DB::table('products')
            ->where('category_id', $export->category_id)
            ->orderBy('id')
            ->get(['id', 'title', 'price_cents']);
        $csv = fopen('php://temp', 'w+b');
        if ($csv === false) {
            throw new RuntimeException('Unable to open the CSV buffer.');
        }

        $disk = Storage::disk('local');
        $target = "exports/{$this->exportId}.csv";
        $temporary = "exports/.{$this->exportId}-" . Str::uuid() . '.tmp';
        try {
            if (fputcsv($csv, ['id', 'title', 'price_cents'], escape: '') === false) {
                throw new RuntimeException('Unable to write the CSV header.');
            }
            foreach ($products as $product) {
                if (fputcsv($csv, [$product->id, $product->title, $product->price_cents], escape: '') === false) {
                    throw new RuntimeException('Unable to write a CSV row.');
                }
            }
            rewind($csv);
            if (!$disk->put($temporary, $csv) || !$disk->move($temporary, $target)) {
                throw new RuntimeException('Unable to publish the export.');
            }
        } finally {
            fclose($csv);
            $disk->delete($temporary);
        }

        DB::table('exports')->where('id', $this->exportId)->update([
            'state' => 'ready',
            'rows_written' => $products->count(),
            'finished_at' => now()->format('Y-m-d H:i:s.uP'),
        ]);
    }

    public function failed(): void
    {
        DB::table('exports')->where('id', $this->exportId)->where('state', '!=', 'ready')->update([
            'state' => 'failed',
            'finished_at' => now()->format('Y-m-d H:i:s.uP'),
        ]);
    }
}
