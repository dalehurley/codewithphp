<?php

// filename: json-error-location.php

declare(strict_types=1);

$document = "{\n  \"items\": [1, 2,]\n}";

try {
    json_decode($document, flags: JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    echo $error->getMessage(), PHP_EOL;
}

json_decode($document);
echo json_last_error_msg(), PHP_EOL;
