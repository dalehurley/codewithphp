<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Chart', ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .download-buttons {
            margin: 15px 0;
            text-align: right;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn:focus {
            outline: 2px solid #2980b9;
            outline-offset: 2px;
        }
        
        /* Screen reader only content */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
            border: 0;
        }
        
        /* Tablet and below */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .container {
                padding: 15px;
            }
            .chart-container {
                height: 300px;
            }
            .stats {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
            }
            h1 {
                font-size: 1.5em;
            }
            .download-buttons {
                text-align: center;
            }
            .btn {
                margin: 5px;
            }
        }
        
        /* Mobile */
        @media (max-width: 480px) {
            .container {
                padding: 10px;
                border-radius: 0;
            }
            .chart-container {
                height: 250px;
                margin: 10px 0;
            }
            .stats {
                grid-template-columns: 1fr;
            }
            .stat-card {
                padding: 15px;
            }
            .stat-value {
                font-size: 1.5em;
            }
        }
        
        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
            }
            .chart-container {
                page-break-inside: avoid;
            }
            .download-buttons, .no-print {
                display: none !important;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: #1a1a1a;
                color: #e0e0e0;
            }
            .container {
                background: #2d2d2d;
                box-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }
            h1 {
                color: #e0e0e0;
                border-bottom-color: #667eea;
            }
            .stat-card {
                background: #3d3d3d;
                border-left-color: #667eea;
            }
            .stat-value {
                color: #e0e0e0;
            }
            .stat-label {
                color: #95a5a6;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($title ?? 'Data Visualization', ENT_QUOTES, 'UTF-8') ?></h1>
        
        <?php if (!empty($stats)): ?>
        <div class="stats">
            <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <div class="stat-value"><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="stat-label"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Accessible chart container with ARIA labels -->
        <div class="chart-container" 
             role="img" 
             aria-label="<?= htmlspecialchars($chartDescription ?? $title ?? 'Data chart', ENT_QUOTES, 'UTF-8') ?>">
            <canvas id="myChart" 
                    role="img"
                    aria-label="<?= htmlspecialchars($chartDescription ?? $title ?? 'Data chart', ENT_QUOTES, 'UTF-8') ?>"></canvas>
            
            <!-- Screen reader fallback description -->
            <?php if (!empty($accessibleDescription)): ?>
            <div class="sr-only" id="chart-description">
                <?= htmlspecialchars($accessibleDescription, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Download buttons -->
        <div class="download-buttons no-print">
            <button onclick="downloadChartPNG()" class="btn" aria-label="Download chart as PNG image">
                💾 Download PNG
            </button>
            <button onclick="downloadChartSVG()" class="btn" aria-label="Download chart as SVG image">
                💾 Download SVG
            </button>
        </div>
        
        <?php if (!empty($description)): ?>
        <div class="description">
            <h3>Insights</h3>
            <p><?= nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        const chartConfig = <?= $chartJson ?>;
        const myChart = new Chart(ctx, chartConfig);
        
        /**
         * Download chart as PNG image
         */
        function downloadChartPNG() {
            const canvas = document.getElementById('myChart');
            const url = canvas.toDataURL('image/png');
            
            const link = document.createElement('a');
            link.download = 'chart-' + Date.now() + '.png';
            link.href = url;
            link.click();
        }
        
        /**
         * Download chart as SVG image (via canvas conversion)
         * Note: This converts canvas to PNG then embeds in SVG for compatibility
         */
        function downloadChartSVG() {
            const canvas = document.getElementById('myChart');
            const imgData = canvas.toDataURL('image/png');
            
            const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
     width="${canvas.width}" height="${canvas.height}" viewBox="0 0 ${canvas.width} ${canvas.height}">
    <image xlink:href="${imgData}" width="${canvas.width}" height="${canvas.height}"/>
</svg>`;
            
            const blob = new Blob([svg], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.download = 'chart-' + Date.now() + '.svg';
            link.href = url;
            link.click();
            
            URL.revokeObjectURL(url);
        }
        
        /**
         * Keyboard navigation support
         */
        document.addEventListener('keydown', function(e) {
            // Add keyboard shortcuts for download
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 's') {
                    e.preventDefault();
                    downloadChartPNG();
                }
            }
        });
    </script>
</body>
</html>
