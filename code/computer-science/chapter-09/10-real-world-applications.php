<?php
declare(strict_types=1);

echo "=== Real-World Recursion Applications ===\n\n";

// 1. File system traversal
function listFiles(string $dir, int $depth = 0): void {
    if (!is_dir($dir)) return;
    
    $indent = str_repeat("  ", $depth);
    echo $indent . basename($dir) . "/\n";
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            listFiles($path, $depth + 1);
        } else {
            echo $indent . "  " . $file . "\n";
        }
    }
}

// 2. JSON parsing (nested structures)
function flattenJSON(mixed $data, string $prefix = ''): array {
    $result = [];
    
    if (is_array($data) || is_object($data)) {
        foreach ($data as $key => $value) {
            $newKey = $prefix ? "$prefix.$key" : $key;
            $result = array_merge($result, flattenJSON($value, $newKey));
        }
    } else {
        $result[$prefix] = $data;
    }
    
    return $result;
}

// 3. Expression evaluation
function evaluate(string $expr): int {
    $expr = trim($expr);
    
    if (is_numeric($expr)) {
        return (int)$expr;
    }
    
    // Simple parser for expressions like "2 + 3"
    if (preg_match('/(\d+)\s*([+\-*\/])\s*(.+)/', $expr, $matches)) {
        $left = (int)$matches[1];
        $op = $matches[2];
        $right = evaluate($matches[3]);
        
        return match($op) {
            '+' => $left + $right,
            '-' => $left - $right,
            '*' => $left * $right,
            '/' => (int)($left / $right),
        };
    }
    
    return 0;
}

// Tests
echo "1. File System Traversal\n";
echo "   (Simulated - actual directory listing omitted for brevity)\n\n";

echo "2. JSON Flattening\n";
$json = ['user' => ['name' => 'John', 'address' => ['city' => 'NYC']]];
$flat = flattenJSON($json);
foreach ($flat as $key => $value) {
    echo "   $key => $value\n";
}

echo "\n3. Expression Evaluation\n";
echo "   2 + 3 * 4 = " . evaluate("2 + 3 * 4") . "\n";

echo "\nReal-world recursion uses:\n";
echo "  • File system operations\n";
echo "  • Parsing (JSON, XML, expressions)\n";
echo "  • Tree traversals (DOM, AST)\n";
echo "  • Graph algorithms (DFS, pathfinding)\n";
echo "  • Backtracking (Sudoku, N-Queens)\n";

echo "\n✅ Complete!\n";
