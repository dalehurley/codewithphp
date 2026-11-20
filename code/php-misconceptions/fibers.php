<?php

declare(strict_types=1);

$blocking = ($argv[1] ?? null) === '--blocking';

$fiber = new Fiber(static function () use ($blocking): void {
    echo '1. Fiber starts its work', PHP_EOL;
    if ($blocking) {
        echo '   Blocking call starts', PHP_EOL;
        usleep(200_000);
        echo '   Blocking call returns', PHP_EOL;
    }
    Fiber::suspend();
    echo '3. Fiber finishes its work', PHP_EOL;
});

$fiber->start();
echo '2. Caller runs while the fiber is paused', PHP_EOL;
$fiber->resume();
