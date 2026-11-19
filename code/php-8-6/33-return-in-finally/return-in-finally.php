<?php

// filename: return-in-finally.php

declare(strict_types=1);

function writeNote(string $note, bool $simulateFailure = false): int
{
    $stream = fopen('php://temp', 'w+');
    if ($stream === false) {
        throw new RuntimeException('Cannot open temp stream');
    }

    try {
        if ($simulateFailure) {
            throw new RuntimeException('Write rejected');
        }
        $written = fwrite($stream, $note);
        if ($written === false || $written !== strlen($note)) {
            throw new RuntimeException('Incomplete write');
        }
        return $written;
    } finally {
        fclose($stream);
    }
}

echo writeNote('hello'), " bytes\n";
try {
    writeNote('hello', true);
} catch (RuntimeException $error) {
    echo $error->getMessage(), PHP_EOL;
}
