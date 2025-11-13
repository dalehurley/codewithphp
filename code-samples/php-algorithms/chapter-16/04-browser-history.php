<?php

declare(strict_types=1);

/**
 * Browser History Implementation using Doubly Linked List
 *
 * A practical application of doubly linked lists simulating browser history
 * with back and forward navigation capabilities.
 *
 * Features:
 * - Visit new URLs
 * - Navigate back through history
 * - Navigate forward through history
 * - Clear history
 * - View current page
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

/**
 * Page node representing a visited webpage
 */
class PageNode
{
    public function __construct(
        public string $url,
        public string $title,
        public int $timestamp,
        public ?PageNode $next = null,
        public ?PageNode $prev = null
    ) {}
}

/**
 * Browser History implementation using doubly linked list
 */
class BrowserHistory
{
    private ?PageNode $current = null;
    private int $historySize = 0;
    private int $maxHistory;

    /**
     * Initialize browser history
     *
     * @param int $maxHistory Maximum number of pages to keep in history
     */
    public function __construct(int $maxHistory = 100)
    {
        $this->maxHistory = $maxHistory;
    }

    /**
     * Visit a new URL - O(1)
     * Clears forward history when visiting new page
     *
     * @param string $url The URL to visit
     * @param string $title The page title
     * @return void
     */
    public function visit(string $url, string $title = ''): void
    {
        $newPage = new PageNode(
            url: $url,
            title: $title ?: $url,
            timestamp: time()
        );

        if ($this->current !== null) {
            // Clear forward history
            $this->current->next = $newPage;
            $newPage->prev = $this->current;
        }

        $this->current = $newPage;
        $this->historySize++;

        // Trim history if exceeds max size
        $this->trimHistory();

        echo "Visited: {$url}\n";
    }

    /**
     * Navigate back to previous page - O(1)
     *
     * @return ?string The URL of the previous page or null if can't go back
     */
    public function back(): ?string
    {
        if ($this->current === null || $this->current->prev === null) {
            echo "Cannot go back: Already at the beginning of history\n";
            return null;
        }

        $this->current = $this->current->prev;
        echo "Back to: {$this->current->url}\n";
        return $this->current->url;
    }

    /**
     * Navigate forward to next page - O(1)
     *
     * @return ?string The URL of the next page or null if can't go forward
     */
    public function forward(): ?string
    {
        if ($this->current === null || $this->current->next === null) {
            echo "Cannot go forward: Already at the end of history\n";
            return null;
        }

        $this->current = $this->current->next;
        echo "Forward to: {$this->current->url}\n";
        return $this->current->url;
    }

    /**
     * Get current page - O(1)
     *
     * @return ?PageNode The current page or null if history is empty
     */
    public function getCurrentPage(): ?PageNode
    {
        return $this->current;
    }

    /**
     * Get current URL - O(1)
     *
     * @return ?string The current URL or null if history is empty
     */
    public function getCurrentUrl(): ?string
    {
        return $this->current?->url;
    }

    /**
     * Can navigate back? - O(1)
     *
     * @return bool True if can go back, false otherwise
     */
    public function canGoBack(): bool
    {
        return $this->current !== null && $this->current->prev !== null;
    }

    /**
     * Can navigate forward? - O(1)
     *
     * @return bool True if can go forward, false otherwise
     */
    public function canGoForward(): bool
    {
        return $this->current !== null && $this->current->next !== null;
    }

    /**
     * Get all history (back pages only) - O(n)
     *
     * @return array Array of page information
     */
    public function getHistory(): array
    {
        $history = [];
        $page = $this->getFirstPage();

        while ($page !== null) {
            $history[] = [
                'url' => $page->url,
                'title' => $page->title,
                'timestamp' => date('Y-m-d H:i:s', $page->timestamp),
                'is_current' => $page === $this->current
            ];
            $page = $page->next;
        }

        return $history;
    }

    /**
     * Get backward history (pages before current) - O(n)
     *
     * @param int $limit Maximum number of pages to return
     * @return array Array of URLs
     */
    public function getBackwardHistory(int $limit = 10): array
    {
        $history = [];
        $page = $this->current?->prev;
        $count = 0;

        while ($page !== null && $count < $limit) {
            $history[] = $page->url;
            $page = $page->prev;
            $count++;
        }

        return $history;
    }

    /**
     * Get forward history (pages after current) - O(n)
     *
     * @param int $limit Maximum number of pages to return
     * @return array Array of URLs
     */
    public function getForwardHistory(int $limit = 10): array
    {
        $history = [];
        $page = $this->current?->next;
        $count = 0;

        while ($page !== null && $count < $limit) {
            $history[] = $page->url;
            $page = $page->next;
            $count++;
        }

        return $history;
    }

    /**
     * Clear all history - O(1)
     *
     * @return void
     */
    public function clearHistory(): void
    {
        $this->current = null;
        $this->historySize = 0;
        echo "History cleared\n";
    }

    /**
     * Display current navigation state
     *
     * @return void
     */
    public function displayStatus(): void
    {
        echo "\n--- Browser History Status ---\n";
        echo "Current: " . ($this->current?->url ?? 'Empty') . "\n";
        echo "Can go back: " . ($this->canGoBack() ? 'Yes' : 'No') . "\n";
        echo "Can go forward: " . ($this->canGoForward() ? 'Yes' : 'No') . "\n";
        echo "History size: {$this->historySize}\n";

        $backHistory = $this->getBackwardHistory(3);
        if (!empty($backHistory)) {
            echo "Recent back pages: " . implode(', ', $backHistory) . "\n";
        }

        $forwardHistory = $this->getForwardHistory(3);
        if (!empty($forwardHistory)) {
            echo "Forward pages: " . implode(', ', $forwardHistory) . "\n";
        }

        echo "----------------------------\n\n";
    }

    /**
     * Get the first page in history - O(n)
     *
     * @return ?PageNode The first page or null
     */
    private function getFirstPage(): ?PageNode
    {
        if ($this->current === null) {
            return null;
        }

        $page = $this->current;
        while ($page->prev !== null) {
            $page = $page->prev;
        }

        return $page;
    }

    /**
     * Trim history to max size - O(n)
     * Removes oldest pages when history exceeds limit
     *
     * @return void
     */
    private function trimHistory(): void
    {
        if ($this->historySize <= $this->maxHistory) {
            return;
        }

        // Find the first page
        $first = $this->getFirstPage();

        // Remove first page
        if ($first !== null && $first->next !== null) {
            $first->next->prev = null;
            $this->historySize--;
        }
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Browser History Demo ===\n\n";

    $browser = new BrowserHistory(maxHistory: 50);

    // Simulate browsing session
    echo "=== Browsing Session ===\n";
    $browser->visit('https://www.google.com', 'Google');
    $browser->visit('https://www.github.com', 'GitHub');
    $browser->visit('https://www.stackoverflow.com', 'Stack Overflow');
    $browser->visit('https://www.php.net', 'PHP Documentation');

    $browser->displayStatus();

    // Test back navigation
    echo "=== Testing Back Navigation ===\n";
    $browser->back(); // Back to Stack Overflow
    $browser->back(); // Back to GitHub
    $browser->displayStatus();

    // Test forward navigation
    echo "=== Testing Forward Navigation ===\n";
    $browser->forward(); // Forward to Stack Overflow
    $browser->displayStatus();

    // Visit new page (clears forward history)
    echo "=== Visiting New Page (Clears Forward History) ===\n";
    $browser->visit('https://www.reddit.com', 'Reddit');
    $browser->displayStatus();

    // Try to go forward (should fail)
    echo "=== Attempting Forward (Should Fail) ===\n";
    $browser->forward();

    // Display full history
    echo "\n=== Full History ===\n";
    $history = $browser->getHistory();
    foreach ($history as $i => $page) {
        $marker = $page['is_current'] ? ' <-- Current' : '';
        echo sprintf(
            "%d. %s (%s)%s\n",
            $i + 1,
            $page['title'],
            $page['url'],
            $marker
        );
    }

    // Test edge cases
    echo "\n=== Testing Edge Cases ===\n";

    // Try to go back from first page
    $newBrowser = new BrowserHistory();
    $newBrowser->visit('https://example.com', 'Example');
    $newBrowser->back(); // Should print cannot go back message

    // Clear history
    echo "\n";
    $browser->clearHistory();
    $browser->displayStatus();
}
