<?php

declare(strict_types=1);

/**
 * API Client using cURL
 * 
 * Demonstrates making HTTP requests to external APIs with proper error handling.
 */

/**
 * Fetch user data from GitHub API
 * 
 * @param string $username GitHub username
 * @return array|null User data or null on error
 */
function fetchGitHubUser(string $username): ?array
{
    $url = "https://api.github.com/users/" . urlencode($username);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Tutorial-App');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $response = curl_exec($ch);

    if ($response === false) {
        echo "❌ cURL Error: " . curl_error($ch) . PHP_EOL;
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 404) {
        echo "❌ User not found" . PHP_EOL;
        return null;
    }

    if ($httpCode !== 200) {
        echo "❌ HTTP Error: " . $httpCode . PHP_EOL;
        return null;
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON Error: " . json_last_error_msg() . PHP_EOL;
        return null;
    }

    return $data;
}

/**
 * Create a new user via API (using test API)
 * 
 * @param string $name User's name
 * @param string $email User's email
 * @return array|null Created user data or null on error
 */
function createUser(string $name, string $email): ?array
{
    $url = 'https://jsonplaceholder.typicode.com/users';

    $data = [
        'name' => $name,
        'email' => $email,
        'username' => strtolower(str_replace(' ', '', $name))
    ];

    $json = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        return json_decode($response, true);
    }

    echo "❌ Error: HTTP " . $httpCode . PHP_EOL;
    return null;
}

/**
 * Search GitHub repositories
 * 
 * @param string $query Search query
 * @param int $limit Number of results to return
 * @return array|null Search results or null on error
 */
function searchGitHubRepos(string $query, int $limit = 5): ?array
{
    $params = http_build_query([
        'q' => $query,
        'sort' => 'stars',
        'order' => 'desc',
        'per_page' => $limit
    ]);

    $url = "https://api.github.com/search/repositories?" . $params;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-Tutorial-App');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    if ($response === false) {
        echo "❌ cURL Error: " . curl_error($ch) . PHP_EOL;
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "❌ HTTP Error: " . $httpCode . PHP_EOL;
        return null;
    }

    return json_decode($response, true);
}

// Demo 1: Fetch GitHub user
echo "=== Fetching GitHub User ===" . PHP_EOL;
$user = fetchGitHubUser('github');

if ($user !== null) {
    echo "Name: " . $user['name'] . PHP_EOL;
    echo "Bio: " . ($user['bio'] ?? 'N/A') . PHP_EOL;
    echo "Public Repos: " . $user['public_repos'] . PHP_EOL;
    echo "Followers: " . $user['followers'] . PHP_EOL;
    echo "Location: " . ($user['location'] ?? 'N/A') . PHP_EOL;
    echo "Created: " . $user['created_at'] . PHP_EOL;
}

echo PHP_EOL;

// Demo 2: Search GitHub repositories
echo "=== Top PHP Repositories ===" . PHP_EOL;
$results = searchGitHubRepos('language:php', 5);

if ($results !== null && isset($results['items'])) {
    foreach ($results['items'] as $index => $repo) {
        echo ($index + 1) . ". {$repo['name']}" . PHP_EOL;
        echo "   Stars: " . number_format($repo['stargazers_count']) . PHP_EOL;
        echo "   Description: " . ($repo['description'] ?? 'No description') . PHP_EOL;
        echo "   URL: " . $repo['html_url'] . PHP_EOL;
        echo PHP_EOL;
    }
}

// Demo 3: Create user (POST request)
echo "=== Creating New User (Test API) ===" . PHP_EOL;
$newUser = createUser('Alice Johnson', 'alice@example.com');

if ($newUser !== null) {
    echo "✓ User created!" . PHP_EOL;
    echo "ID: " . $newUser['id'] . PHP_EOL;
    echo "Name: " . $newUser['name'] . PHP_EOL;
    echo "Email: " . $newUser['email'] . PHP_EOL;
}

echo PHP_EOL . "✓ API demonstrations complete!" . PHP_EOL;
