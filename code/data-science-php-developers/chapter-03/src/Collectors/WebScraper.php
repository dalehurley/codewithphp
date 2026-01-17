<?php

declare(strict_types=1);

namespace DataScience\Collectors;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use RuntimeException;

class WebScraper
{
    private Client $client;
    private int $delayMs;
    private string $userAgent;
    
    public function __construct(
        int $delayMs = 2000,
        ?string $userAgent = null
    ) {
        $this->delayMs = $delayMs;
        $this->userAgent = $userAgent ?? 
            'Mozilla/5.0 (compatible; DataCollector/1.0; +https://yoursite.com/bot)';
        
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    }
    
    /**
     * Fetch and parse a web page
     */
    public function scrape(string $url): Crawler
    {
        // Check robots.txt compliance
        if (!$this->isAllowedByRobotsTxt($url)) {
            throw new RuntimeException(
                "Scraping disallowed by robots.txt: {$url}"
            );
        }
        
        // Enforce polite delay
        usleep($this->delayMs * 1000);
        
        try {
            $response = $this->client->get($url);
            $html = (string)$response->getBody();
            
            return new Crawler($html, $url);
            
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Failed to scrape {$url}: " . $e->getMessage()
            );
        }
    }
    
    /**
     * Extract data using CSS selectors
     */
    public function extract(Crawler $crawler, array $selectors): array
    {
        $data = [];
        
        foreach ($selectors as $key => $selector) {
            try {
                $elements = $crawler->filter($selector);
                
                if ($elements->count() === 0) {
                    $data[$key] = null;
                    continue;
                }
                
                // If multiple elements, return array
                if ($elements->count() > 1) {
                    $data[$key] = $elements->each(function (Crawler $node) {
                        return trim($node->text());
                    });
                } else {
                    $data[$key] = trim($elements->text());
                }
                
            } catch (\Exception $e) {
                $data[$key] = null;
            }
        }
        
        return $data;
    }
    
    /**
     * Extract structured data from multiple items on a page
     */
    public function extractList(
        Crawler $crawler,
        string $itemSelector,
        array $fieldSelectors
    ): array {
        $items = [];
        
        $crawler->filter($itemSelector)->each(
            function (Crawler $item) use (&$items, $fieldSelectors) {
                $data = [];
                
                foreach ($fieldSelectors as $key => $selector) {
                    try {
                        $element = $item->filter($selector);
                        $data[$key] = $element->count() > 0 
                            ? trim($element->text()) 
                            : null;
                    } catch (\Exception $e) {
                        $data[$key] = null;
                    }
                }
                
                $items[] = $data;
            }
        );
        
        return $items;
    }
    
    /**
     * Check robots.txt compliance (simplified)
     */
    private function isAllowedByRobotsTxt(string $url): bool
    {
        // Parse URL to get base domain
        $parsed = parse_url($url);
        $robotsUrl = "{$parsed['scheme']}://{$parsed['host']}/robots.txt";
        
        try {
            $response = $this->client->get($robotsUrl);
            $robotsTxt = (string)$response->getBody();
            
            // Simple check: look for "Disallow: /" for all user agents
            // Production code should use a proper robots.txt parser
            if (preg_match('/User-agent: \*.*?Disallow: \//s', $robotsTxt)) {
                return false;
            }
            
        } catch (\Exception $e) {
            // If robots.txt doesn't exist, assume allowed
            return true;
        }
        
        return true;
    }
    
    /**
     * Extract links from page
     */
    public function extractLinks(Crawler $crawler, ?string $filter = null): array
    {
        $selector = $filter ? "a{$filter}" : 'a';
        
        return $crawler->filter($selector)->each(function (Crawler $node) {
            return [
                'url' => $node->attr('href'),
                'text' => trim($node->text()),
            ];
        });
    }
}


