<?php

// filename: error-include-args.php

declare(strict_types=1);

set_error_handler(static function (int $level, string $message): bool {
    echo $message, PHP_EOL;
    return true;
}, E_WARNING);

try {
    foreach (['0', '1'] as $enabled) {
        ini_set('error_include_args', $enabled);
        echo "Setting $enabled: ";
        unlink('php://memory');
    }
} finally {
    restore_error_handler();
}
