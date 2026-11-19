<?php

declare(strict_types=1);

$fiber = new Fiber(static function (): void {
    echo '1. Fiber starts its work', PHP_EOL;
    Fiber::suspend();
    echo '3. Fiber finishes its work', PHP_EOL;
});

$fiber->start();
echo '2. Caller runs while the fiber is paused', PHP_EOL;
$fiber->resume();
