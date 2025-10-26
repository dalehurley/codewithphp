<?php

trait Logger
{
    public function log(string $message): void
    {
        echo "[LOG] $message" . PHP_EOL;
    }
}

trait Debugger
{
    public function log(string $message): void
    {
        echo "[DEBUG] $message" . PHP_EOL;
    }
}

class Application
{
    // Use both traits
    use Logger, Debugger {
        // Resolve the conflict: specify which log() to use
        Logger::log insteadof Debugger;
        // Optionally, keep the other as an alias
        Debugger::log as debugLog;
    }
}

$app = new Application();
$app->log("This uses Logger's method");
$app->debugLog("This uses Debugger's method");
