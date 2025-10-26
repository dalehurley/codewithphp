#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Environment Verification Script for AI/ML PHP Development
 * 
 * This script checks that all required components are installed and working:
 * - PHP 8.4+
 * - Required extensions
 * - Composer
 * - ML libraries (PHP-ML, Rubix ML)
 * - Optional components (Python, Tensor)
 */

echo "\n🔍 AI/ML PHP Environment Verification\n";
echo "=====================================\n\n";

$checks = [];
$failures = 0;

// Check 1: PHP Version
echo "1. Checking PHP version... ";
$phpVersion = PHP_VERSION;
$versionOk = version_compare($phpVersion, '8.4.0', '>=');
$checks['PHP 8.4+'] = $versionOk;

if ($versionOk) {
    echo "✅ PHP $phpVersion\n";
} else {
    echo "❌ PHP $phpVersion (need 8.4+)\n";
    $failures++;
}

// Check 2: Required Extensions
echo "2. Checking required extensions...\n";
$requiredExtensions = ['json', 'mbstring', 'curl', 'dom', 'zip'];
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $checks["Extension: $ext"] = $loaded;

    if ($loaded) {
        echo "   ✅ $ext\n";
    } else {
        echo "   ❌ $ext (missing)\n";
        $failures++;
    }
}

// Check 3: Composer
echo "3. Checking Composer... ";
exec('composer --version 2>&1', $output, $returnCode);
$composerOk = $returnCode === 0;
$checks['Composer'] = $composerOk;

if ($composerOk) {
    echo "✅ Installed\n";
} else {
    echo "❌ Not found\n";
    $failures++;
}

// Check 4: Autoloader
echo "4. Checking Composer autoloader... ";
$autoloadPath = __DIR__ . '/vendor/autoload.php';
$autoloadOk = file_exists($autoloadPath);
$checks['Autoloader'] = $autoloadOk;

if ($autoloadOk) {
    echo "✅ Found\n";
    require $autoloadPath;
} else {
    echo "❌ Not found (run 'composer install')\n";
    $failures++;
    echo "\n⚠️  Cannot proceed without autoloader. Run 'composer install' first.\n\n";
    exit(1);
}

// Check 5: PHP-ML
echo "5. Checking PHP-ML library... ";
try {
    $phpmlClass = class_exists('Phpml\Classification\KNearestNeighbors');
    $checks['PHP-ML'] = $phpmlClass;

    if ($phpmlClass) {
        echo "✅ Installed\n";
    } else {
        echo "❌ Not found\n";
        $failures++;
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    $checks['PHP-ML'] = false;
    $failures++;
}

// Check 6: Rubix ML
echo "6. Checking Rubix ML library... ";
try {
    $rubixml = class_exists('Rubix\ML\Classifiers\KNearestNeighbors');
    $checks['Rubix ML'] = $rubixml;

    if ($rubixml) {
        echo "✅ Installed\n";
    } else {
        echo "❌ Not found\n";
        $failures++;
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    $checks['Rubix ML'] = false;
    $failures++;
}

// Check 7: Optional - Tensor extension
echo "7. Checking Rubix Tensor (optional)... ";
try {
    $tensor = class_exists('Tensor\Matrix');
    $checks['Rubix Tensor'] = $tensor;

    if ($tensor) {
        echo "✅ Installed (performance boost enabled)\n";
    } else {
        echo "⚠️  Not installed (optional, but recommended for speed)\n";
    }
} catch (Exception $e) {
    echo "⚠️  Not installed (optional)\n";
    $checks['Rubix Tensor'] = false;
}

// Check 8: Python (optional)
echo "8. Checking Python (optional)... ";
exec('python3 --version 2>&1', $pythonOutput, $pythonCode);
$pythonOk = $pythonCode === 0 && strpos($pythonOutput[0] ?? '', 'Python 3') !== false;
$checks['Python 3'] = $pythonOk;

if ($pythonOk) {
    echo "✅ {$pythonOutput[0]}\n";
} else {
    echo "⚠️  Not found (optional, needed for advanced chapters)\n";
}

// Check 9: Disk Space
echo "9. Checking disk space... ";
$diskSpace = disk_free_space(__DIR__);
$diskSpaceGB = round($diskSpace / (1024 * 1024 * 1024), 1);
$diskOk = $diskSpace > (1024 * 1024 * 1024); // 1GB minimum
$checks['Disk Space'] = $diskOk;

if ($diskOk) {
    echo "✅ {$diskSpaceGB} GB available\n";
} else {
    echo "⚠️  Only {$diskSpaceGB} GB available (1+ GB recommended)\n";
}

// Check 10: Project Directories
echo "10. Checking project directories... ";
$projectDirs = ['src', 'tests', 'data', 'models'];
$dirsOk = true;
$missingDirs = [];

foreach ($projectDirs as $dir) {
    $dirPath = __DIR__ . '/' . $dir;
    if (!is_dir($dirPath)) {
        $dirsOk = false;
        $missingDirs[] = $dir;
        $checks["Directory: $dir"] = false;
    } else {
        $writable = is_writable($dirPath);
        $checks["Directory: $dir"] = $writable;

        if ($writable) {
            echo "   ✅ $dir (writable)\n";
        } else {
            echo "   ⚠️  $dir (not writable)\n";
            $failures++;
        }
    }
}

if (!$dirsOk) {
    echo "   ❌ Missing directories: " . implode(', ', $missingDirs) . "\n";
    $failures++;
} elseif (empty($missingDirs)) {
    echo "✅ All directories present\n";
}

// Summary
echo "\n=====================================\n";
echo "📊 Summary\n";
echo "=====================================\n";

$passed = count(array_filter($checks, fn($v) => $v === true));
$total = count($checks);
$criticalFailures = $failures;

echo "Passed: $passed / $total checks\n";
echo "Critical failures: $criticalFailures\n";

if ($criticalFailures === 0) {
    echo "\n🎉 Success! Your environment is ready for AI/ML development.\n";
    echo "You can proceed to Chapter 03.\n\n";

    // Show recommendations for optional components
    $optionalComponents = array_filter($checks, function ($key) {
        return strpos($key, 'Tensor') !== false ||
            strpos($key, 'Python') !== false ||
            strpos($key, 'Directory') !== false;
    }, ARRAY_FILTER_USE_KEY);

    $missingOptional = array_filter($optionalComponents, fn($v) => $v === false);
    if (!empty($missingOptional)) {
        echo "💡 Optional improvements:\n";
        foreach ($missingOptional as $component => $status) {
            echo "   • $component (recommended but not required)\n";
        }
        echo "\n";
    }

    exit(0);
} else {
    echo "\n❌ $criticalFailures critical issue(s) found. Please fix them before proceeding.\n\n";

    // Show specific failures
    echo "Issues to fix:\n";
    foreach ($checks as $check => $status) {
        if ($status === false) {
            echo "   • $check\n";
        }
    }

    echo "\n📖 Refer to the troubleshooting section in Chapter 02.\n";
    echo "🔄 Run this script again after fixing the issues.\n\n";
    exit(1);
}
