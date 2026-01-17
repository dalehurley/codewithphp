<?php

declare(strict_types=1);

namespace DataScience\Visualization;

/**
 * LiveDashboard - Real-time dashboard with AJAX updates
 * 
 * Extends DashboardGenerator to support live data updates without page reload.
 * Updates charts and stats via AJAX calls to a data endpoint.
 */
class LiveDashboard extends DashboardGenerator
{
    /**
     * Generate dashboard with AJAX live updates
     * 
     * @param string $title Dashboard title
     * @param string $dataEndpoint API endpoint that returns JSON with updated data
     * @param int $updateInterval Update interval in seconds (default: 30)
     * @return string Complete HTML with AJAX update functionality
     */
    public function generateLive(
        string $title, 
        string $dataEndpoint, 
        int $updateInterval = 30
    ): string {
        // Generate base dashboard HTML
        $html = parent::generate($title, refreshInterval: 0);
        
        $safeEndpoint = htmlspecialchars($dataEndpoint, ENT_QUOTES, 'UTF-8');
        $safeInterval = max(5, $updateInterval); // Minimum 5 seconds
        
        // Add AJAX update script before closing body tag
        $ajaxScript = <<<JS
    <script>
        // Store last update status
        let updateStatus = document.createElement('div');
        updateStatus.id = 'updateStatus';
        updateStatus.style.cssText = 'position: fixed; top: 10px; right: 10px; padding: 10px; background: #27ae60; color: white; border-radius: 4px; font-size: 12px; display: none;';
        document.body.appendChild(updateStatus);
        
        /**
         * Update dashboard data via AJAX
         */
        async function updateDashboard() {
            try {
                // Show loading indicator
                updateStatus.textContent = '🔄 Updating...';
                updateStatus.style.background = '#3498db';
                updateStatus.style.display = 'block';
                
                const response = await fetch('{$safeEndpoint}');
                
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                
                const data = await response.json();
                
                // Update stat cards
                if (data.stats) {
                    Object.keys(data.stats).forEach(statId => {
                        const element = document.getElementById('stat-' + statId);
                        if (element) {
                            const valueEl = element.querySelector('.stat-value');
                            const trendEl = element.querySelector('.stat-trend');
                            
                            if (valueEl && data.stats[statId].value !== undefined) {
                                valueEl.textContent = data.stats[statId].value;
                            }
                            
                            if (trendEl && data.stats[statId].trend) {
                                trendEl.textContent = data.stats[statId].trend;
                            }
                        }
                    });
                }
                
                // Update charts
                if (data.charts) {
                    Object.keys(data.charts).forEach(chartId => {
                        if (charts[chartId]) {
                            const chartData = data.charts[chartId];
                            
                            // Update datasets
                            if (chartData.datasets) {
                                charts[chartId].data.datasets.forEach((dataset, i) => {
                                    if (chartData.datasets[i] && chartData.datasets[i].data) {
                                        dataset.data = chartData.datasets[i].data;
                                    }
                                });
                            }
                            
                            // Update labels if provided
                            if (chartData.labels) {
                                charts[chartId].data.labels = chartData.labels;
                            }
                            
                            // Update without animation for smoothness
                            charts[chartId].update('none');
                        }
                    });
                }
                
                // Update table data
                if (data.tables) {
                    Object.keys(data.tables).forEach(tableId => {
                        const table = document.getElementById('table-' + tableId);
                        if (table) {
                            const tbody = table.querySelector('tbody');
                            if (tbody && data.tables[tableId].rows) {
                                let rowsHtml = '';
                                data.tables[tableId].rows.forEach(row => {
                                    rowsHtml += '<tr>';
                                    row.forEach(cell => {
                                        rowsHtml += '<td>' + escapeHtml(String(cell)) + '</td>';
                                    });
                                    rowsHtml += '</tr>';
                                });
                                tbody.innerHTML = rowsHtml;
                            }
                        }
                    });
                }
                
                // Update timestamp
                document.getElementById('lastUpdate').textContent = new Date().toLocaleString();
                
                // Show success
                updateStatus.textContent = '✓ Updated';
                updateStatus.style.background = '#27ae60';
                setTimeout(() => {
                    updateStatus.style.display = 'none';
                }, 2000);
                
            } catch (error) {
                console.error('Dashboard update failed:', error);
                updateStatus.textContent = '⚠️ Update failed';
                updateStatus.style.background = '#e74c3c';
                
                // Hide error after 5 seconds
                setTimeout(() => {
                    updateStatus.style.display = 'none';
                }, 5000);
            }
        }
        
        /**
         * Escape HTML to prevent XSS in dynamic content
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Update every {$safeInterval} seconds
        setInterval(updateDashboard, {$safeInterval} * 1000);
        
        // Update immediately on load
        setTimeout(updateDashboard, 1000);
        
        // Add manual refresh button
        const refreshBtn = document.createElement('button');
        refreshBtn.textContent = '🔄 Refresh Now';
        refreshBtn.className = 'btn btn-primary no-print';
        refreshBtn.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 1000;';
        refreshBtn.onclick = updateDashboard;
        document.body.appendChild(refreshBtn);
    </script>
JS;
        
        // Insert before closing body tag
        $html = str_replace('</body>', $ajaxScript . "\n</body>", $html);
        
        return $html;
    }
    
    /**
     * Helper method to add stat cards with IDs for AJAX updates
     */
    public function addLiveStatCard(
        string $id,
        string $title,
        string $value,
        string $trend = '',
        string $icon = '📊',
        int $width = 3
    ): self {
        // Call parent method
        parent::addStatCard($title, $value, $trend, $icon, $width);
        
        // Modify last widget to include ID for AJAX targeting
        $lastIndex = count($this->widgets) - 1;
        $this->widgets[$lastIndex]['id'] = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        
        return $this;
    }
}
