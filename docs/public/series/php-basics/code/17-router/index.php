<?php

declare(strict_types=1);

/**
 * Router Demo - Front Controller
 * 
 * This file demonstrates using the Router class.
 * In a real app, this would be your public/index.php
 */

require_once __DIR__ . '/Router.php';

$router = new Router();

// Define routes
$router->get('/', function () {
    echo "<h1>Home Page</h1>";
    echo "<p>Welcome to our website!</p>";
    echo '<ul>';
    echo '<li><a href="/about">About</a></li>';
    echo '<li><a href="/contact">Contact</a></li>';
    echo '<li><a href="/posts">Blog Posts</a></li>';
    echo '<li><a href="/posts/123">Single Post</a></li>';
    echo '<li><a href="/users/john/profile">User Profile</a></li>';
    echo '</ul>';
});

$router->get('/about', function () {
    echo "<h1>About Us</h1>";
    echo "<p>We are learning PHP routing!</p>";
    echo '<p><a href="/">Back to Home</a></p>';
});

$router->get('/contact', function () {
    echo "<h1>Contact Us</h1>";
    echo "<p>Email: contact@example.com</p>";
    echo '<p><a href="/">Back to Home</a></p>';
});

$router->get('/posts', function () {
    echo "<h1>Blog Posts</h1>";
    echo "<ul>";
    echo "<li><a href='/posts/1'>First Post</a></li>";
    echo "<li><a href='/posts/2'>Second Post</a></li>";
    echo "<li><a href='/posts/3'>Third Post</a></li>";
    echo "</ul>";
    echo '<p><a href="/">Back to Home</a></p>';
});

// Route with parameter
$router->get('/posts/{id}', function (string $id) {
    echo "<h1>Blog Post #{$id}</h1>";
    echo "<p>Content for post {$id} would go here.</p>";
    echo '<p><a href="/posts">Back to Posts</a></p>';
});

// Multiple parameters
$router->get('/users/{username}/profile', function (string $username) {
    echo "<h1>User Profile</h1>";
    echo "<p>Username: {$username}</p>";
    echo "<p>This would display profile information.</p>";
    echo '<p><a href="/">Back to Home</a></p>';
});

// POST route example
$router->post('/submit', function () {
    echo "<h1>Form Submitted</h1>";
    echo "<p>Thank you for your submission!</p>";
});

// Dispatch the request
$router->dispatch();
