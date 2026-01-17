<?php

declare(strict_types=1);

namespace DataScience\Visualization;

/**
 * DashboardGenerator - Build interactive dashboards with security
 * 
 * Generates responsive HTML dashboards with multiple widget types.
 * All output is properly escaped to prevent XSS attacks.
 */
class DashboardGenerator
{
    private ChartBuilder $chartBuilder;
    private array $widgets = [];
    
    public function __construct()
    {
        $this->chartBuilder = new ChartBuilder();
    }
    
    /**
     * Add chart widget to dashboard
     */
    public function addChart(
        string $id,
        string $title,
        array $chartConfig,
        int $width = 6
    ): self {
        $this->widgets[] = [
            'type' => 'chart',
            'id' => htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'config' => $chartConfig,
            'width' => max(1, min(12, $width)), // Validate width 1-12
        ];
        
        return $this;
    }
    
    /**
     * Add stat card widget (SECURE VERSION with XSS protection)
     */
    public function addStatCard(
        string $title,
        string $value,
        string $trend = '',
        string $icon = '📊',
        int $width = 3
    ): self {
        $this->widgets[] = [
            'type' => 'stat',
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'value' => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            'trend' => htmlspecialchars($trend, ENT_QUOTES, 'UTF-8'),
            'icon' => htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'),
            'width' => max(1, min(12, $width)),
        ];
        
        return $this;
    }
    
    /**
     * Add table widget (SECURE VERSION with XSS protection)
     */
    public function addTable(
        string $title,
        array $headers,
        array $rows,
        int $width = 12
    ): self {
        $this->widgets[] = [
            'type' => 'table',
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'headers' => array_map(fn($h) => htmlspecialchars((string)$h, ENT_QUOTES, 'UTF-8'), $headers),
            'rows' => array_map(fn($row) => array_map(fn($cell) => htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8'), $row), $rows),
            'width' => max(1, min(12, $width)),
        ];
        
        return $this;
    }
    
    /**
     * Add filter widget for interactive filtering
     */
    public function addFilter(
        string $id,
        string $label,
        array $options,
        int $width = 3
    ): self {
        $this->widgets[] = [
            'type' => 'filter',
            'id' => htmlspecialchars($id, ENT_QUOTES, 'UTF-8'),
            'label' => htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
            'options' => array_map(fn($k, $v) => [
                'value' => htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8'),
                'label' => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8')
            ], array_keys($options), $options),
            'width' => max(1, min(12, $width)),
        ];
        
        return $this;
    }
    
    /**
     * Generate complete dashboard HTML with responsive design
     */
    public function generate(
        string $title,
        int $refreshInterval = 0
    ): string {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $currentTime = htmlspecialchars($this->getCurrentTime(), ENT_QUOTES, 'UTF-8');
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeTitle}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { 
            background: #f8f9fa; 
            padding: 20px; 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .widget {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card {
            text-align: center;
            padding: 30px 20px;
        }
        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .stat-trend {
            font-size: 0.85em;
            margin-top: 10px;
        }
        .trend-up { color: #27ae60; }
        .trend-down { color: #e74c3c; }
        .widget-title {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 15px;
            color: #34495e;
        }
        .last-updated {
            color: #95a5a6;
            font-size: 0.85em;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Tablet and below */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .dashboard-header {
                padding: 20px;
            }
            .widget {
                padding: 15px;
            }
            .chart-container {
                height: 250px;
            }
            .stat-card {
                padding: 20px 15px;
            }
            .stat-value {
                font-size: 2em;
            }
        }
        
        /* Mobile */
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            .dashboard-header {
                padding: 15px;
                border-radius: 0;
            }
            .widget {
                padding: 10px;
                border-radius: 4px;
            }
            .chart-container {
                height: 200px;
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
            .widget {
                box-shadow: none;
                page-break-inside: avoid;
            }
            .no-print {
                display: none;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: #1a1a1a;
                color: #e0e0e0;
            }
            .widget {
                background: #2d2d2d;
                box-shadow: 0 2px 4px rgba(0,0,0,0.5);
            }
            .stat-card {
                background: #2d2d2d;
            }
            .stat-value {
                color: #e0e0e0;
            }
            .widget-title {
                color: #e0e0e0;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-header">
            <h1>{$safeTitle}</h1>
            <p class="last-updated mb-0">Last updated: <span id="lastUpdate">{$currentTime}</span></p>
        </div>
        
        <div class="row">
HTML;
        
        foreach ($this->widgets as $widget) {
            $html .= $this->renderWidget($widget);
        }
        
        $html .= <<<HTML
        </div>
    </div>
    
    <script>
        const charts = {};
        
HTML;
        
        // Initialize charts
        foreach ($this->widgets as $widget) {
            if ($widget['type'] === 'chart') {
                $chartJson = $this->chartBuilder->toJson($widget['config']);
                $html .= <<<JS
        charts['{$widget['id']}'] = new Chart(
            document.getElementById('{$widget['id']}').getContext('2d'),
            {$chartJson}
        );
        
JS;
            }
        }
        
        // Add auto-refresh
        if ($refreshInterval > 0) {
            $html .= <<<JS
        
        // Auto-refresh every {$refreshInterval} seconds
        setInterval(function() {
            location.reload();
        }, {$refreshInterval} * 1000);
        
        // Update timestamp
        setInterval(function() {
            document.getElementById('lastUpdate').textContent = new Date().toLocaleString();
        }, 1000);
        
JS;
        }
        
        $html .= <<<HTML
    </script>
</body>
</html>
HTML;
        
        return $html;
    }
    
    /**
     * Render individual widget (SECURE - all data pre-escaped)
     */
    private function renderWidget(array $widget): string
    {
        $width = $widget['width'];
        $html = "<div class=\"col-md-{$width}\">\n";
        
        switch ($widget['type']) {
            case 'chart':
                $html .= $this->renderChartWidget($widget);
                break;
            case 'stat':
                $html .= $this->renderStatWidget($widget);
                break;
            case 'table':
                $html .= $this->renderTableWidget($widget);
                break;
            case 'filter':
                $html .= $this->renderFilterWidget($widget);
                break;
        }
        
        $html .= "</div>\n";
        return $html;
    }
    
    /**
     * Render chart widget
     */
    private function renderChartWidget(array $widget): string
    {
        return <<<HTML
<div class="widget">
    <div class="widget-title">{$widget['title']}</div>
    <div class="chart-container">
        <canvas id="{$widget['id']}"></canvas>
    </div>
</div>
HTML;
    }
    
    /**
     * Render stat card widget (SECURE - data pre-escaped)
     */
    private function renderStatWidget(array $widget): string
    {
        $trendClass = '';
        $trendIcon = '';
        
        if ($widget['trend']) {
            if (str_contains($widget['trend'], '+')) {
                $trendClass = 'trend-up';
                $trendIcon = '↑';
            } elseif (str_contains($widget['trend'], '-')) {
                $trendClass = 'trend-down';
                $trendIcon = '↓';
            }
        }
        
        return <<<HTML
<div class="widget stat-card">
    <div style="font-size: 2em; margin-bottom: 10px;">{$widget['icon']}</div>
    <div class="stat-value">{$widget['value']}</div>
    <div class="stat-label">{$widget['title']}</div>
    {$this->renderTrend($widget['trend'], $trendClass, $trendIcon)}
</div>
HTML;
    }
    
    /**
     * Render trend indicator
     */
    private function renderTrend(string $trend, string $class, string $icon): string
    {
        if (!$trend) {
            return '';
        }
        
        return "<div class=\"stat-trend {$class}\">{$icon} {$trend}</div>";
    }
    
    /**
     * Render table widget (SECURE - data pre-escaped)
     */
    private function renderTableWidget(array $widget): string
    {
        $html = <<<HTML
<div class="widget">
    <div class="widget-title">{$widget['title']}</div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
HTML;
        
        foreach ($widget['headers'] as $header) {
            $html .= "<th>{$header}</th>";
        }
        
        $html .= <<<HTML
                </tr>
            </thead>
            <tbody>
HTML;
        
        foreach ($widget['rows'] as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>{$cell}</td>";
            }
            $html .= "</tr>\n";
        }
        
        $html .= <<<HTML
            </tbody>
        </table>
    </div>
</div>
HTML;
        
        return $html;
    }
    
    /**
     * Render filter widget (SECURE - data pre-escaped)
     */
    private function renderFilterWidget(array $widget): string
    {
        $optionsHtml = '';
        foreach ($widget['options'] as $option) {
            $optionsHtml .= sprintf(
                '<option value="%s">%s</option>',
                $option['value'],
                $option['label']
            );
        }
        
        return <<<HTML
<div class="widget">
    <label for="{$widget['id']}" class="form-label">{$widget['label']}</label>
    <select id="{$widget['id']}" class="form-control" onchange="applyFilter(this)">
        {$optionsHtml}
    </select>
</div>
HTML;
    }
    
    /**
     * Get current timestamp
     */
    private function getCurrentTime(): string
    {
        return date('Y-m-d H:i:s');
    }
}
