<?php

// filename: mysqli-quote-string.php

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(
    getenv('MYSQL_HOST') ?: '127.0.0.1',
    getenv('MYSQL_USER') ?: 'root',
    getenv('MYSQL_PASSWORD') ?: '',
    port: (int) (getenv('MYSQL_PORT') ?: '3306'),
);
try {
    $db->set_charset('utf8mb4');
    $quoted = $db->quote_string("O'Reilly");
    $result = $db->query("SELECT $quoted AS label");
    echo $result->fetch_assoc()['label'], PHP_EOL;
} finally {
    $db->close();
}
