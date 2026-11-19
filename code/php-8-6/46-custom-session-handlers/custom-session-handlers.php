<?php

// filename: custom-session-handlers.php

declare(strict_types=1);

ini_set('session.save_handler', 'files');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_cookies', '0');
session_cache_limiter('');

$storage = sys_get_temp_dir() . '/php86-sessions-' . bin2hex(random_bytes(8));
if (!mkdir($storage, 0700)) {
    throw new RuntimeException('Cannot create session storage');
}

final class FileSessionHandler extends SessionHandler
{
    public function validateId(string $id): bool
    {
        return parent::validateId($id);
    }
}

try {
    session_save_path($storage);
    session_set_save_handler(new FileSessionHandler(), false);
    $unknown = 'discard-this-unknown-id';
    session_id($unknown);
    if (!session_start()) {
        throw new RuntimeException('Could not start the session');
    }
    $replaced = session_id() !== $unknown;
    $_SESSION['visits'] = 1;
    session_destroy();
    echo 'Unknown ID replaced: ', $replaced ? 'yes' : 'no', PHP_EOL;
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    foreach (glob($storage . '/sess_*') ?: [] as $path) {
        unlink($path);
    }
    rmdir($storage);
}
