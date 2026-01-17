<?php

declare(strict_types=1);

namespace DataScience\Visualization;

/**
 * ChartExporter - Convert Chart.js charts to static images
 * 
 * Exports charts as PNG images for embedding in PDFs and offline viewing.
 * Requires Node.js and Puppeteer for headless browser rendering.
 */
class ChartExporter
{
    private string $tempDir;
    private string $nodeScript;
    
    public function __construct(string $tempDir = 'temp/charts')
    {
        $this->tempDir = $tempDir;
        $this->nodeScript = __DIR__ . '/../../scripts/capture-chart.js';
        
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                throw new \RuntimeException("Cannot create temp directory: {$tempDir}");
            }
        }
    }
    
    /**
     * Convert Chart.js configuration to PNG image
     * 
     * @param array $chartConfig Chart.js configuration array
     * @param int $width Image width in pixels
     * @param int $height Image height in pixels
     * @return string Base64-encoded PNG image data
     * @throws \RuntimeException if capture fails or dependencies missing
     */
    public function chartToPng(array $chartConfig, int $width = 800, int $height = 400): string
    {
        // Check Node.js availability
        exec('which node', $output, $returnCode);
        if ($returnCode !== 0) {
            throw new \RuntimeException(
                'Node.js is required for chart export. Please install Node.js 18+ and Puppeteer.'
            );
        }
        
        // Check if capture script exists
        if (!file_exists($this->nodeScript)) {
            throw new \RuntimeException(
                "Chart capture script not found: {$this->nodeScript}\n" .
                "Please ensure scripts/capture-chart.js is present."
            );
        }
        
        $chartBuilder = new ChartBuilder();
        $chartJson = $chartBuilder->toJson($chartConfig);
        
        // Create temporary HTML file
        $html = $this->generateChartHTML($chartJson, $width, $height);
        $htmlFile = $this->tempDir . '/chart-' . uniqid() . '.html';
        
        if (file_put_contents($htmlFile, $html) === false) {
            throw new \RuntimeException("Cannot write temporary HTML file: {$htmlFile}");
        }
        
        // Use puppeteer to render and capture
        $imageFile = str_replace('.html', '.png', $htmlFile);
        
        // Execute Node.js script to capture chart
        $command = sprintf(
            'node %s %s %s %d %d 2>&1',
            escapeshellarg($this->nodeScript),
            escapeshellarg($htmlFile),
            escapeshellarg($imageFile),
            $width,
            $height
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($imageFile)) {
            // Clean up temp file
            @unlink($htmlFile);
            throw new \RuntimeException(
                'Chart capture failed. ' . 
                'Ensure Puppeteer is installed: npm install puppeteer' . "\n" .
                'Error: ' . implode("\n", $output)
            );
        }
        
        // Read image and convert to base64
        $imageData = file_get_contents($imageFile);
        
        if ($imageData === false) {
            throw new \RuntimeException("Cannot read captured image: {$imageFile}");
        }
        
        $base64 = base64_encode($imageData);
        
        // Clean up temporary files
        @unlink($htmlFile);
        @unlink($imageFile);
        
        return $base64;
    }
    
    /**
     * Generate standalone HTML for chart rendering
     */
    private function generateChartHTML(string $chartJson, int $width, int $height): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { margin: 0; padding: 20px; background: white; }
        #chartContainer { width: {$width}px; height: {$height}px; }
    </style>
</head>
<body>
    <div id="chartContainer">
        <canvas id="chart"></canvas>
    </div>
    <script>
        const ctx = document.getElementById('chart').getContext('2d');
        const config = {$chartJson};
        new Chart(ctx, config);
    </script>
</body>
</html>
HTML;
    }
    
    /**
     * Export chart and save to file
     */
    public function exportToFile(array $chartConfig, string $outputPath, int $width = 800, int $height = 400): void
    {
        $base64 = $this->chartToPng($chartConfig, $width, $height);
        $imageData = base64_decode($base64);
        
        // Ensure directory exists
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Cannot create directory: {$dir}");
            }
        }
        
        if (file_put_contents($outputPath, $imageData) === false) {
            throw new \RuntimeException("Cannot write image to: {$outputPath}");
        }
    }
    
    /**
     * Clean up temporary directory
     */
    public function cleanup(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}
