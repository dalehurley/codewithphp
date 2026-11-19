<?php

// filename: mysqli-charset-and-statements.php

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
    echo $db->character_set_name(), PHP_EOL;
    $result = $db->execute_query('SELECT ? AS message', ["Today's specials"]);
    echo $result->fetch_assoc()['message'], PHP_EOL;
} finally {
    $db->close();
}
