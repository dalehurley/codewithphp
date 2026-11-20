-- Run after the standard shop seed. The fixed history is safe to seed again.
INSERT INTO orders (
    run_id, reference, product_id, quantity, unit_price_cents,
    shipping_cents, total_cents, created_at
)
SELECT 'orders-history-fixture', 'history-' || fixture.n, p.id,
       fixture.quantity, p.price_cents, shipping.cents,
       p.price_cents * fixture.quantity + shipping.cents,
       TIMESTAMPTZ '2025-01-01 00:00:00+00' + (fixture.n - 1) * INTERVAL '1 minute'
FROM (
    SELECT n, 1 + (n - 1) % 500 AS product_id, 1 + (n - 1) % 3 AS quantity
    FROM generate_series(1, 200000) AS series(n)
) AS fixture
JOIN products AS p ON p.id = fixture.product_id
CROSS JOIN LATERAL (SELECT 399 + p.id % 5 * 50 AS cents) AS shipping
ON CONFLICT (run_id, reference) DO NOTHING;

ANALYZE orders;

SELECT count(*) AS historical_orders
FROM orders
WHERE run_id = 'orders-history-fixture';
