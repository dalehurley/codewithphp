<?php

declare(strict_types=1);

namespace Controllers;

use Models\User;

/**
 * User Controller
 * 
 * Handles user-related operations.
 */

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Display user profile
     */
    public function show(string $id): void
    {
        $user = $this->userModel->find((int)$id);

        if ($user === null) {
            http_response_code(404);
            $this->view('404');
            return;
        }

        $this->view('users/show', [
            'title' => $user['name'] . "'s Profile",
            'user' => $user
        ]);
    }
}
