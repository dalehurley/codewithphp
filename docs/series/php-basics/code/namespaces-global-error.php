<?php

namespace App\Utils;

// Notice: NO use statement for DateTime
// This file demonstrates the common mistake!

class TimeHelper
{
    public function getCurrentTime(): string
    {
        // This will look for App\Utils\DateTime - which doesn't exist!
        // PHP will throw: Fatal error: Class "App\Utils\DateTime" not found
        $now = new DateTime();
        return $now->format('Y-m-d H:i:s');
    }
}

echo "This file will produce an error to demonstrate the problem...\n";
$helper = new TimeHelper();
echo $helper->getCurrentTime();
