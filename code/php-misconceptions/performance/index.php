<?php

declare(strict_types=1);

$dependencyStart = hrtime(true);
// Simulate a dependency wait without contacting another service.
usleep(80_000);
$dependencyMs = (hrtime(true) - $dependencyStart) / 1_000_000;

$pricingStart = hrtime(true);
$total = array_sum([1250, 750]);
$pricingMs = (hrtime(true) - $pricingStart) / 1_000_000;

$status = opcache_get_status(false);

header('Content-Type: application/json');
header(sprintf('Server-Timing: dependency;dur=%.3F, pricing;dur=%.3F', $dependencyMs, $pricingMs));
header('X-Lab-OPcache: ' . (($status['opcache_enabled'] ?? false) ? 'on' : 'off'));

if (isset($status['opcache_statistics']['hits'])) {
    header('X-Lab-OPcache-Hits: ' . $status['opcache_statistics']['hits']);
}

echo json_encode(['total_cents' => $total], JSON_THROW_ON_ERROR), PHP_EOL;
