<?php

# filename: examples/check-memory.php

echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Current Usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n";
echo "Peak Usage: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
