#!/usr/bin/env php
<?php
/**
 * Site Spider - Error Detection
 * 
 * Crawls the local development site and reports only 404s and other errors.
 * Usage: php spider-check-errors.php [base-url] [max-urls] [max-depth]
 * 
 * Arguments:
 *   base-url  - Starting URL (default: http://localhost:4323/series)
 *   max-urls  - Maximum URLs to check (default: 1000)
 *   max-depth - Maximum link depth (default: 10)
 * 
 * Examples:
 *   php spider-check-errors.php
 *   php spider-check-errors.php http://localhost:4323/
 *   php spider-check-errors.php http://localhost:4323/ 500 5
 */

class SiteSpider
{
    private string $baseUrl;
    private string $baseDomain;
    private array $visited = [];
    private array $errors = [];
    private int $checkedCount = 0;
    private int $maxUrls;
    private int $maxDepth;
    
    public function __construct(string $baseUrl, int $maxUrls = 1000, int $maxDepth = 10)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->maxUrls = $maxUrls;
        $this->maxDepth = $maxDepth;
        $parsed = parse_url($this->baseUrl);
        $this->baseDomain = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? 'localhost');
        if (isset($parsed['port'])) {
            $this->baseDomain .= ':' . $parsed['port'];
        }
    }
    
    public function crawl(): void
    {
        echo "🕷️  Starting spider at: {$this->baseUrl}\n";
        echo "   Base domain: {$this->baseDomain}\n";
        echo "   Max URLs: {$this->maxUrls}\n";
        echo "   Max depth: {$this->maxDepth}\n";
        echo "   Looking for errors...\n\n";
        
        $this->crawlUrl($this->baseUrl, '', 0);
        
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "✅ Crawl complete!\n";
        echo "   Checked: {$this->checkedCount} URLs\n";
        echo "   Errors found: " . count($this->errors) . "\n";
        
        if (empty($this->errors)) {
            echo "\n🎉 No errors found! All pages are working.\n";
        } else {
            echo "\n" . str_repeat('=', 80) . "\n";
            echo "❌ ERRORS FOUND:\n\n";
            
            foreach ($this->errors as $error) {
                echo "Status: {$error['status']} - {$error['url']}\n";
                if (!empty($error['found_on'])) {
                    echo "  → Found on: {$error['found_on']}\n";
                }
                echo "\n";
            }
        }
    }
    
    private function crawlUrl(string $url, string $foundOn = '', int $depth = 0): void
    {
        // Check limits
        if ($this->checkedCount >= $this->maxUrls) {
            return;
        }
        
        if ($depth > $this->maxDepth) {
            return;
        }
        
        // Normalize URL
        $url = $this->normalizeUrl($url);
        
        // Skip if already visited
        if (isset($this->visited[$url])) {
            return;
        }
        
        // Skip external URLs
        if (!$this->isInternalUrl($url)) {
            return;
        }
        
        // Mark as visited
        $this->visited[$url] = true;
        $this->checkedCount++;
        
        // Check the URL
        echo ".";
        if ($this->checkedCount % 50 === 0) {
            echo " [{$this->checkedCount}]\n";
        }
        
        // Create stream context
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'follow_location' => 0,
                'ignore_errors' => true, // Don't throw warnings on 404, etc.
            ]
        ]);
        
        // Fetch the URL
        $response = @file_get_contents($url, false, $context);
        
        // Parse response headers
        $statusCode = 0;
        if (isset($http_response_header)) {
            $statusLine = $http_response_header[0] ?? '';
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
                $statusCode = (int)$matches[1];
            }
        }
        
        // If we got no response, it's a connection failure
        if ($response === false && $statusCode === 0) {
            $this->errors[] = [
                'url' => $url,
                'status' => 'Connection Failed',
                'found_on' => $foundOn
            ];
            return;
        }
        
        // Check for errors
        if ($statusCode >= 400) {
            $this->errors[] = [
                'url' => $url,
                'status' => $statusCode,
                'found_on' => $foundOn
            ];
            return; // Don't crawl links from error pages
        }
        
        // Extract and crawl links only from successful pages
        if ($response !== false && $statusCode >= 200 && $statusCode < 400) {
            $links = $this->extractLinks($response, $url);
            
            foreach ($links as $link) {
                $this->crawlUrl($link, $url, $depth + 1);
            }
        }
        
        // Free memory
        unset($response);
    }
    
    private function extractLinks(string $html, string $baseUrl): array
    {
        $links = [];
        
        // Limit HTML size to prevent memory issues
        if (strlen($html) > 10000000) { // 10MB limit
            $html = substr($html, 0, 10000000);
        }
        
        // Extract href attributes from anchor tags - use more efficient regex
        if (preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $href = $match[1];
                
                // Skip anchors, mailto, tel, javascript, etc.
                if (str_starts_with($href, '#') || 
                    str_starts_with($href, 'mailto:') || 
                    str_starts_with($href, 'tel:') ||
                    str_starts_with($href, 'javascript:')) {
                    continue;
                }
                
                // Convert relative URLs to absolute
                $absoluteUrl = $this->makeAbsoluteUrl($href, $baseUrl);
                if ($absoluteUrl && !isset($this->visited[$this->normalizeUrl($absoluteUrl)])) {
                    $links[] = $absoluteUrl;
                }
            }
        }
        
        return array_unique($links);
    }
    
    private function makeAbsoluteUrl(string $url, string $baseUrl): ?string
    {
        // Already absolute
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        
        // Protocol-relative URL
        if (str_starts_with($url, '//')) {
            return 'http:' . $url;
        }
        
        // Absolute path
        if (str_starts_with($url, '/')) {
            return $this->baseDomain . $url;
        }
        
        // Relative path
        $basePath = dirname(parse_url($baseUrl, PHP_URL_PATH) ?? '/');
        $basePath = $basePath === '.' ? '/' : $basePath;
        return $this->baseDomain . rtrim($basePath, '/') . '/' . $url;
    }
    
    private function normalizeUrl(string $url): string
    {
        // Remove fragment
        $url = preg_replace('/#.*$/', '', $url);
        
        // Remove trailing slash for consistency
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';
        
        // Keep trailing slash only for directory-like paths
        if ($path !== '/' && str_ends_with($path, '/')) {
            $url = rtrim($url, '/');
        }
        
        return $url;
    }
    
    private function isInternalUrl(string $url): bool
    {
        return str_starts_with($url, $this->baseDomain);
    }
}

// Run the spider
$baseUrl = $argv[1] ?? 'http://localhost:4323/series';
$maxUrls = isset($argv[2]) ? (int)$argv[2] : 1000;
$maxDepth = isset($argv[3]) ? (int)$argv[3] : 10;

$spider = new SiteSpider($baseUrl, $maxUrls, $maxDepth);
$spider->crawl();
