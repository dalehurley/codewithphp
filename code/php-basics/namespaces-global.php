<?php

namespace App\Utils;

// Import DateTime from the global namespace
use DateTime;

class TimeHelper
{
    public function getCurrentTime(): string
    {
        // Now DateTime is recognized (thanks to the use statement)
        $now = new DateTime();
        return $now->format('Y-m-d H:i:s');
    }

    public function getCurrentTimeWithBackslash(): string
    {
        // Alternative: use fully qualified name with leading backslash
        $now = new \DateTime();
        return $now->format('Y-m-d H:i:s');
    }
}

$helper = new TimeHelper();
echo "Using 'use DateTime': " . $helper->getCurrentTime() . PHP_EOL;
echo "Using \\DateTime:      " . $helper->getCurrentTimeWithBackslash() . PHP_EOL;

// This demonstrates that both approaches work the same way
