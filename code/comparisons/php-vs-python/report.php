<?php
declare(strict_types=1);

use Pdo\Sqlite;

if ($argc !== 2 || trim($argv[1]) === '') {
    fwrite(STDERR, "Usage: php report.php CUSTOMER\n");
    exit(2);
}

$path = __DIR__ . '/invoices.sqlite';
if (!is_file($path)) {
    fwrite(STDERR, "Create invoices.sqlite from invoices.sql first.\n");
    exit(1);
}

try {
    $db = new Sqlite('sqlite:' . $path, options: [
        Sqlite::ATTR_OPEN_FLAGS => Sqlite::OPEN_READONLY,
    ]);
    $query = $db->prepare(<<<'SQL'
        SELECT COUNT(i.customer), COALESCE(SUM(i.cents), 0)
        FROM customers AS c
        LEFT JOIN invoices AS i
            ON i.customer = c.slug AND i.status = 'paid'
        WHERE c.slug = :customer
        GROUP BY c.slug
        SQL);
    $query->execute(['customer' => $argv[1]]);
    $row = $query->fetch(PDO::FETCH_NUM);
} catch (PDOException) {
    fwrite(STDERR, "Could not read the invoice database. Check that the fixture was loaded.\n");
    exit(1);
}

if ($row === false) {
    fwrite(STDERR, "Unknown customer.\n");
    exit(3);
}

[$count, $total] = $row;
printf("Paid invoices = %d, total = %d cents\n", $count, $total);
