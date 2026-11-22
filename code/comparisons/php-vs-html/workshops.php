<?php
declare(strict_types=1);

function escape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET' && $method !== 'HEAD') {
    header('Allow: GET, HEAD');
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(405);
    exit('Use GET or HEAD for the workshop schedule.');
}

$workshops = [
    'saturday' => ['HTML & accessibility', 'From <form> to PHP'],
    'sunday' => ['Write your first PHP page'],
    'monday' => [],
];

$day = $_GET['day'] ?? null;
$error = null;
$title = 'Choose a workshop day';
$sessions = [];

if ($day === null || $day === '') {
    $error = 'Choose a day on the workshop welcome page.';
} elseif (!is_string($day) || !array_key_exists($day, $workshops)) {
    $error = 'Choose Saturday, Sunday or Monday.';
} else {
    $title = 'Workshops on ' . ucfirst($day);
    $sessions = $workshops[$day];
}

header('Content-Type: text/html; charset=UTF-8');
if ($error !== null) {
    http_response_code(400);
}
if ($method === 'HEAD') {
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= escape($title) ?></title>
</head>
<body>
  <main>
    <h1><?= escape($title) ?></h1>
    <?php if ($error !== null): ?>
      <p><?= escape($error) ?></p>
    <?php elseif ($sessions === []): ?>
      <p>No workshops are scheduled for this day.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($sessions as $session): ?>
          <li><?= escape($session) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <p><a href="index.html">Back to the workshop form</a></p>
  </main>
</body>
</html>
