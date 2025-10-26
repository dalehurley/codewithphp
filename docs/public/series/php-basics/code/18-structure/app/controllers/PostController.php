<?php

declare(strict_types=1);

namespace Controllers;

use Models\Post;

/**
 * Post Controller
 * 
 * Handles blog post CRUD operations.
 */

class PostController extends Controller
{
    private Post $postModel;

    public function __construct()
    {
        $this->postModel = new Post();
    }

    /**
     * Display all posts
     */
    public function index(): void
    {
        $posts = $this->postModel->all();

        $this->view('posts/index', [
            'title' => 'All Posts',
            'posts' => $posts
        ]);
    }

    /**
     * Display single post
     */
    public function show(string $id): void
    {
        $post = $this->postModel->find((int)$id);

        if ($post === null) {
            http_response_code(404);
            $this->view('404');
            return;
        }

        $this->view('posts/show', [
            'title' => $post['title'],
            'post' => $post
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->view('posts/create', [
            'title' => 'Create New Post'
        ]);
    }

    /**
     * Store new post
     */
    public function store(): void
    {
        $errors = $this->validate([
            'title' => 'required',
            'content' => 'required'
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('/posts/create');
        }

        $this->postModel->create([
            'title' => $this->input('title'),
            'content' => $this->input('content'),
            'author' => 'Admin' // In real app, get from session
        ]);

        $_SESSION['success'] = 'Post created successfully!';
        $this->redirect('/posts');
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $post = $this->postModel->find((int)$id);

        if ($post === null) {
            $this->redirect('/posts');
        }

        $this->view('posts/edit', [
            'title' => 'Edit Post',
            'post' => $post
        ]);
    }

    /**
     * Update post
     */
    public function update(string $id): void
    {
        $errors = $this->validate([
            'title' => 'required',
            'content' => 'required'
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect("/posts/{$id}/edit");
        }

        $this->postModel->update((int)$id, [
            'title' => $this->input('title'),
            'content' => $this->input('content')
        ]);

        $_SESSION['success'] = 'Post updated successfully!';
        $this->redirect('/posts/' . $id);
    }

    /**
     * Delete post
     */
    public function destroy(string $id): void
    {
        $this->postModel->delete((int)$id);

        $_SESSION['success'] = 'Post deleted successfully!';
        $this->redirect('/posts');
    }
}
