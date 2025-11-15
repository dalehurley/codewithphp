<?php

declare(strict_types=1);

/**
 * Insertion Sort - Practical Applications
 *
 * Real-world use cases where insertion sort excels:
 * - Sorting nearly sorted data (O(n) performance)
 * - Online sorting (sorting as data arrives)
 * - Small arrays with objects
 * - Maintaining sorted order with new insertions
 */

/**
 * Represents a blog post
 */
class Post
{
    public function __construct(
        public string $title,
        public int $timestamp,
        public string $author,
        public int $views = 0
    ) {}

    public function __toString(): string
    {
        $date = date('Y-m-d H:i:s', $this->timestamp);
        return "{$this->title} by {$this->author} ({$date}) - {$this->views} views";
    }
}

/**
 * Represents a task with priority
 */
class Task
{
    public function __construct(
        public string $name,
        public int $priority,
        public bool $isUrgent = false
    ) {}

    public function __toString(): string
    {
        $urgentFlag = $this->isUrgent ? '[URGENT] ' : '';
        return "{$urgentFlag}{$this->name} (Priority: {$this->priority})";
    }
}

/**
 * Sort posts by timestamp using insertion sort
 * Perfect for nearly sorted data (new posts added at end)
 *
 * @param array<Post> $posts Array of posts
 * @return array<Post> Sorted posts (newest first)
 */
function sortPostsByTimestamp(array $posts): array
{
    $n = count($posts);

    for ($i = 1; $i < $n; $i++) {
        $key = $posts[$i];
        $j = $i - 1;

        // Sort in descending order (newest first)
        while ($j >= 0 && $posts[$j]->timestamp < $key->timestamp) {
            $posts[$j + 1] = $posts[$j];
            $j--;
        }

        $posts[$j + 1] = $key;
    }

    return $posts;
}

/**
 * Online sorting: Insert new post into already sorted array
 *
 * @param array<Post> $sortedPosts Already sorted posts
 * @param Post $newPost New post to insert
 * @return array<Post> Sorted posts with new post inserted
 */
function insertPostIntoSorted(array $sortedPosts, Post $newPost): array
{
    $sortedPosts[] = $newPost;
    $i = count($sortedPosts) - 1;

    // Bubble the new post to its correct position
    while ($i > 0 && $sortedPosts[$i]->timestamp > $sortedPosts[$i - 1]->timestamp) {
        [$sortedPosts[$i], $sortedPosts[$i - 1]] = [$sortedPosts[$i - 1], $sortedPosts[$i]];
        $i--;
    }

    return $sortedPosts;
}

/**
 * Sort tasks by priority with custom comparison
 *
 * @param array<Task> $tasks Array of tasks
 * @return array<Task> Sorted tasks (urgent first, then by priority)
 */
function sortTasks(array $tasks): array
{
    $n = count($tasks);

    for ($i = 1; $i < $n; $i++) {
        $key = $tasks[$i];
        $j = $i - 1;

        // Custom comparison: urgent tasks first, then by priority
        while ($j >= 0 && compareTasks($tasks[$j], $key) > 0) {
            $tasks[$j + 1] = $tasks[$j];
            $j--;
        }

        $tasks[$j + 1] = $key;
    }

    return $tasks;
}

/**
 * Compare two tasks for sorting
 *
 * @param Task $a First task
 * @param Task $b Second task
 * @return int -1 if a comes first, 1 if b comes first, 0 if equal
 */
function compareTasks(Task $a, Task $b): int
{
    // Urgent tasks always come first
    if ($a->isUrgent && !$b->isUrgent) {
        return -1;
    }
    if (!$a->isUrgent && $b->isUrgent) {
        return 1;
    }

    // If both urgent or both not urgent, sort by priority (higher first)
    return $b->priority <=> $a->priority;
}

/**
 * Generic insertion sort with custom comparator
 *
 * @template T
 * @param array<T> $arr Array to sort
 * @param callable $comparator Comparison function
 * @return array<T> Sorted array
 */
function insertionSortCustom(array $arr, callable $comparator): array
{
    $n = count($arr);

    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        while ($j >= 0 && $comparator($arr[$j], $key) > 0) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }

        $arr[$j + 1] = $key;
    }

    return $arr;
}

// ============================================================================
// Examples and Test Cases
// ============================================================================

echo "INSERTION SORT - PRACTICAL APPLICATIONS\n";
echo str_repeat('=', 70) . "\n\n";

// Example 1: Sorting blog posts (nearly sorted scenario)
echo "Example 1: Blog Posts - Nearly Sorted Data\n";
echo str_repeat('-', 70) . "\n";
$posts = [
    new Post("First Post", strtotime("2024-01-01 10:00"), "Alice", 100),
    new Post("Second Post", strtotime("2024-01-02 10:00"), "Bob", 150),
    new Post("Third Post", strtotime("2024-01-03 10:00"), "Charlie", 200),
    new Post("Old Post", strtotime("2024-01-01 15:00"), "Dave", 50), // Out of order
    new Post("Fourth Post", strtotime("2024-01-04 10:00"), "Eve", 300),
];

echo "Posts before sorting (notice one out of order):\n";
foreach ($posts as $i => $post) {
    echo "  " . ($i + 1) . ". $post\n";
}

$sortedPosts = sortPostsByTimestamp($posts);

echo "\nPosts after sorting (newest first):\n";
foreach ($sortedPosts as $i => $post) {
    echo "  " . ($i + 1) . ". $post\n";
}
echo "\nNote: Only one element was out of place, insertion sort handled it in ~O(n) time!\n\n";

// Example 2: Online sorting - adding posts as they arrive
echo "Example 2: Online Sorting - Insert New Post\n";
echo str_repeat('-', 70) . "\n";
$sortedPosts = [
    new Post("Latest Post", strtotime("2024-01-04 10:00"), "Alice", 100),
    new Post("Recent Post", strtotime("2024-01-03 10:00"), "Bob", 150),
    new Post("Older Post", strtotime("2024-01-02 10:00"), "Charlie", 200),
];

echo "Current sorted posts:\n";
foreach ($sortedPosts as $i => $post) {
    echo "  " . ($i + 1) . ". $post\n";
}

$newPost = new Post("Breaking News!", strtotime("2024-01-03 15:00"), "Dave", 0);
echo "\nNew post arrives: $newPost\n";

$sortedPosts = insertPostIntoSorted($sortedPosts, $newPost);

echo "\nAfter inserting new post:\n";
foreach ($sortedPosts as $i => $post) {
    echo "  " . ($i + 1) . ". $post\n";
}
echo "\nNote: New post inserted in correct position with minimal comparisons!\n\n";

// Example 3: Task prioritization
echo "Example 3: Task Prioritization\n";
echo str_repeat('-', 70) . "\n";
$tasks = [
    new Task("Write documentation", 3),
    new Task("Fix critical bug", 10, true),
    new Task("Update dependencies", 2),
    new Task("Security patch", 9, true),
    new Task("Code review", 5),
    new Task("Refactor module", 4),
];

echo "Tasks before sorting:\n";
foreach ($tasks as $i => $task) {
    echo "  " . ($i + 1) . ". $task\n";
}

$sortedTasks = sortTasks($tasks);

echo "\nTasks after sorting (urgent first, then by priority):\n";
foreach ($sortedTasks as $i => $task) {
    echo "  " . ($i + 1) . ". $task\n";
}
echo "\n";

// Example 4: Sorting by multiple criteria with custom comparator
echo "Example 4: Multi-Criteria Sorting with Custom Comparator\n";
echo str_repeat('-', 70) . "\n";
$posts = [
    new Post("Popular Post", strtotime("2024-01-01"), "Alice", 1000),
    new Post("Recent Post", strtotime("2024-01-03"), "Bob", 100),
    new Post("Another Popular", strtotime("2024-01-02"), "Charlie", 1000),
    new Post("Viral Post", strtotime("2024-01-01"), "Dave", 5000),
];

// Sort by views (desc), then by timestamp (desc)
$sortedPosts = insertionSortCustom($posts, function ($a, $b) {
    $viewsCompare = $b->views <=> $a->views;
    if ($viewsCompare !== 0) {
        return $viewsCompare;
    }
    return $b->timestamp <=> $a->timestamp;
});

echo "Posts sorted by popularity, then recency:\n";
foreach ($sortedPosts as $i => $post) {
    echo "  " . ($i + 1) . ". $post\n";
}
echo "\n";

// Example 5: Performance comparison - nearly sorted vs random
echo "Example 5: Performance - Nearly Sorted vs Random\n";
echo str_repeat('-', 70) . "\n";

// Nearly sorted (95% sorted)
$nearlySorted = range(1, 1000);
$swaps = 50; // 5% out of order
for ($i = 0; $i < $swaps; $i++) {
    $a = rand(0, 999);
    $b = rand(0, 999);
    [$nearlySorted[$a], $nearlySorted[$b]] = [$nearlySorted[$b], $nearlySorted[$a]];
}

$start = microtime(true);
$result = insertionSortCustom($nearlySorted, fn($a, $b) => $a <=> $b);
$nearlySortedTime = (microtime(true) - $start) * 1000;

// Random
$random = range(1, 1000);
shuffle($random);

$start = microtime(true);
$result = insertionSortCustom($random, fn($a, $b) => $a <=> $b);
$randomTime = (microtime(true) - $start) * 1000;

echo sprintf("Nearly sorted (95%%): %.4f ms (~O(n))\n", $nearlySortedTime);
echo sprintf("Random data:          %.4f ms (O(n²))\n", $randomTime);
echo sprintf("Speed difference:     %.1fx faster for nearly sorted!\n", $randomTime / $nearlySortedTime);
echo "\n";

echo "When to use Insertion Sort:\n";
echo "✓ Small arrays (< 50 elements)\n";
echo "✓ Nearly sorted data (huge performance advantage!)\n";
echo "✓ Online sorting (adding elements to sorted array)\n";
echo "✓ Stable sorting required with O(1) space\n";
echo "✓ Simplicity and code clarity important\n";
