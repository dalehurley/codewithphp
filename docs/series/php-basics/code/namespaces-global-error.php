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

echo "This file demonstrates a common namespace error...\n\n";
echo "When you don't import DateTime with 'use', PHP looks for it in the current namespace!\n";
echo "Expected: \\DateTime (global)\n";
echo "Actual:   App\\Utils\\DateTime (doesn't exist)\n\n";

try {
    $helper = new TimeHelper();
    echo $helper->getCurrentTime();
} catch (\Error $e) {
    echo "❌ Error caught (as expected):\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "✅ Fix: Add 'use DateTime;' at the top of the TimeHelper class\n";
    echo "   Or use: new \\DateTime() to reference the global class\n";
    exit(0); // Exit successfully to show this is an educational example
}
