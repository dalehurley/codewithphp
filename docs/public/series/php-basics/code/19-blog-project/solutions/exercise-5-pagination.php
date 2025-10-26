<?php

declare(strict_types=1);

/**
 * Exercise 5: Add Pagination (⭐⭐⭐⭐)
 * 
 * Limit posts shown per page and add navigation links.
 * 
 * Requirements:
 * - Modify Post::all() to accept limit and offset
 * - Add Post::count() method for total count
 * - Calculate total pages in controller
 * - Add Previous/Next links to view
 * - Handle ?page=N query parameter
 */

// ============================================================================
// Updated Post Model with pagination support
// ============================================================================

class Post
{
    private static function getConnection(): PDO
    {
        $pdo = new PDO('sqlite:' . __DIR__ . '/blog.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /**
     * Get posts with pagination
     * 
     * @param int $limit Number of posts per page
     * @param int $offset Starting position
     * @return array Array of posts
     */
    public static function paginate(int $limit = 10, int $offset = 0): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM posts 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total count of posts
     * 
     * @return int Total number of posts
     */
    public static function count(): int
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Get posts with search and pagination
     * 
     * @param string $query Search query
     * @param int $limit Number of posts per page
     * @param int $offset Starting position
     * @return array Array of posts
     */
    public static function searchPaginated(string $query, int $limit = 10, int $offset = 0): array
    {
        if (empty(trim($query))) {
            return self::paginate($limit, $offset);
        }

        $pdo = self::getConnection();
        $searchTerm = '%' . $query . '%';

        $stmt = $pdo->prepare("
            SELECT * FROM posts 
            WHERE title LIKE ? OR content LIKE ?
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$searchTerm, $searchTerm, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count search results
     * 
     * @param string $query Search query
     * @return int Number of matching posts
     */
    public static function countSearch(string $query): int
    {
        if (empty(trim($query))) {
            return self::count();
        }

        $pdo = self::getConnection();
        $searchTerm = '%' . $query . '%';

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM posts 
            WHERE title LIKE ? OR content LIKE ?
        ");

        $stmt->execute([$searchTerm, $searchTerm]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }
}

// ============================================================================
// Pagination Helper Class
// ============================================================================

class Paginator
{
    private int $currentPage;
    private int $perPage;
    private int $totalItems;
    private int $totalPages;

    public function __construct(int $currentPage, int $perPage, int $totalItems)
    {
        $this->currentPage = max(1, $currentPage);
        $this->perPage = max(1, $perPage);
        $this->totalItems = max(0, $totalItems);
        $this->totalPages = (int) ceil($this->totalItems / $this->perPage);

        // Ensure current page doesn't exceed total pages
        if ($this->currentPage > $this->totalPages && $this->totalPages > 0) {
            $this->currentPage = $this->totalPages;
        }
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function getNextPage(): int
    {
        return min($this->totalPages, $this->currentPage + 1);
    }

    public function getPageNumbers(): array
    {
        $pages = [];
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }

    public function getStartItem(): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }
        return $this->getOffset() + 1;
    }

    public function getEndItem(): int
    {
        return min($this->totalItems, $this->getOffset() + $this->perPage);
    }
}

// ============================================================================
// Updated PostController with pagination
// ============================================================================

class PostController
{
    private const POSTS_PER_PAGE = 5;

    /**
     * Display paginated posts or search results
     */
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $searchQuery = $_GET['q'] ?? '';

        // Get total count
        $totalPosts = empty($searchQuery)
            ? Post::count()
            : Post::countSearch($searchQuery);

        // Create paginator
        $paginator = new Paginator($page, self::POSTS_PER_PAGE, $totalPosts);

        // Get posts
        $posts = empty($searchQuery)
            ? Post::paginate(self::POSTS_PER_PAGE, $paginator->getOffset())
            : Post::searchPaginated($searchQuery, self::POSTS_PER_PAGE, $paginator->getOffset());

        $this->renderIndexView($posts, $paginator, $searchQuery);
    }

    /**
     * Render the index view with pagination
     */
    private function renderIndexView(array $posts, Paginator $paginator, string $searchQuery = ''): void
    {
        $isSearching = !empty($searchQuery);
        $queryString = $isSearching ? '&q=' . urlencode($searchQuery) : '';

?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Blog Posts - Page <?php echo $paginator->getCurrentPage(); ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 900px;
                    margin: 50px auto;
                    padding: 0 20px;
                }

                h1 {
                    color: #2c3e50;
                }

                /* Stats Bar */
                .stats-bar {
                    background: #f8f9fa;
                    padding: 15px 20px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .stats-info {
                    color: #555;
                }

                .stats-info strong {
                    color: #2c3e50;
                }

                /* Post List */
                .post-list {
                    display: grid;
                    gap: 20px;
                    margin-bottom: 30px;
                }

                .post-card {
                    background: white;
                    padding: 25px;
                    border: 1px solid #e1e8ed;
                    border-radius: 8px;
                }

                .post-title {
                    color: #2c3e50;
                    margin-top: 0;
                }

                .post-title a {
                    color: #2c3e50;
                    text-decoration: none;
                }

                .post-title a:hover {
                    color: #3498db;
                }

                .post-meta {
                    color: #7f8c8d;
                    font-size: 0.85em;
                    margin-bottom: 15px;
                }

                .post-excerpt {
                    color: #555;
                    line-height: 1.6;
                }

                /* Pagination */
                .pagination {
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                    margin: 40px 0;
                    align-items: center;
                }

                .page-link {
                    padding: 10px 15px;
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    text-decoration: none;
                    color: #2c3e50;
                    font-weight: bold;
                    transition: all 0.3s;
                }

                .page-link:hover {
                    background: #3498db;
                    color: white;
                    border-color: #3498db;
                }

                .page-link.active {
                    background: #3498db;
                    color: white;
                    border-color: #3498db;
                }

                .page-link.disabled {
                    opacity: 0.5;
                    cursor: not-allowed;
                    pointer-events: none;
                }

                .page-ellipsis {
                    padding: 10px 5px;
                    color: #7f8c8d;
                }

                .pagination-info {
                    text-align: center;
                    color: #7f8c8d;
                    margin-top: 20px;
                    font-size: 0.9em;
                }

                /* No Results */
                .no-results {
                    background: #fff3cd;
                    padding: 30px;
                    border-radius: 8px;
                    text-align: center;
                    color: #856404;
                }
            </style>
        </head>

        <body>
            <h1>Blog Posts</h1>

            <?php if (!empty($posts)): ?>
                <!-- Stats Bar -->
                <div class="stats-bar">
                    <div class="stats-info">
                        Showing <strong><?php echo $paginator->getStartItem(); ?>-<?php echo $paginator->getEndItem(); ?></strong>
                        of <strong><?php echo $paginator->getTotalPages() * self::POSTS_PER_PAGE; ?></strong> posts
                    </div>
                    <div class="stats-info">
                        Page <strong><?php echo $paginator->getCurrentPage(); ?></strong>
                        of <strong><?php echo $paginator->getTotalPages(); ?></strong>
                    </div>
                </div>

                <!-- Post List -->
                <div class="post-list">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <h2 class="post-title">
                                <a href="/posts/<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <div class="post-meta">
                                Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                            </div>
                            <div class="post-excerpt">
                                <?php
                                $excerpt = htmlspecialchars($post['content']);
                                echo strlen($excerpt) > 200 ? substr($excerpt, 0, 200) . '...' : $excerpt;
                                ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($paginator->getTotalPages() > 1): ?>
                    <nav class="pagination">
                        <!-- Previous Button -->
                        <a
                            href="?page=<?php echo $paginator->getPreviousPage() . $queryString; ?>"
                            class="page-link <?php echo !$paginator->hasPrevious() ? 'disabled' : ''; ?>">
                            ← Previous
                        </a>

                        <!-- First Page -->
                        <?php if ($paginator->getCurrentPage() > 3): ?>
                            <a href="?page=1<?php echo $queryString; ?>" class="page-link">1</a>
                            <?php if ($paginator->getCurrentPage() > 4): ?>
                                <span class="page-ellipsis">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php foreach ($paginator->getPageNumbers() as $pageNum): ?>
                            <a
                                href="?page=<?php echo $pageNum . $queryString; ?>"
                                class="page-link <?php echo $pageNum === $paginator->getCurrentPage() ? 'active' : ''; ?>">
                                <?php echo $pageNum; ?>
                            </a>
                        <?php endforeach; ?>

                        <!-- Last Page -->
                        <?php if ($paginator->getCurrentPage() < $paginator->getTotalPages() - 2): ?>
                            <?php if ($paginator->getCurrentPage() < $paginator->getTotalPages() - 3): ?>
                                <span class="page-ellipsis">...</span>
                            <?php endif; ?>
                            <a href="?page=<?php echo $paginator->getTotalPages() . $queryString; ?>" class="page-link">
                                <?php echo $paginator->getTotalPages(); ?>
                            </a>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <a
                            href="?page=<?php echo $paginator->getNextPage() . $queryString; ?>"
                            class="page-link <?php echo !$paginator->hasNext() ? 'disabled' : ''; ?>">
                            Next →
                        </a>
                    </nav>

                    <div class="pagination-info">
                        Jump to page:
                        <?php for ($i = 1; $i <= min(5, $paginator->getTotalPages()); $i++): ?>
                            <a href="?page=<?php echo $i . $queryString; ?>"><?php echo $i; ?></a>
                            <?php if ($i < min(5, $paginator->getTotalPages())): ?> | <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <h2>No posts found</h2>
                    <p>There are no posts to display.</p>
                </div>
            <?php endif; ?>
        </body>

        </html>
<?php
    }
}

// ============================================================================
// Demo
// ============================================================================

echo "=== Pagination Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Added Post::paginate() method:" . PHP_EOL;
echo "  - Accepts limit and offset parameters" . PHP_EOL;
echo "  - Returns paginated results" . PHP_EOL . PHP_EOL;

echo "✓ Added Post::count() method:" . PHP_EOL;
echo "  - Returns total number of posts" . PHP_EOL;
echo "  - Used to calculate total pages" . PHP_EOL . PHP_EOL;

echo "✓ Added Paginator helper class:" . PHP_EOL;
echo "  - Calculates offset, page numbers" . PHP_EOL;
echo "  - hasPrevious() / hasNext() helpers" . PHP_EOL;
echo "  - getPageNumbers() for page links" . PHP_EOL;
echo "  - getStartItem() / getEndItem() for stats" . PHP_EOL . PHP_EOL;

echo "✓ Updated controller with pagination:" . PHP_EOL;
echo "  - Reads ?page=N parameter" . PHP_EOL;
echo "  - Creates Paginator instance" . PHP_EOL;
echo "  - Fetches appropriate page of posts" . PHP_EOL . PHP_EOL;

echo "✓ Updated view with pagination UI:" . PHP_EOL;
echo "  - Previous/Next buttons" . PHP_EOL;
echo "  - Page number links" . PHP_EOL;
echo "  - Ellipsis for large page counts" . PHP_EOL;
echo "  - Current page highlighting" . PHP_EOL;
echo "  - Stats bar (showing X-Y of Z posts)" . PHP_EOL;
echo "  - Disabled state for prev/next at boundaries" . PHP_EOL;
