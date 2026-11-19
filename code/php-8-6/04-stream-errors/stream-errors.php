<?php

// filename: stream-errors.php

declare(strict_types=1);

$missingPath = sys_get_temp_dir() . '/settings-' . bin2hex(random_bytes(8)) . '.json';
$throwing = stream_context_create(['stream' => ['error_mode' => StreamErrorMode::Exception]]);

try {
    file_get_contents($missingPath, context: $throwing);
} catch (StreamException $exception) {
    foreach ($exception->getErrors() as $error) {
        printf("Caught %s from %s\n", $error->code->name, $error->wrapperName);
    }
}

stream_clear_errors();
$silent = stream_context_create([
    'stream' => ['error_mode' => StreamErrorMode::Silent, 'error_store' => StreamErrorStore::All],
]);
if (file_get_contents($missingPath, context: $silent) === false) {
    printf("Stored errors: %d\n", count(stream_last_errors()));
}
