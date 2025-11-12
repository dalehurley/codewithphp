<?php

namespace App\Database;

class Logger
{
    public static function log(string $message): void
    {
        echo "[Database Logger] $message" . PHP_EOL;
    }
}
