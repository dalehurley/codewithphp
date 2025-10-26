<?php

declare(strict_types=1);

/**
 * Application Routes
 * 
 * Define all application routes here.
 * 
 * Note: This file is meant to be included by the main application.
 * For standalone testing, a mock router is provided.
 */

// For standalone testing: Create mock router and classes if not already loaded
if (!isset($router)) {
    echo "📝 Routes File - Testing Mode" . PHP_EOL;
    echo "This file defines application routes and is meant to be included by the main app." . PHP_EOL;
    echo "In a real application, routes would be:" . PHP_EOL . PHP_EOL;

    echo "Home Routes:" . PHP_EOL;
    echo "  GET  /              -> HomeController@index" . PHP_EOL;
    echo "  GET  /about         -> HomeController@about" . PHP_EOL . PHP_EOL;

    echo "Post Routes (RESTful):" . PHP_EOL;
    echo "  GET  /posts         -> PostController@index" . PHP_EOL;
    echo "  GET  /posts/create  -> PostController@create" . PHP_EOL;
    echo "  POST /posts         -> PostController@store" . PHP_EOL;
    echo "  GET  /posts/{id}    -> PostController@show" . PHP_EOL;
    echo "  GET  /posts/{id}/edit -> PostController@edit" . PHP_EOL;
    echo "  POST /posts/{id}/update -> PostController@update" . PHP_EOL;
    echo "  POST /posts/{id}/delete -> PostController@destroy" . PHP_EOL . PHP_EOL;

    echo "User Routes:" . PHP_EOL;
    echo "  GET  /users/{id}    -> UserController@show" . PHP_EOL;

    exit(0);
}

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
