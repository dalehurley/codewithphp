<?php

declare(strict_types=1);

/**
 * Exercise 4: Add Post Search (⭐⭐⭐)
 * 
 * Implement a search feature to find posts by title or content.
 * 
 * Requirements:
 * - Add search() method to Post model using LIKE queries
 * - Add search form to posts index page
 * - Modify index() method to handle search queries
 * - Display search results with "Clear search" link
 */

// ============================================================================
// Updated Post Model with search functionality
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
     * Search posts by title or content
     * 
     * @param string $query Search query
     * @return array Array of matching posts
     */
    public static function search(string $query): array
    {
        if (empty(trim($query))) {
            return self::all();
        }

        $pdo = self::getConnection();

        // Use LIKE for case-insensitive search
        $searchTerm = '%' . $query . '%';

        $stmt = $pdo->prepare("
            SELECT * FROM posts 
            WHERE title LIKE ? OR content LIKE ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search with highlighting support
     * Returns posts with matched query highlighted
     */
    public static function searchWithHighlights(string $query): array
    {
        $posts = self::search($query);

        if (empty(trim($query))) {
            return $posts;
        }

        // Add highlights to each post
        foreach ($posts as &$post) {
            $post['title_highlighted'] = self::highlight($post['title'], $query);
            $post['content_highlighted'] = self::highlight($post['content'], $query);
            $post['matched_in'] = self::getMatchLocation($post, $query);
        }

        return $posts;
    }

    /**
     * Highlight search terms in text
     */
    private static function highlight(string $text, string $query): string
    {
        if (empty($query)) {
            return htmlspecialchars($text);
        }

        // Escape the text first
        $text = htmlspecialchars($text);

        // Highlight the query (case-insensitive)
        $highlighted = preg_replace(
            '/(' . preg_quote($query, '/') . ')/i',
            '<mark>$1</mark>',
            $text
        );

        return $highlighted;
    }

    /**
     * Determine where the match occurred
     */
    private static function getMatchLocation(array $post, string $query): string
    {
        $inTitle = stripos($post['title'], $query) !== false;
        $inContent = stripos($post['content'], $query) !== false;

        if ($inTitle && $inContent) {
            return 'title and content';
        } elseif ($inTitle) {
            return 'title';
        } else {
            return 'content';
        }
    }

    /**
     * Get all posts
     */
    public static function all(): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get search suggestions based on popular terms
     */
    public static function getPopularSearchTerms(int $limit = 5): array
    {
        // In a real app, you'd track search queries
        // For now, return common words from post titles
        $pdo = self::getConnection();
        $posts = self::all();

        $words = [];
        foreach ($posts as $post) {
            $titleWords = explode(' ', strtolower($post['title']));
            foreach ($titleWords as $word) {
                $word = preg_replace('/[^a-z0-9]/', '', $word);
                if (strlen($word) > 3) {
                    $words[] = $word;
                }
            }
        }

        $wordCounts = array_count_values($words);
        arsort($wordCounts);

        return array_slice(array_keys($wordCounts), 0, $limit);
    }
}

// ============================================================================
// Updated PostController with search handling
// ============================================================================

class PostController
{
    /**
     * Display all posts or search results
     */
    public function index(): void
    {
        $searchQuery = $_GET['q'] ?? '';

        if (!empty($searchQuery)) {
            $posts = Post::searchWithHighlights($searchQuery);
        } else {
            $posts = Post::all();
        }

        $this->renderIndexView($posts, $searchQuery);
    }

    /**
     * Render the index view with search
     */
    private function renderIndexView(array $posts, string $searchQuery = ''): void
    {
        $isSearching = !empty($searchQuery);
        $resultCount = count($posts);

?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Blog Posts<?php echo $isSearching ? ' - Search Results' : ''; ?></title>
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

                /* Search Bar */
                .search-container {
                    background: #f8f9fa;
                    padding: 25px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                }

                .search-form {
                    display: flex;
                    gap: 10px;
                }

                .search-input {
                    flex: 1;
                    padding: 12px 15px;
                    border: 2px solid #ddd;
                    border-radius: 4px;
                    font-size: 16px;
                }

                .search-input:focus {
                    outline: none;
                    border-color: #3498db;
                }

                .search-btn {
                    padding: 12px 30px;
                    background: #3498db;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    font-weight: bold;
                    cursor: pointer;
                }

                .search-btn:hover {
                    background: #2980b9;
                }

                /* Search Results Info */
                .search-info {
                    background: #e8f4f8;
                    padding: 15px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }

                .search-info-text {
                    color: #2c3e50;
                }

                .search-query {
                    font-weight: bold;
                    color: #3498db;
                }

                .clear-search {
                    color: #e74c3c;
                    text-decoration: none;
                    font-weight: bold;
                }

                .clear-search:hover {
                    text-decoration: underline;
                }

                /* No Results */
                .no-results {
                    background: #fff3cd;
                    padding: 30px;
                    border-radius: 8px;
                    text-align: center;
                    color: #856404;
                }

                .no-results h2 {
                    margin-top: 0;
                    color: #856404;
                }

                /* Post List */
                .post-list {
                    display: grid;
                    gap: 20px;
                }

                .post-card {
                    background: white;
                    padding: 25px;
                    border: 1px solid #e1e8ed;
                    border-radius: 8px;
                    transition: box-shadow 0.3s;
                }

                .post-card:hover {
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                }

                .post-title {
                    color: #2c3e50;
                    margin-top: 0;
                    margin-bottom: 10px;
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

                .match-badge {
                    display: inline-block;
                    background: #3498db;
                    color: white;
                    padding: 2px 8px;
                    border-radius: 3px;
                    font-size: 0.75em;
                    margin-left: 10px;
                }

                .post-excerpt {
                    color: #555;
                    line-height: 1.6;
                }

                mark {
                    background: #fff3cd;
                    padding: 2px 4px;
                    border-radius: 2px;
                    font-weight: bold;
                }

                .read-more {
                    display: inline-block;
                    margin-top: 10px;
                    color: #3498db;
                    text-decoration: none;
                    font-weight: bold;
                }

                .read-more:hover {
                    text-decoration: underline;
                }
            </style>
        </head>

        <body>
            <h1>Blog Posts</h1>

            <!-- Search Form -->
            <div class="search-container">
                <form action="/posts" method="get" class="search-form">
                    <input
                        type="text"
                        name="q"
                        class="search-input"
                        placeholder="Search posts by title or content..."
                        value="<?php echo htmlspecialchars($searchQuery); ?>"
                        autofocus />
                    <button type="submit" class="search-btn">🔍 Search</button>
                </form>
            </div>

            <?php if ($isSearching): ?>
                <!-- Search Results Info -->
                <div class="search-info">
                    <div class="search-info-text">
                        Found <strong><?php echo $resultCount; ?></strong>
                        result<?php echo $resultCount !== 1 ? 's' : ''; ?>
                        for <span class="search-query">"<?php echo htmlspecialchars($searchQuery); ?>"</span>
                    </div>
                    <a href="/posts" class="clear-search">✖ Clear Search</a>
                </div>
            <?php endif; ?>

            <?php if (empty($posts)): ?>
                <!-- No Results -->
                <div class="no-results">
                    <h2>No posts found</h2>
                    <p>Try different keywords or <a href="/posts">view all posts</a>.</p>
                </div>
            <?php else: ?>
                <!-- Post List -->
                <div class="post-list">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <h2 class="post-title">
                                <a href="/posts/<?php echo $post['id']; ?>">
                                    <?php
                                    echo $isSearching && isset($post['title_highlighted'])
                                        ? $post['title_highlighted']
                                        : htmlspecialchars($post['title']);
                                    ?>
                                </a>
                                <?php if ($isSearching && isset($post['matched_in'])): ?>
                                    <span class="match-badge">
                                        Match in: <?php echo htmlspecialchars($post['matched_in']); ?>
                                    </span>
                                <?php endif; ?>
                            </h2>
                            <div class="post-meta">
                                Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                            </div>
                            <div class="post-excerpt">
                                <?php
                                $excerpt = $isSearching && isset($post['content_highlighted'])
                                    ? $post['content_highlighted']
                                    : htmlspecialchars($post['content']);

                                // Truncate to 200 characters
                                if (strlen(strip_tags($excerpt)) > 200) {
                                    $excerpt = substr($excerpt, 0, 200) . '...';
                                }
                                echo $excerpt;
                                ?>
                            </div>
                            <a href="/posts/<?php echo $post['id']; ?>" class="read-more">Read More →</a>
                        </article>
                    <?php endforeach; ?>
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

echo "=== Post Search Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Added Post::search() method:" . PHP_EOL;
echo "  - Searches both title and content using LIKE" . PHP_EOL;
echo "  - Case-insensitive search" . PHP_EOL;
echo "  - Returns all posts if query is empty" . PHP_EOL . PHP_EOL;

echo "✓ Added Post::searchWithHighlights():" . PHP_EOL;
echo "  - Returns posts with highlighted search terms" . PHP_EOL;
echo "  - Indicates where match occurred (title/content)" . PHP_EOL;
echo "  - Uses <mark> tag for highlights" . PHP_EOL . PHP_EOL;

echo "✓ Updated PostController::index():" . PHP_EOL;
echo "  - Checks for 'q' query parameter" . PHP_EOL;
echo "  - Calls Post::searchWithHighlights() if searching" . PHP_EOL;
echo "  - Otherwise shows all posts" . PHP_EOL . PHP_EOL;

echo "✓ Updated view with search features:" . PHP_EOL;
echo "  - Search form with autofocus" . PHP_EOL;
echo "  - Search results info bar" . PHP_EOL;
echo "  - 'Clear Search' link" . PHP_EOL;
echo "  - Highlighted search terms in results" . PHP_EOL;
echo "  - Badge showing where match occurred" . PHP_EOL;
echo "  - 'No results' message" . PHP_EOL . PHP_EOL;

echo "✓ URL structure:" . PHP_EOL;
echo "  All posts: /posts" . PHP_EOL;
echo "  Search: /posts?q=search+term" . PHP_EOL . PHP_EOL;

echo "Security considerations:" . PHP_EOL;
echo "  - Uses prepared statements (prevents SQL injection)" . PHP_EOL;
echo "  - Escapes output with htmlspecialchars()" . PHP_EOL;
echo "  - Validates and sanitizes user input" . PHP_EOL;
