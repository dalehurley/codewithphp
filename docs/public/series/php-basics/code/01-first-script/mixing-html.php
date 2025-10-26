<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP and HTML</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .info-box {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <h1>Mixing PHP with HTML</h1>

    <?php
    // PHP can be embedded anywhere in HTML
    $currentTime = date('F j, Y, g:i a');
    $userName = "Developer";
    ?>

    <div class="info-box">
        <p><strong>Current Date & Time:</strong> <?php echo $currentTime; ?></p>
        <p><strong>Welcome:</strong> <?php echo $userName; ?></p>
    </div>

    <h2>Dynamic List</h2>
    <ul>
        <?php
        // Generate list items dynamically
        $items = ["HTML", "CSS", "JavaScript", "PHP"];

        foreach ($items as $item) {
            echo "<li>$item</li>" . PHP_EOL;
        }
        ?>
    </ul>

    <h2>Conditional Content</h2>
    <?php
    $hour = (int)date('H');

    if ($hour < 12) {
        echo "<p>Good morning! ☀️</p>";
    } elseif ($hour < 18) {
        echo "<p>Good afternoon! 🌤️</p>";
    } else {
        echo "<p>Good evening! 🌙</p>";
    }
    ?>
</body>

</html>