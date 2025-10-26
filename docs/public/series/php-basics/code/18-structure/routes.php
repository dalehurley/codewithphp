<?php

declare(strict_types=1);

/**
 * Application Routes
 * 
 * Define all application routes here.
 */

use Controllers\HomeController;
use Controllers\PostController;
use Controllers\UserController;

// Home routes
$router->get('/', [new HomeController(), 'index']);
$router->get('/about', [new HomeController(), 'about']);

// Post routes (RESTful)
$postController = new PostController();
$router->get('/posts', [$postController, 'index']);
$router->get('/posts/create', [$postController, 'create']);
$router->post('/posts', [$postController, 'store']);
$router->get('/posts/{id}', [$postController, 'show']);
$router->get('/posts/{id}/edit', [$postController, 'edit']);
$router->post('/posts/{id}/update', [$postController, 'update']);
$router->post('/posts/{id}/delete', [$postController, 'destroy']);

// User routes
$userController = new UserController();
$router->get('/users/{id}', [$userController, 'show']);
