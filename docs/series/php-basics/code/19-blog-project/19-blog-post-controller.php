<?php

declare(strict_types=1);

/**
 * Chapter 19 Code Sample: Post Controller
 * 
 * This file demonstrates controller methods for handling blog post operations.
 * Copy this to src/Controllers/PostController.php in your simple-blog project.
 * 
 * Note: This code uses the global view() helper function defined in src/helpers.php.
 * The view() function is loaded via Composer's autoloader (see Chapter 18).
 */

namespace App\Controllers;

use App\Models\Post;

// Note: view() is a global helper function loaded by Composer
// It's defined in src/helpers.php and registered in composer.json's "files" section

class PostController
{
    /**
     * Display a list of all posts.
     */
    public function index(): void
    {
        $posts = Post::all();

        view('posts/index', [
            'posts' => $posts,
            'pageTitle' => 'All Posts'
        ]);
    }

    /**
     * Display a single post by ID.
     * 
     * @param string $id The post ID from the URL
     */
    public function show(string $id): void
    {
        $post = Post::find((int)$id);

        if (!$post) {
            http_response_code(404);
            view('404');
            return;
        }

        view('posts/show', [
            'post' => $post,
            'pageTitle' => htmlspecialchars($post['title'])
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): void
    {
        view('posts/create', [
            'pageTitle' => 'Create New Post'
        ]);
    }

    /**
     * Store a new post from form submission.
     */
    public function store(): void
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        // Validate input
        $errors = [];

        if (empty(trim($title))) {
            $errors[] = 'Title is required.';
        }

        if (empty(trim($content))) {
            $errors[] = 'Content is required.';
        }

        if (strlen($title) > 255) {
            $errors[] = 'Title must be 255 characters or less.';
        }

        // If validation fails, show the form again with errors
        if (!empty($errors)) {
            view('posts/create', [
                'pageTitle' => 'Create New Post',
                'errors' => $errors,
                'title' => $title,
                'content' => $content
            ]);
            return;
        }

        // Create the post
        Post::create($title, $content);

        // Redirect to the posts index
        header('Location: /posts');
        exit;
    }

    /**
     * Show the form for editing an existing post.
     * 
     * @param string $id The post ID from the URL
     */
    public function edit(string $id): void
    {
        $post = Post::find((int)$id);

        if (!$post) {
            http_response_code(404);
            view('404');
            return;
        }

        view('posts/edit', [
            'post' => $post,
            'pageTitle' => 'Edit Post'
        ]);
    }

    /**
     * Update an existing post from form submission.
     * 
     * @param string $id The post ID from the URL
     */
    public function updatePost(string $id): void
    {
        $postId = (int)$id;
        $post = Post::find($postId);

        if (!$post) {
            http_response_code(404);
            view('404');
            return;
        }

        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        // Validate input
        $errors = [];

        if (empty(trim($title))) {
            $errors[] = 'Title is required.';
        }

        if (empty(trim($content))) {
            $errors[] = 'Content is required.';
        }

        if (strlen($title) > 255) {
            $errors[] = 'Title must be 255 characters or less.';
        }

        // If validation fails, show the form again with errors
        if (!empty($errors)) {
            view('posts/edit', [
                'pageTitle' => 'Edit Post',
                'errors' => $errors,
                'post' => [
                    'id' => $postId,
                    'title' => $title,
                    'content' => $content
                ]
            ]);
            return;
        }

        // Update the post
        Post::update($postId, $title, $content);

        // Redirect to the post detail page
        header("Location: /posts/{$postId}");
        exit;
    }

    /**
     * Delete a post.
     * 
     * @param string $id The post ID from the URL
     */
    public function destroy(string $id): void
    {
        Post::delete((int)$id);

        header('Location: /posts');
        exit;
    }
}
