<?php

declare(strict_types=1);

namespace App\Profiling;

final class RequestTrace
{
    private readonly int $startedAt;
    private readonly ?float $startedCpuMs;
    private float $sqlMs = 0.0;
    private int $sqlCount = 0;
    private float $shippingMs = 0.0;

    public function __construct()
    {
        $this->startedAt = hrtime(true);
        $this->startedCpuMs = self::cpuMs();
    }

    public function recordQuery(float $milliseconds): void
    {
        $this->sqlMs += $milliseconds;
        $this->sqlCount++;
    }

    public function recordShipping(int $startedAt): void
    {
        $this->shippingMs += (hrtime(true) - $startedAt) / 1_000_000;
    }

    public function serverTiming(): string
    {
        $appMs = (hrtime(true) - $this->startedAt) / 1_000_000;
        $cpuMs = self::cpuMs();
        $metrics = [
            sprintf('app;dur=%.2F', $appMs),
            sprintf('sql;dur=%.2F;desc="%d queries"', $this->sqlMs, $this->sqlCount),
            sprintf('shipping;dur=%.2F', $this->shippingMs),
            sprintf('other;dur=%.2F', max(0.0, $appMs - $this->sqlMs - $this->shippingMs)),
        ];

        if ($cpuMs !== null && $this->startedCpuMs !== null) {
            $metrics[] = sprintf('cpu;dur=%.2F', $cpuMs - $this->startedCpuMs);
        }

        return implode(', ', $metrics);
    }

    private static function cpuMs(): ?float
    {
        $usage = getrusage();

        if ($usage === false) {
            return null;
        }

        return ($usage['ru_utime.tv_sec'] + $usage['ru_stime.tv_sec']) * 1000
            + ($usage['ru_utime.tv_usec'] + $usage['ru_stime.tv_usec']) / 1000;
    }
}
