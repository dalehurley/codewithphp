<?php

declare(strict_types=1);

function escapeHtml(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (!extension_loaded('pdo_sqlite')) {
    http_response_code(500);
    exit('Enable PDO SQLite in PHP, then restart the development server.');
}

session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

$error = null;
$title = '';

try {
    $storage = dirname(__DIR__) . '/storage';
    if (!is_dir($storage) && !mkdir($storage, 0775) && !is_dir($storage)) {
        throw new RuntimeException('Cannot create the SQLite storage directory.');
    }

    $db = new PDO('sqlite:' . $storage . '/reading-list.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->exec('CREATE TABLE IF NOT EXISTS books (
        id INTEGER PRIMARY KEY,
        title TEXT NOT NULL,
        is_read INTEGER NOT NULL DEFAULT 0 CHECK (is_read IN (0, 1))
    )');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = $_POST['csrf'] ?? '';
        if (!is_string($csrf) || !hash_equals($_SESSION['csrf'], $csrf)) {
            http_response_code(403);
            exit('The form expired. Reload the page and try again.');
        }

        switch ($_POST['action'] ?? '') {
            case 'add':
                $title = is_string($_POST['title'] ?? null) ? trim($_POST['title']) : '';
                // Match up to 120 Unicode characters using PHP's built-in regex support.
                if (preg_match('/\A.{1,120}\z/us', $title) !== 1) {
                    $error = 'Enter a title between 1 and 120 characters.';
                    http_response_code(422);
                    break;
                }

                $insert = $db->prepare('INSERT INTO books (title) VALUES (:title)');
                $insert->execute(['title' => $title]);
                break;

            case 'read':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if (!$id) {
                    http_response_code(400);
                    exit('Choose a valid book.');
                }

                $update = $db->prepare('UPDATE books SET is_read = 1 WHERE id = :id');
                $update->execute(['id' => $id]);
                break;

            default:
                http_response_code(400);
                exit('Unknown form action.');
        }

        if ($error === null) {
            header('Location: /', true, 303);
            exit;
        }
    }

    $books = $db->query('SELECT id, title, is_read FROM books ORDER BY id DESC')->fetchAll();
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('SQLite could not complete the request. Check the development server log and storage directory.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reading List</title>
    <style>
        body { max-width: 40rem; margin: 2rem auto; padding: 0 1rem; font: 1rem/1.5 system-ui, sans-serif; }
        label, input[type="text"] { display: block; }
        input[type="text"] { box-sizing: border-box; width: min(100%, 28rem); padding: .5rem; margin: .25rem 0 .5rem; }
        input, button { font: inherit; }
        li { margin-block: .75rem; }
        li form { display: inline; margin-inline-start: .5rem; }
        [role="alert"] { color: #a02424; }
    </style>
</head>
<body>
<main>
    <h1>Reading List</h1>

    <?php if ($error !== null): ?>
        <p role="alert"><?= escapeHtml($error) ?></p>
    <?php endif; ?>

    <form method="post" action="/">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="csrf" value="<?= escapeHtml($_SESSION['csrf']) ?>">
        <label for="title">Book title</label>
        <input type="text" id="title" name="title" value="<?= escapeHtml($title) ?>" required>
        <button type="submit">Add book</button>
    </form>

    <h2>Books</h2>
    <?php if ($books === []): ?>
        <p>Your list is empty. Add a book to get started.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($books as $book): ?>
                <li>
                    <?= escapeHtml($book['title']) ?>
                    <?php if ($book['is_read']): ?>
                        <span>(Read)</span>
                    <?php else: ?>
                        <form method="post" action="/">
                            <input type="hidden" name="action" value="read">
                            <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                            <input type="hidden" name="csrf" value="<?= escapeHtml($_SESSION['csrf']) ?>">
                            <button type="submit" aria-label="Mark <?= escapeHtml($book['title']) ?> as read">Mark as read</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>
</body>
</html>
