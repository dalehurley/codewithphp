import { sleep } from 'k6';
import { sha256 } from 'k6/crypto';
import encoding from 'k6/encoding';
import exec from 'k6/execution';
import http from 'k6/http';
import { Counter, Rate, Trend } from 'k6/metrics';

const profile = __ENV.PROFILE || 'steady';
const preview = __ENV.WORKLOAD === 'preview';
const catalogueLab = __ENV.WORKLOAD === 'catalogue';
const ordersLab = __ENV.WORKLOAD === 'orders';
const exportLab = __ENV.WORKLOAD === 'exports';
const exportMode = __ENV.EXPORT_MODE || 'queued';
const cacheLab = __ENV.WORKLOAD === 'cache';
const cacheMode = __ENV.CACHE_MODE || 'locked';
const cacheExpiry = cacheLab && __ENV.CACHE_TRAFFIC === 'expiry';
const catalogueVariant = __ENV.CATALOGUE_VARIANT || 'projected';
const cataloguePage = Number(__ENV.CATALOGUE_PAGE || 1);
const rate = Number(__ENV.RATE || 20);
const seconds = Number(__ENV.SECONDS || 30);
const vus = Number(__ENV.VUS || 4);
const runId = __ENV.RUN_ID;
const origin = 'http://web:8080';
const arrivalPool = { preAllocatedVUs: 16, maxVUs: Number(__ENV.MAX_VUS || 80) };
const drain = '75s';
const historyPath = '/api/order-history?product_id=42&from=2025-04-01&before=2025-06-01';
const stages = [
    { duration: '15s', target: rate },
    { duration: '5s', target: rate * 3 },
    { duration: '15s', target: rate * 3 },
    { duration: '5s', target: rate },
    { duration: '20s', target: rate },
];

const scenarios = {
    steady: cacheExpiry
        ? { executor: 'per-vu-iterations', vus, iterations: 1, maxDuration: '10s' }
        : { executor: 'constant-arrival-rate', rate, timeUnit: '1s', duration: seconds + 's', ...arrivalPool },
    smoke: { executor: 'constant-arrival-rate', rate: 5, timeUnit: '1s', duration: '4s', ...arrivalPool },
    burst: { executor: 'ramping-arrival-rate', startRate: rate, timeUnit: '1s', stages, ...arrivalPool },
    closed: { executor: 'constant-vus', vus, duration: seconds + 's' },
    cold: { executor: 'shared-iterations', vus: 1, iterations: 1, maxDuration: '15s' },
};

const workflowsStarted = new Counter('shop_workflows_started');
const workflowsSucceeded = new Counter('shop_workflows_succeeded');
const ordersSaved = new Counter('shop_orders_confirmed');
const exportsAccepted = new Counter('shop_exports_accepted');
const exportsSeenReady = new Counter('shop_exports_seen_ready');
const failure = new Rate('shop_workflow_failure');
const catalogueLatency = new Trend('shop_catalogue_ms', true);
const catalogueSqlTime = new Trend('shop_catalogue_sql_ms', true);
const catalogueQueries = new Trend('shop_catalogue_queries');
const catalogueBodyBytes = new Trend('shop_catalogue_body_bytes');
const cataloguePhpCpu = new Trend('shop_catalogue_php_cpu_ms', true);
const cacheQueries = new Counter('shop_catalogue_cache_db_queries');
const cacheHits = new Counter('shop_catalogue_cache_hits');
const cacheCoalesced = new Counter('shop_catalogue_cache_coalesced');
const cacheMisses = new Counter('shop_catalogue_cache_misses');
const cacheFallbacks = new Counter('shop_catalogue_cache_fallbacks');
const orderLatency = new Trend('shop_order_ms', true);
const orderSqlTime = new Trend('shop_order_sql_ms', true);
const historyLatency = new Trend('shop_order_history_ms', true);
const historySqlTime = new Trend('shop_order_history_sql_ms', true);
const exportLatency = new Trend('shop_export_observed_ms', true);
const exportQueueWait = new Trend('shop_export_queue_wait_ms', true);
const exportProcessing = new Trend('shop_export_processing_ms', true);
const exportInitial = new Trend('shop_export_initial_http_ms', true);
const exportFileWait = new Trend('shop_export_file_wait_ms', true);
const exportDownload = new Trend('shop_export_download_http_ms', true);
const exportPolls = new Counter('shop_export_status_requests');
const exportFiles = new Counter('shop_export_files_verified');
const exportOnTime = new Rate('shop_export_on_time');
const coldLatency = new Trend('shop_cold_catalogue_ms', true);

const mixedThresholds = {
    shop_workflow_failure: ['rate<0.01'],
    'shop_workflow_failure{workflow:order}': ['rate<0.01'],
    'shop_workflow_failure{workflow:export}': ['rate<0.01'],
    shop_catalogue_ms: ['p(95)<250'],
    shop_order_ms: ['p(95)<500'],
    shop_export_on_time: ['rate>=0.95'],
};
const workloadThresholds = {
    preview: { shop_workflow_failure: ['rate<0.01'], shop_catalogue_ms: ['p(95)<250'] },
    catalogue: { shop_workflow_failure: ['rate==0'], shop_catalogue_ms: ['p(95)<250'] },
    cache: { shop_workflow_failure: ['rate==0'], shop_catalogue_ms: ['p(95)<250'] },
    orders: { shop_workflow_failure: ['rate==0'], shop_order_history_ms: ['p(95)<250'], shop_order_ms: ['p(95)<500'] },
    exports: { shop_workflow_failure: ['rate==0'], shop_export_on_time: ['rate>=0.95'] },
};
const thresholds = profile === 'cold'
    ? { shop_workflow_failure: ['rate==0'] }
    : { ...(workloadThresholds[__ENV.WORKLOAD] ?? mixedThresholds) };
if (!cacheExpiry && ['smoke', 'steady', 'burst'].includes(profile)) {
    thresholds.dropped_iterations = ['count==0'];
}
if (profile === 'burst') {
    for (const phase of ['base', 'peak', 'recovery']) {
        // Give handleSummary tagged percentiles for each phase. The targets above gate the overall run.
        thresholds['shop_catalogue_ms{phase:' + phase + '}'] = ['p(95)>=0'];
        thresholds['shop_order_ms{phase:' + phase + '}'] = ['p(95)>=0'];
    }
}
if (profile === 'cold') {
    thresholds['shop_cold_catalogue_ms{sample:first}'] = ['p(95)>=0'];
    thresholds['shop_cold_catalogue_ms{sample:second}'] = ['p(95)>=0'];
}

export const options = {
    scenarios: { [profile]: { ...scenarios[profile], gracefulStop: drain } },
    thresholds,
    summaryTrendStats: ['min', 'med', 'p(95)', 'p(99)', 'max'],
    systemTags: ['status', 'method', 'name', 'scenario', 'expected_response', 'error_code'],
};

function getShop(path, name, timeout = '3s', headers = {}) {
    return http.get(origin + path, { tags: { name }, timeout, headers });
}

function postJson(path, body, headers = {}, timeout = '3s') {
    return http.post(origin + path, JSON.stringify(body), {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...headers },
        tags: { name: path },
        timeout,
    });
}

function readBody(response, status) {
    if (response.status !== status) {
        return null;
    }

    try {
        return response.json();
    } catch (_) {
        return null;
    }
}

function sqlTiming(response) {
    return (response.headers['Server-Timing'] || '').match(/sql;dur=([\d.]+);desc="(\d+) queries"/);
}

export function setup() {
    if (!runId) {
        throw new Error('Run this workload with bench/run.sh so every run has a fresh ID.');
    }
    if (profile === 'cold') {
        return null;
    }

    const lab = readBody(getShop('/api/lab', '/api/lab'), 200);
    const fixture = lab?.catalogue_benchmark;
    const extraProducts = fixture?.products ?? 0;
    if (lab?.lab !== 'bang-for-buck-shop' || lab.categories !== 20 + (extraProducts > 0 ? 1 : 0)
        || lab.products !== 5000 + extraProducts || lab.products_per_category !== 250) {
        throw new Error('Start and seed the shop before running the workload.');
    }

    if (catalogueLab) {
        if (!fixture || fixture.products !== fixture.size || cataloguePage * 20 >= fixture.products) {
            throw new Error('Seed CatalogueBenchmarkSeeder before running the Chapter 5 workload.');
        }
        const firstProduct = fixture.first_id + (cataloguePage - 1) * 20;
        // Only the test harness constructs a cursor to target a known seeded window.
        const position = cataloguePage === 1 ? '' : '&cursor=' + encoding.b64encode(
            JSON.stringify({ id: firstProduct - 1, _pointsToNextItems: true }), 'rawurl'
        );
        const page = catalogueVariant === 'cursor' ? position : '&page=' + cataloguePage;
        return { category: fixture.category, firstProduct, path: '/api/catalogue?category=' + fixture.category + '&variant=' + catalogueVariant + page };
    }

    if (cacheLab) {
        if (!fixture || fixture.products !== fixture.size) {
            throw new Error('Seed CatalogueBenchmarkSeeder before running the Chapter 7 workload.');
        }
        return { fixture, path: '/api/catalogue-summary?category=' + fixture.category + '&cache=' + cacheMode };
    }

    if (exportLab) {
        if (!fixture || fixture.products !== fixture.size) {
            throw new Error('Seed CatalogueBenchmarkSeeder before running the Chapter 8 workload.');
        }
        const csv = ['id,title,price_cents'];
        for (let id = fixture.first_id; id < fixture.first_id + fixture.size; id++) {
            csv.push(id + ',"Product ' + id + '",' + priceFor(id));
        }
        return { fixture, csvHash: sha256(csv.join('\n') + '\n', 'hex') };
    }

    if (ordersLab && !matchesOrderHistory(readBody(getShop(historyPath, '/api/order-history'), 200))) {
        throw new Error('Seed bench/orders-fixture.sql before running the Chapter 6 workload.');
    }

    return lab;
}

function phaseForIteration() {
    if (profile !== 'burst') {
        return profile;
    }

    const phaseSeconds = (Date.now() - exec.scenario.startTime) / 1000;
    if (phaseSeconds < 15) return 'base';
    if (phaseSeconds < 20) return 'ramp';
    if (phaseSeconds < 35) return 'peak';
    if (phaseSeconds < 40) return 'ramp-down';
    return 'recovery';
}

function priceFor(id) {
    return 500 + (id % 101) * 37;
}

function matchesCatalogue(body, category, firstProduct) {
    return Array.isArray(body?.items) && body.items.length === 20 && body.has_more === true
        && body.items.every((item, index) => {
            const id = firstProduct + index;
            return item?.id === id && item.title === 'Product ' + id
                && item.category === 'Category ' + category && item.price_cents === priceFor(id);
        });
}

function catalogue(category, page, tags, variant = 'lazy') {
    const response = getShop('/api/catalogue?category=' + category + '&page=' + page + '&variant=' + variant, '/api/catalogue');
    catalogueLatency.add(response.timings.duration, tags);
    const body = readBody(response, 200);
    const firstProduct = (category - 1) * 250 + (page - 1) * 20 + 1;
    const valid = matchesCatalogue(body, category, firstProduct);

    return { valid, latency: response.timings.duration };
}

function catalogueComparison(plan, tags) {
    const response = getShop(plan.path, '/api/catalogue', '3s', { 'X-Shop-Trace': '1' });
    catalogueLatency.add(response.timings.duration, tags);

    const timing = response.headers['Server-Timing'] || '';
    const sql = sqlTiming(response);
    const cpu = timing.match(/cpu;dur=([\d.]+)/);
    const body = readBody(response, 200);
    const expectedQueries = catalogueVariant === 'lazy' ? 22 : 3;
    if (!sql || Number(sql[2]) !== expectedQueries || !matchesCatalogue(body, plan.category, plan.firstProduct)
        || (catalogueVariant === 'cursor' && !body.next_cursor)) {
        console.error('Catalogue validation failed for ' + catalogueVariant + ' page ' + cataloguePage
            + ' (HTTP ' + response.status + ', queries ' + (sql?.[2] ?? 'missing')
            + ', first product ' + (body?.items?.[0]?.id ?? 'missing') + ').');
        return false;
    }

    catalogueSqlTime.add(Number(sql[1]), tags);
    catalogueQueries.add(Number(sql[2]), tags);
    catalogueBodyBytes.add(response.body.length, tags); // The deterministic sample JSON is ASCII.
    if (cpu) cataloguePhpCpu.add(Number(cpu[1]), tags);

    return true;
}

function catalogueCacheComparison(plan, tags) {
    const response = getShop(plan.path, '/api/catalogue-summary', '3s', { 'X-Shop-Trace': '1' });
    catalogueLatency.add(response.timings.duration, tags);

    const body = readBody(response, 200);
    const { category, first_id: first, size } = plan.fixture;
    const correctSummary = body?.category_id === category && body.category === 'Category ' + category
        && body.product_count === size && body.min_price_cents === 500 && body.max_price_cents === 4200
        && Array.isArray(body.featured) && body.featured.length === 20 && body.featured.every((item, index) => {
            const id = first + index;
            return item?.id === id && item.title === 'Product ' + id && item.price_cents === priceFor(id);
        });
    const status = response.headers['X-Catalogue-Cache'];
    const queriesByStatus = { bypass: [1], hit: [0], coalesced: [0], miss: [1], fallback: [0, 1] };
    const sql = sqlTiming(response);
    if (!correctSummary || !sql || !queriesByStatus[status]?.includes(Number(sql[2]))
        || (cacheMode === 'bypass') !== (status === 'bypass')
        || (cacheMode === 'direct' && status === 'coalesced')) {
        console.error('Catalogue summary validation failed (HTTP ' + response.status + ', cache ' + (status ?? 'missing')
            + ', queries ' + (sql?.[2] ?? 'missing') + ').');
        return false;
    }

    catalogueSqlTime.add(Number(sql[1]), tags);
    cacheQueries.add(Number(sql[2]), tags);
    cacheHits.add(status === 'hit' ? 1 : 0, tags);
    cacheCoalesced.add(status === 'coalesced' ? 1 : 0, tags);
    cacheMisses.add(status === 'miss' ? 1 : 0, tags);
    cacheFallbacks.add(status === 'fallback' ? 1 : 0, tags);
    return true;
}

function cataloguePreview(tags) {
    const response = getShop('/api/catalogue-preview', '/api/catalogue-preview');
    catalogueLatency.add(response.timings.duration, tags);

    const body = typeof response.body === 'string' ? response.body : '';
    const rows = body.match(/<li data-product="[^"]+">[^<]+<\/li>/g);
    const valid = response.status === 200 && body.includes('<h1>First 20 products</h1>') && rows?.length === 20
        && rows.every((row, index) => {
            const id = index + 1;
            return row === '<li data-product="' + id + '">Product ' + id + ' — ' + priceFor(id) + ' cents</li>';
        });

    return { valid, latency: response.timings.duration };
}

function order(reference, product, quantity, tags) {
    const response = postJson('/api/orders', {
        run_id: runId, reference, product_id: product, quantity,
    }, ordersLab ? { 'X-Shop-Trace': '1' } : {});
    orderLatency.add(response.timings.duration, tags);
    const body = readBody(response, 201);
    const price = priceFor(product);
    const shipping = 399 + (product % 5) * 50;

    let valid = Number.isInteger(body?.id) && body.product_id === product && body.quantity === quantity
        && body.unit_price_cents === price && body.shipping_cents === shipping
        && body.total_cents === price * quantity + shipping;

    if (ordersLab) {
        const sql = sqlTiming(response);
        valid = valid && Number(sql?.[2]) === 4;
        if (valid) orderSqlTime.add(Number(sql[1]), tags);
        else console.error('Checkout validation failed (HTTP ' + response.status + ', SQL queries ' + (sql?.[2] ?? 'missing') + ').');
    }

    ordersSaved.add(valid ? 1 : 0, tags);
    return valid;
}

function matchesOrderHistory(body) {
    return Array.isArray(body?.items) && body.items.length === 20 && body.items.every((item, index) => {
        const sequence = 199542 - index * 500;
        const timestamp = new Date(Date.UTC(2025, 0, 1) + (sequence - 1) * 60000)
            .toISOString().slice(0, 19).replace('T', ' ');
        const quantity = 1 + (sequence - 1) % 3;

        return Number.isInteger(item?.id) && item.reference === 'history-' + sequence
            && item.product_id === 42 && item.total_cents === priceFor(42) * quantity + 499
            && typeof item.created_at === 'string' && item.created_at.startsWith(timestamp);
    });
}

function orderHistory(tags) {
    const response = getShop(historyPath, '/api/order-history', '3s', { 'X-Shop-Trace': '1' });
    historyLatency.add(response.timings.duration, tags);
    const sql = sqlTiming(response);
    const valid = Number(sql?.[2]) === 1 && matchesOrderHistory(readBody(response, 200));

    if (valid) historySqlTime.add(Number(sql[1]), tags);
    else console.error('Order history validation failed (HTTP ' + response.status + ', SQL queries ' + (sql?.[2] ?? 'missing') + ').');

    return valid;
}

function waitForExport(id, rows, deadline, tags) {
    while (true) {
        const remaining = deadline - Date.now();
        if (remaining <= 0) return false;
        if (exportLab) exportPolls.add(1, tags);
        const response = getShop('/api/exports/' + id, '/api/exports/:id', Math.min(remaining, 2000) + 'ms');
        const status = readBody(response, 200);
        if (status?.id !== id) return false;
        if (status.state === 'ready') {
            const valid = status.rows === rows && Number.isFinite(status.elapsed_ms)
                && Number.isFinite(status.queue_wait_ms) && status.queue_wait_ms >= 0
                && Number.isFinite(status.processing_ms) && status.processing_ms >= 0;
            if (valid) {
                exportQueueWait.add(status.queue_wait_ms, tags);
                exportProcessing.add(status.processing_ms, tags);
            }
            return valid;
        }
        if (!['pending', 'running'].includes(status.state)) return false;
        sleep(Math.min(0.25, Math.max(0, deadline - Date.now()) / 1000));
    }
}

function exportProducts(reference, category, tags) {
    const start = Date.now();
    const accepted = readBody(postJson('/api/exports', {
        run_id: runId, reference, category_id: category,
    }), 202);
    const acceptedValid = Number.isInteger(accepted?.id) && accepted.state === 'pending';
    exportsAccepted.add(acceptedValid ? 1 : 0, tags);
    const ready = acceptedValid && waitForExport(accepted.id, 250, start + 70000, tags);

    const elapsed = Date.now() - start;
    exportsSeenReady.add(ready ? 1 : 0, tags);
    if (ready) exportLatency.add(elapsed, tags);
    exportOnTime.add(ready && elapsed <= 60000, tags);

    return ready;
}

function exportComparison(plan, reference, tags) {
    const sync = exportMode === 'sync';
    const start = Date.now();
    const deadline = start + 70000;
    const response = postJson(sync ? '/api/exports/sync' : '/api/exports', {
        run_id: runId, reference, category_id: plan.fixture.category,
    }, {}, '10s');
    exportInitial.add(response.timings.duration, tags);

    let file = response;
    if (!sync) {
        const accepted = readBody(response, 202);
        if (Number.isInteger(accepted?.id) && accepted.state === 'pending'
            && response.headers.Location === '/api/exports/' + accepted.id
            && waitForExport(accepted.id, plan.fixture.size, deadline, tags) && Date.now() < deadline) {
            const timeout = Math.min(deadline - Date.now(), 10000);
            file = getShop('/api/exports/' + accepted.id + '/file', '/api/exports/:id/file', Math.max(1, timeout) + 'ms');
            exportDownload.add(file.timings.duration, tags);
        }
    }

    const elapsed = Date.now() - start;
    const valid = file.status === 200 && /^text\/csv\b/i.test(file.headers['Content-Type'] || '')
        && typeof file.body === 'string' && sha256(file.body, 'hex') === plan.csvHash;
    exportFiles.add(valid ? 1 : 0, tags);
    exportOnTime.add(valid && elapsed <= 60000, tags);
    if (valid) exportFileWait.add(elapsed, tags);
    else console.error('Export ' + reference + ' in ' + exportMode + ' mode failed CSV validation (POST '
        + response.status + ', file ' + file.status + ').');
    return valid;
}

function record(workflow, phase, run) {
    const tags = { workflow, phase };
    workflowsStarted.add(1, tags);
    const success = run(tags);
    failure.add(!success, tags);
    workflowsSucceeded.add(success ? 1 : 0, tags);
}

export default function (plan) {
    if (profile === 'cold') {
        for (const sample of ['first', 'second']) {
            record('catalogue', profile, (tags) => {
                const result = preview ? cataloguePreview(tags) : catalogue(1, 1, tags);
                coldLatency.add(result.latency, { sample });
                return result.valid;
            });
        }
        return;
    }

    if (preview) {
        record('catalogue', profile, (tags) => cataloguePreview(tags).valid);
        return;
    }

    if (catalogueLab) {
        record('catalogue', profile, (tags) => catalogueComparison(plan, tags));
        return;
    }

    if (cacheLab) {
        record('catalogue-summary', profile, (tags) => catalogueCacheComparison(plan, tags));
        return;
    }

    const iteration = exec.scenario.iterationInTest;
    if (exportLab) {
        const slot = iteration % 10;
        const category = 1 + iteration % 3;
        const reference = String(iteration);
        if (slot === 1) {
            record('export', profile, (tags) => exportComparison(plan, reference, tags));
        } else if (slot === 4) {
            const product = (category - 1) * 250 + 1 + (iteration * 13) % 250;
            record('order', profile, (tags) => order(reference, product, 1 + iteration % 3, tags));
        } else {
            const page = 1 + Math.floor(iteration / 3) % 3;
            record('catalogue', profile, (tags) => catalogue(category, page, tags, 'projected').valid);
        }
        return;
    }

    if (ordersLab) {
        if (iteration % 5 === 4) {
            const product = 1 + (iteration * 29) % 500;
            record('order', profile, (tags) => order(String(iteration), product, 1 + iteration % 3, tags));
        } else {
            record('order-history', profile, orderHistory);
        }
        return;
    }

    const slot = iteration % 20;
    const phase = phaseForIteration();
    const category = iteration % 10 < 8 ? 1 + iteration % 3 : 1 + (iteration * 7) % 20;
    const reference = String(iteration);

    if (slot === 0) {
        record('export', phase, (tags) => exportProducts(reference, category, tags));
    } else if ([1, 7, 13].includes(slot)) {
        const product = (category - 1) * 250 + 1 + (iteration * 13) % 250;
        record('order', phase, (tags) => order(reference, product, 1 + iteration % 3, tags));
    } else {
        record('catalogue', phase, (tags) => catalogue(category, 1 + iteration % 3, tags).valid);
    }

    if (profile === 'closed') sleep(0.2);
}

export function handleSummary(data) {
    const sample = (metric, statistic) => data.metrics[metric]?.values?.[statistic];
    const printable = (value, digits = 0) => Number.isFinite(value) ? value.toFixed(digits) : 'n/a';
    const output = [
        'Workflows started: ' + printable(sample('shop_workflows_started', 'count')),
        'Successful workflows: ' + printable(sample('shop_workflows_succeeded', 'count')),
        'Dropped iterations: ' + printable(sample('dropped_iterations', 'count') ?? (thresholds.dropped_iterations ? 0 : undefined)),
    ];
    if (!ordersLab) {
        output.push((preview ? 'Catalogue preview' : 'Catalogue') + ' p95 (ms): ' + printable(sample('shop_catalogue_ms', 'p(95)'), 2));
    }

    if (ordersLab) {
        output.push('Order history p95 (ms): ' + printable(sample('shop_order_history_ms', 'p(95)'), 2));
        output.push('Order history SQL p50 (ms): ' + printable(sample('shop_order_history_sql_ms', 'med'), 2));
        output.push('Checkout p95 (ms): ' + printable(sample('shop_order_ms', 'p(95)'), 2));
        output.push('Checkout SQL p50 (ms): ' + printable(sample('shop_order_sql_ms', 'med'), 2));
        output.push('Orders confirmed: ' + printable(sample('shop_orders_confirmed', 'count')));
    } else if (profile === 'cold') {
        output.push('First response (ms): ' + printable(sample('shop_cold_catalogue_ms{sample:first}', 'med'), 2));
        output.push('Second response (ms): ' + printable(sample('shop_cold_catalogue_ms{sample:second}', 'med'), 2));
    } else if (catalogueLab) {
        output.push('Catalogue SQL p50 (ms): ' + printable(sample('shop_catalogue_sql_ms', 'med'), 2));
        output.push('Catalogue queries per request: ' + printable(sample('shop_catalogue_queries', 'med')));
        output.push('Catalogue response body (bytes): ' + printable(sample('shop_catalogue_body_bytes', 'med')));
    } else if (cacheLab) {
        output.push('Catalogue summary SQL p50 (ms): ' + printable(sample('shop_catalogue_sql_ms', 'med'), 2));
        output.push('Catalogue summary SQL queries: ' + printable(sample('shop_catalogue_cache_db_queries', 'count')));
        output.push('Cache hits: ' + printable(sample('shop_catalogue_cache_hits', 'count')));
        output.push('Cache refills: ' + printable(sample('shop_catalogue_cache_misses', 'count')));
        output.push('Requests served after waiting: ' + printable(sample('shop_catalogue_cache_coalesced', 'count')));
        output.push('Cache fallbacks: ' + printable(sample('shop_catalogue_cache_fallbacks', 'count')));
    } else if (exportLab) {
        output.push('Checkout p95 (ms): ' + printable(sample('shop_order_ms', 'p(95)'), 2));
        output.push('Export POST p95 (ms): ' + printable(sample('shop_export_initial_http_ms', 'p(95)'), 2));
        output.push('Export queue wait p95 (ms): ' + printable(sample('shop_export_queue_wait_ms', 'p(95)'), 2));
        output.push('Export generation p95 (ms): ' + printable(sample('shop_export_processing_ms', 'p(95)'), 2));
        output.push('Export file wait p95 (ms): ' + printable(sample('shop_export_file_wait_ms', 'p(95)'), 2));
        output.push('Export status requests: ' + printable(sample('shop_export_status_requests', 'count') ?? 0));
        output.push('Export files verified: ' + printable(sample('shop_export_files_verified', 'count')));
    } else if (!preview) {
        output.push('Order p95 (ms): ' + printable(sample('shop_order_ms', 'p(95)'), 2));
        output.push('Exports accepted: ' + printable(sample('shop_exports_accepted', 'count')));
        output.push('Exports seen ready: ' + printable(sample('shop_exports_seen_ready', 'count')));
    }

    return {
        stdout: output.join('\n') + '\n',
        '/results/summary.json': JSON.stringify(data, null, 2) + '\n',
    };
}
