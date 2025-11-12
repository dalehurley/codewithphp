<?php

namespace App\Utils;

class Logger
{
    public static function log(string $message): void
    {
        echo "[Utils Logger] $message" . PHP_EOL;
    }
}
