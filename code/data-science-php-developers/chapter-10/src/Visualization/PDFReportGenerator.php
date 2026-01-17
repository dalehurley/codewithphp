<?php

declare(strict_types=1);

namespace DataScience\Visualization;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * PDFReportGenerator - Generate professional PDF reports with security
 * 
 * Creates PDF reports from HTML with proper input sanitization to prevent XSS.
 * All user input is escaped unless explicitly allowed with whitelist.
 */
class PDFReportGenerator
{
    private Dompdf $dompdf;
    private string $html = '';
    
    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $this->dompdf = new Dompdf($options);
    }
    
    /**
     * Start building report
     */
    public function startReport(string $title, string $subtitle = ''): self
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeSubtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
        $date = date('F j, Y');
        
        $this->html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        h1 {
            color: #2c3e50;
            margin: 0;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 10px;
        }
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #34495e;
            border-left: 4px solid #3498db;
            padding-left: 10px;
            margin: 20px 0 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background: #3498db;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ecf0f1;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .stat-grid {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        .stat-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ecf0f1;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .insight-box {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 15px 0;
        }
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            color: #95a5a6;
            font-size: 10px;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$safeTitle}</h1>
        <div class="subtitle">{$safeSubtitle}<br>Generated on {$date}</div>
    </div>
HTML;
        
        return $this;
    }
    
    /**
     * Add section heading
     */
    public function addSection(string $title): self
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $this->html .= "<div class=\"section-title\">{$safeTitle}</div>\n";
        return $this;
    }
    
    /**
     * Add paragraph text (SECURE VERSION with sanitization)
     * 
     * @param string $text The text to add
     * @param bool $allowHTML If true, allows safe HTML tags; if false, escapes all HTML
     */
    public function addText(string $text, bool $allowHTML = false): self
    {
        if ($allowHTML) {
            // Strip dangerous tags, keep safe formatting tags
            $text = strip_tags($text, '<strong><em><u><br><ul><ol><li><p>');
        } else {
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
        
        $this->html .= "<p>{$text}</p>\n";
        return $this;
    }
    
    /**
     * Add stat cards (SECURE VERSION)
     */
    public function addStats(array $stats): self
    {
        $this->html .= "<div class=\"stat-grid\">\n<div class=\"stat-row\">\n";
        
        foreach ($stats as $stat) {
            $value = htmlspecialchars((string)$stat['value'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars((string)$stat['label'], ENT_QUOTES, 'UTF-8');
            
            $this->html .= <<<HTML
    <div class="stat-cell">
        <div class="stat-value">{$value}</div>
        <div class="stat-label">{$label}</div>
    </div>
HTML;
        }
        
        $this->html .= "</div>\n</div>\n";
        return $this;
    }
    
    /**
     * Add table (SECURE VERSION with escaping)
     */
    public function addTable(array $headers, array $rows): self
    {
        if (empty($headers)) {
            throw new \InvalidArgumentException('Table headers cannot be empty');
        }
        
        $this->html .= "<table>\n<thead><tr>\n";
        
        foreach ($headers as $header) {
            $safeHeader = htmlspecialchars((string)$header, ENT_QUOTES, 'UTF-8');
            $this->html .= "<th>{$safeHeader}</th>";
        }
        
        $this->html .= "</tr></thead>\n<tbody>\n";
        
        foreach ($rows as $row) {
            // Warn if row doesn't match header count
            if (count($row) !== count($headers)) {
                trigger_error('Row column count does not match headers', E_USER_WARNING);
            }
            
            $this->html .= "<tr>";
            foreach ($row as $cell) {
                $safeCell = htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8');
                $this->html .= "<td>{$safeCell}</td>";
            }
            $this->html .= "</tr>\n";
        }
        
        $this->html .= "</tbody>\n</table>\n";
        return $this;
    }
    
    /**
     * Add insight box (green)
     */
    public function addInsight(string $text): self
    {
        $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $this->html .= "<div class=\"insight-box\">✓ <strong>Insight:</strong> {$safeText}</div>\n";
        return $this;
    }
    
    /**
     * Add warning box (orange)
     */
    public function addWarning(string $text): self
    {
        $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $this->html .= "<div class=\"warning-box\">⚠ <strong>Warning:</strong> {$safeText}</div>\n";
        return $this;
    }
    
    /**
     * Add chart image (must be base64 encoded)
     */
    public function addChartImage(string $base64Image, string $caption = ''): self
    {
        // Validate base64 format
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $base64Image)) {
            throw new \InvalidArgumentException('Invalid base64 image data');
        }
        
        $safeCaption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');
        
        $this->html .= "<div style=\"text-align: center; margin: 20px 0;\">\n";
        $this->html .= "<img src=\"data:image/png;base64,{$base64Image}\" style=\"max-width: 100%;\">\n";
        if ($caption) {
            $this->html .= "<div style=\"font-size: 12px; color: #7f8c8d; margin-top: 5px;\">{$safeCaption}</div>\n";
        }
        $this->html .= "</div>\n";
        return $this;
    }
    
    /**
     * Add page break
     */
    public function addPageBreak(): self
    {
        $this->html .= "<div class=\"page-break\"></div>\n";
        return $this;
    }
    
    /**
     * Generate and save PDF
     */
    public function savePDF(string $filename): void
    {
        // Validate filename
        if (empty($filename)) {
            throw new \InvalidArgumentException('Filename cannot be empty');
        }
        
        // Ensure directory exists
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Cannot create directory: {$dir}");
            }
        }
        
        $this->html .= <<<HTML
    <div class="footer">
        Generated by Data Science Reporting System<br>
        Report Date: {$this->getCurrentTime()}
    </div>
</body>
</html>
HTML;
        
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        
        $output = $this->dompdf->output();
        
        if (file_put_contents($filename, $output) === false) {
            throw new \RuntimeException("Failed to write PDF to: {$filename}");
        }
    }
    
    /**
     * Output PDF to browser
     */
    public function streamPDF(string $filename): void
    {
        if (empty($filename)) {
            throw new \InvalidArgumentException('Filename cannot be empty');
        }
        
        $this->html .= "</body></html>";
        
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        $this->dompdf->stream($filename);
    }
    
    /**
     * Get current timestamp
     */
    private function getCurrentTime(): string
    {
        return date('Y-m-d H:i:s');
    }
}
