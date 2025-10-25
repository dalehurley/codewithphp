<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My PHP App' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
        }

        header h1 {
            font-size: 1.5rem;
        }

        nav {
            margin-top: 1rem;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 1.5rem;
        }

        nav a:hover {
            text-decoration: underline;
        }

        main {
            padding: 2rem 0;
            min-height: 60vh;
        }

        footer {
            background: #34495e;
            color: white;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>My PHP App</h1>
            <nav>
                <a href="/">Home</a>
                <a href="/about">About</a>
                <a href="/posts">Posts</a>
                <a href="/posts/create">New Post</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-error">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <?php require $viewPath; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> My PHP App. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>