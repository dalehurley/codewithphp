EXPLAIN (ANALYZE, BUFFERS, TIMING OFF)
SELECT id, reference, product_id, total_cents, created_at
FROM orders
WHERE product_id = 42
  AND created_at >= TIMESTAMPTZ '2025-04-01 00:00:00+00'
  AND created_at <  TIMESTAMPTZ '2025-06-01 00:00:00+00'
ORDER BY created_at DESC, id DESC
LIMIT 20;
