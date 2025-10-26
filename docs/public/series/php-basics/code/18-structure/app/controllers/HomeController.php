<?php

declare(strict_types=1);

namespace Controllers;

/**
 * Home Controller
 * 
 * Handles home page and static pages.
 */

class HomeController extends Controller
{
    public function index(): void
    {
        $data = [
            'title' => 'Home Page',
            'message' => 'Welcome to our MVC application!'
        ];

        $this->view('home/index', $data);
    }

    public function about(): void
    {
        $data = [
            'title' => 'About Us',
            'content' => 'This is a simple MVC application structure.'
        ];

        $this->view('home/about', $data);
    }
}
