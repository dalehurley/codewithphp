<?php
declare(strict_types=1);

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) {
    header('Allow: GET, HEAD, POST');
    header('Content-Type: text/plain; charset=UTF-8');
    http_response_code(405);
    exit('Method not allowed.');
}

header('Content-Type: text/html; charset=UTF-8');
if ($method === 'HEAD') {
    exit;
}

$quantity = null;
$message = '';
$invalid = false;

if ($method === 'POST') {
    header('Vary: Accept');
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 10],
    ]);

    if (is_int($quantity)) {
        $unit = $quantity === 1 ? 'copy' : 'copies';
        $total = number_format($quantity * 450 / 100, 2, '.', '');
        $message = "Estimated total for $quantity $unit is \$$total.";
    } else {
        $invalid = true;
        http_response_code(422);
        $message = 'Enter a whole number from 1 to 10.';
    }

    if (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') {
        header('Content-Type: application/json');
        echo json_encode(['message' => $message], JSON_THROW_ON_ERROR);
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Print quote</title>
  <script src="quote.js" defer></script>
</head>
<body>
  <h1>Print quote</h1>
  <form id="quote-form" action="index.php" method="post">
    <label for="quantity">Number of copies</label>
    <input id="quantity" name="quantity" type="number"
           value="<?= is_int($quantity) ? $quantity : '' ?>"
           min="1" max="10" step="1" required
           aria-describedby="quantity-hint quote-result"
           aria-invalid="<?= $invalid ? 'true' : 'false' ?>">
    <p id="quantity-hint">Enter a whole number from 1 to 10.</p>
    <button type="submit">Get quote</button>
  </form>
  <p id="quote-result" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
</body>
</html>
