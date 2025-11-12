<?php

declare(strict_types=1);

/**
 * Exercise 2: Simple Calculator
 * 
 * Create a calculator form that accepts two numbers and an operation.
 * Display the result of the calculation.
 */

$num1 = '';
$num2 = '';
$operation = '';
$result = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num1 = $_POST['num1'] ?? '';
    $num2 = $_POST['num2'] ?? '';
    $operation = $_POST['operation'] ?? '';

    // Validate inputs
    if ($num1 === '' || !is_numeric($num1)) {
        $errors[] = 'First number must be a valid number';
    }

    if ($num2 === '' || !is_numeric($num2)) {
        $errors[] = 'Second number must be a valid number';
    }

    if (empty($operation)) {
        $errors[] = 'Please select an operation';
    }

    // Perform calculation if no errors
    if (empty($errors)) {
        $n1 = (float)$num1;
        $n2 = (float)$num2;

        $result = match ($operation) {
            'add' => $n1 + $n2,
            'subtract' => $n1 - $n2,
            'multiply' => $n1 * $n2,
            'divide' => $n2 != 0 ? $n1 / $n2 : null,
            default => null
        };

        if ($operation === 'divide' && $n2 == 0) {
            $errors[] = 'Cannot divide by zero';
            $result = null;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculator - Exercise 2</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .calculator {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #218838;
        }

        .result-box {
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }

        .error-box ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <div class="calculator">
        <h1>🧮 Calculator</h1>

        <form method="POST" action="exercise-2-calculator.php">
            <div class="form-group">
                <label for="num1">First Number:</label>
                <input
                    type="number"
                    id="num1"
                    name="num1"
                    step="any"
                    value="<?= htmlspecialchars($num1) ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="operation">Operation:</label>
                <select id="operation" name="operation" required>
                    <option value="">-- Select Operation --</option>
                    <option value="add" <?= $operation === 'add' ? 'selected' : '' ?>>➕ Addition (+)</option>
                    <option value="subtract" <?= $operation === 'subtract' ? 'selected' : '' ?>>➖ Subtraction (-)</option>
                    <option value="multiply" <?= $operation === 'multiply' ? 'selected' : '' ?>>✖️ Multiplication (×)</option>
                    <option value="divide" <?= $operation === 'divide' ? 'selected' : '' ?>>➗ Division (÷)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="num2">Second Number:</label>
                <input
                    type="number"
                    id="num2"
                    name="num2"
                    step="any"
                    value="<?= htmlspecialchars($num2) ?>"
                    required>
            </div>

            <button type="submit">Calculate</button>
        </form>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <strong>Errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($result !== null): ?>
            <div class="result-box">
                <?php
                $operationSymbol = match ($operation) {
                    'add' => '+',
                    'subtract' => '-',
                    'multiply' => '×',
                    'divide' => '÷',
                    default => ''
                };
                ?>
                <?= htmlspecialchars($num1) ?> <?= $operationSymbol ?> <?= htmlspecialchars($num2) ?> = <?= number_format($result, 4) ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>