<?php

declare(strict_types=1);

namespace DataScience\Visualization;

/**
 * DataExporter - Export data in multiple formats with robust error handling
 * 
 * Provides secure data export with validation, error handling, and resource cleanup.
 */
class DataExporter
{
    /**
     * Export to CSV (SECURE VERSION with error handling)
     */
    public function toCSV(array $headers, array $rows, string $filename): void
    {
        // Validate inputs
        if (empty($headers)) {
            throw new \InvalidArgumentException('Headers cannot be empty');
        }
        
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
        
        // Open file with error handling
        $handle = fopen($filename, 'w');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        try {
            // Write headers
            if (fputcsv($handle, $headers) === false) {
                throw new \RuntimeException("Failed to write CSV headers");
            }
            
            // Write rows with validation
            foreach ($rows as $rowIndex => $row) {
                if (count($row) !== count($headers)) {
                    trigger_error(
                        "Row {$rowIndex} column count does not match headers", 
                        E_USER_WARNING
                    );
                }
                
                if (fputcsv($handle, $row) === false) {
                    throw new \RuntimeException("Failed to write CSV row {$rowIndex}");
                }
            }
        } finally {
            fclose($handle);
        }
    }
    
    /**
     * Export to JSON (SECURE VERSION with validation)
     */
    public function toJSON(array $data, string $filename): void
    {
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
        
        $json = json_encode($data, JSON_PRETTY_PRINT);
        
        if ($json === false) {
            throw new \RuntimeException('Failed to encode data as JSON: ' . json_last_error_msg());
        }
        
        if (file_put_contents($filename, $json) === false) {
            throw new \RuntimeException("Failed to write JSON to: {$filename}");
        }
    }
    
    /**
     * Export to Excel-compatible CSV (SECURE VERSION)
     */
    public function toExcelCSV(array $headers, array $rows, string $filename): void
    {
        // Validate inputs
        if (empty($headers)) {
            throw new \InvalidArgumentException('Headers cannot be empty');
        }
        
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
        
        $handle = fopen($filename, 'w');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        try {
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            if (fputcsv($handle, $headers) === false) {
                throw new \RuntimeException("Failed to write CSV headers");
            }
            
            // Write rows
            foreach ($rows as $rowIndex => $row) {
                if (count($row) !== count($headers)) {
                    trigger_error(
                        "Row {$rowIndex} column count does not match headers", 
                        E_USER_WARNING
                    );
                }
                
                if (fputcsv($handle, $row) === false) {
                    throw new \RuntimeException("Failed to write CSV row {$rowIndex}");
                }
            }
        } finally {
            fclose($handle);
        }
    }
    
    /**
     * Export to HTML table (SECURE VERSION with XSS protection)
     */
    public function toHTML(array $headers, array $rows, string $title = 'Data Export'): string
    {
        if (empty($headers)) {
            throw new \InvalidArgumentException('Headers cannot be empty');
        }
        
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$safeTitle}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #3498db; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>{$safeTitle}</h1>
    <table>
        <thead><tr>
HTML;
        
        foreach ($headers as $header) {
            $safeHeader = htmlspecialchars((string)$header, ENT_QUOTES, 'UTF-8');
            $html .= "<th>{$safeHeader}</th>";
        }
        
        $html .= "</tr></thead>\n<tbody>\n";
        
        foreach ($rows as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $safeCell = htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8');
                $html .= "<td>{$safeCell}</td>";
            }
            $html .= "</tr>\n";
        }
        
        $html .= "</tbody>\n</table>\n</body>\n</html>";
        
        return $html;
    }
    
    /**
     * Stream CSV to browser for download (SECURE VERSION)
     */
    public function streamCSV(array $headers, array $rows, string $filename): void
    {
        if (empty($headers)) {
            throw new \InvalidArgumentException('Headers cannot be empty');
        }
        
        if (empty($filename)) {
            throw new \InvalidArgumentException('Filename cannot be empty');
        }
        
        // Validate filename for download
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.csv$/', $filename)) {
            throw new \InvalidArgumentException('Invalid filename format');
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        $handle = fopen('php://output', 'w');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open output stream");
        }
        
        fputcsv($handle, $headers);
        
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }
    
    /**
     * Stream large CSV with generator for memory efficiency
     */
    public function streamLargeCSV(iterable $dataGenerator, array $headers, string $filename): void
    {
        if (empty($headers)) {
            throw new \InvalidArgumentException('Headers cannot be empty');
        }
        
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
        
        $handle = fopen($filename, 'w');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        try {
            fputcsv($handle, $headers);
            
            foreach ($dataGenerator as $row) {
                if (fputcsv($handle, $row) === false) {
                    throw new \RuntimeException("Failed to write CSV row");
                }
                // Memory is freed after each iteration
            }
        } finally {
            fclose($handle);
        }
    }
}
