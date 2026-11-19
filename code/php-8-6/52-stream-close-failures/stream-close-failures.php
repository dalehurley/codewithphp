<?php

// filename: stream-close-failures.php

declare(strict_types=1);

final class BufferedSink
{
    public mixed $context;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return true;
    }

    public function stream_write(string $data): int
    {
        return strlen($data);
    }

    public function stream_flush(): bool
    {
        return false;
    }
}

if (!stream_wrapper_register('tutorialsink', BufferedSink::class)) {
    throw new RuntimeException('Cannot register test stream');
}
try {
    $stream = fopen('tutorialsink://output', 'w');
    if ($stream === false) {
        throw new RuntimeException('Cannot open test stream');
    }
    var_dump(fwrite($stream, 'sample'));
    var_dump(fclose($stream));
    var_dump(file_put_contents('tutorialsink://output', 'sample'));
    var_dump(copy('data://text/plain,sample', 'tutorialsink://output'));
} finally {
    stream_wrapper_unregister('tutorialsink');
}
