<?php

// filename: splfileobject-csv.php

declare(strict_types=1);

$stream = fopen('php://temp', 'w+');
if ($stream === false) {
    throw new RuntimeException('Cannot open CSV stream');
}

try {
    $fields = ['42', 'Dinner, Hobart', 'Said "hello"'];
    if (fputcsv($stream, $fields, escape: '') === false || !rewind($stream)) {
        throw new RuntimeException('Cannot write and rewind CSV');
    }
    $row = fgetcsv($stream, escape: '');
    echo 'Round trip: ', $fields === $row ? 'yes' : 'no', PHP_EOL;
    echo json_encode($row, JSON_THROW_ON_ERROR), PHP_EOL;
} finally {
    fclose($stream);
}
