<?php

declare(strict_types=1);

namespace App;

class ErrorRecovery
{
    public function withRetry(callable $action, int $maxRetries = 3)
    {
        return function(...$args) use ($action, $maxRetries) {
            $lastException = null;
            for ($i = 0; $i < $maxRetries; $i++) {
                try {
                    return $action(...$args);
                } catch (\Throwable $e) {
                    $lastException = $e;
                    sleep(pow(2, $i));
                }
            }
            throw $lastException;
        };
    }
}
