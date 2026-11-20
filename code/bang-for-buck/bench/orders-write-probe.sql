-- This probe executes the INSERT. Rollback keeps the probe orders out of the shop.
BEGIN;

EXPLAIN (ANALYZE, BUFFERS, WAL, TIMING OFF)
INSERT INTO orders (
    run_id, reference, product_id, quantity, unit_price_cents,
    shipping_cents, total_cents, created_at
)
SELECT 'orders-write-probe', 'probe-' || series.n, p.id,
       1, p.price_cents, 399 + p.id % 5 * 50,
       p.price_cents + 399 + p.id % 5 * 50,
       TIMESTAMPTZ '2026-01-01 00:00:00+00' + series.n * INTERVAL '1 minute'
FROM generate_series(1, 1000) AS series(n)
JOIN products AS p ON p.id = 1 + (series.n - 1) % 500
ON CONFLICT (run_id, reference) DO NOTHING;

ROLLBACK;
