<?php

declare(strict_types=1);

require_once __DIR__ . '/../07-metrics-collector.php';

$redis = new Redis();
$redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

$metrics = new MetricsCollector($redis);

// Get health status
$healthResponse = @file_get_contents('http://localhost/health');
$health = $healthResponse ? json_decode($healthResponse, true) : null;

// Get metrics
$snapshot = $metrics->getSnapshot();
$modelMetrics = $metrics->getModelMetrics('classifier-v1');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI-ML Service Dashboard</title>
    <meta http-equiv="refresh" content="5">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
        }

        .card {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-healthy {
            background: #d4edda;
            color: #155724;
        }

        .status-degraded {
            background: #fff3cd;
            color: #856404;
        }

        .status-unhealthy {
            background: #f8d7da;
            color: #721c24;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .metric {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #3498db;
        }

        .metric-value {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .metric-label {
            color: #7f8c8d;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .warning {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 12px;
            margin: 10px 0;
            border-radius: 4px;
            color: #856404;
        }

        .footer {
            text-align: center;
            color: #95a5a6;
            font-size: 13px;
            margin-top: 20px;
            padding: 15px;
        }

        .refresh-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #2ecc71;
            border-radius: 50%;
            margin-right: 5px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #7f8c8d;
            font-size: 12px;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>🤖 AI-ML Service Dashboard</h1>
            <p class="subtitle">
                <span class="refresh-indicator"></span>
                Real-time monitoring • Auto-refreshes every 5 seconds
            </p>
        </header>

        <?php if ($health): ?>
            <!-- System Health -->
            <div class="card">
                <h2>
                    System Health
                    <span class="status-badge status-<?= $health['status'] ?>">
                        <?= strtoupper($health['status']) ?>
                    </span>
                </h2>

                <div class="metrics-grid">
                    <div class="metric">
                        <div class="metric-value"><?= $health['system']['active_workers'] ?></div>
                        <div class="metric-label">Active Workers</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= $health['queue']['depth'] ?></div>
                        <div class="metric-label">Queue Depth</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= $health['cache']['hit_rate'] ?>%</div>
                        <div class="metric-label">Cache Hit Rate</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= number_format($health['system']['total_processed']) ?></div>
                        <div class="metric-label">Total Processed</div>
                    </div>
                </div>

                <?php if (!empty($health['warnings'])): ?>
                    <div class="warning">
                        ⚠️ <?= implode(' • ', $health['warnings']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Performance Metrics -->
            <div class="card">
                <h2>📊 Performance Metrics</h2>

                <div class="metrics-grid">
                    <div class="metric">
                        <div class="metric-value"><?= $snapshot['requests']['per_minute'] ?></div>
                        <div class="metric-label">Requests/Minute</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= $snapshot['performance']['avg_latency_ms'] ?>ms</div>
                        <div class="metric-label">Avg Latency</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= number_format($snapshot['requests']['total']) ?></div>
                        <div class="metric-label">Total Requests</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= $snapshot['requests']['cached_percent'] ?>%</div>
                        <div class="metric-label">Cached Requests</div>
                    </div>
                </div>

                <?php if (!empty($snapshot['top_endpoints'])): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Top Endpoints</th>
                                <th style="text-align: right;">Requests</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($snapshot['top_endpoints'] as $endpoint => $count): ?>
                                <tr>
                                    <td><?= htmlspecialchars($endpoint) ?></td>
                                    <td style="text-align: right;"><?= number_format($count) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Model Metrics -->
            <div class="card">
                <h2>🧠 Model Performance: <?= htmlspecialchars($modelMetrics['model']) ?></h2>

                <div class="metrics-grid">
                    <div class="metric">
                        <div class="metric-value"><?= number_format($modelMetrics['total_predictions']) ?></div>
                        <div class="metric-label">Predictions</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= $modelMetrics['avg_inference_time_ms'] ?>ms</div>
                        <div class="metric-label">Avg Inference Time</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= $modelMetrics['error_rate'] ?>%</div>
                        <div class="metric-label">Error Rate</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?= number_format($modelMetrics['errors']) ?></div>
                        <div class="metric-label">Total Errors</div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="card">
                <div class="warning">
                    ⚠️ Unable to connect to health endpoint. Check if the service is running.
                </div>
            </div>
        <?php endif; ?>

        <div class="footer">
            Last updated: <?= date('Y-m-d H:i:s') ?> •
            Refresh in <span id="countdown">5</span> seconds
        </div>
    </div>

    <script>
        // Countdown timer
        let seconds = 5;
        const countdown = document.getElementById('countdown');
        setInterval(() => {
            seconds--;
            if (seconds <= 0) seconds = 5;
            if (countdown) countdown.textContent = seconds;
        }, 1000);
    </script>
</body>

</html>