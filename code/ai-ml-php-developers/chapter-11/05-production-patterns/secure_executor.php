<?php

declare(strict_types=1);

/**
 * Secure Shell Executor for Production PHP-Python Integration
 * 
 * This class demonstrates security best practices for executing Python scripts
 * from PHP in production environments:
 * 
 * - Input validation and sanitization
 * - Command whitelisting
 * - Argument escaping
 * - Timeout enforcement
 * - Error handling and logging
 * - Resource limits
 * 
 * Use this as a template for production integrations.
 */

class SecureShellExecutor
{
    private string $logFile;

    public function __construct(
        private string $pythonPath,
        private string $scriptsDir,
        private array $allowedScripts = [],
        private int $timeoutSeconds = 30,
        private int $maxOutputSize = 10485760, // 10MB
        ?string $logFile = null
    ) {
        // Validate Python executable is accessible
        // If it's not a full path, assume it's in PATH
        if (strpos($this->pythonPath, '/') !== false) {
            if (!file_exists($this->pythonPath) || !is_executable($this->pythonPath)) {
                throw new RuntimeException("Python executable not found or not executable: {$this->pythonPath}");
            }
        } else {
            // Test if Python is in PATH by trying to execute it
            $testOutput = shell_exec("{$this->pythonPath} --version 2>&1");
            if (empty($testOutput)) {
                throw new RuntimeException("Python executable not found in PATH: {$this->pythonPath}");
            }
        }

        // Validate scripts directory
        if (!is_dir($this->scriptsDir)) {
            throw new RuntimeException("Scripts directory not found: {$this->scriptsDir}");
        }

        $this->scriptsDir = realpath($this->scriptsDir);
        $this->logFile = $logFile ?? sys_get_temp_dir() . '/php_python_executor.log';
    }

    /**
     * Execute a Python script with security checks.
     * 
     * @param string $scriptName Name of the script (not full path)
     * @param array $data Data to pass as JSON to the script
     * @return array Result from Python script
     * @throws SecurityException If security checks fail
     * @throws RuntimeException If execution fails
     */
    public function execute(string $scriptName, array $data = []): array
    {
        // Security Check 1: Validate script name
        $this->validateScriptName($scriptName);

        // Security Check 2: Verify script is in allowed list (if whitelist is configured)
        if (!empty($this->allowedScripts) && !in_array($scriptName, $this->allowedScripts, true)) {
            $this->log('SECURITY', "Attempted to execute non-whitelisted script: {$scriptName}");
            throw new SecurityException("Script not in whitelist: {$scriptName}");
        }

        // Security Check 3: Verify script exists in scripts directory
        $scriptPath = $this->scriptsDir . DIRECTORY_SEPARATOR . $scriptName;
        if (!file_exists($scriptPath)) {
            throw new RuntimeException("Script not found: {$scriptName}");
        }

        // Security Check 4: Ensure script is within scripts directory (prevent path traversal)
        if (strpos(realpath($scriptPath), $this->scriptsDir) !== 0) {
            $this->log('SECURITY', "Path traversal attempt detected: {$scriptName}");
            throw new SecurityException("Invalid script path");
        }

        // Security Check 5: Validate and sanitize data
        $this->validateData($data);

        // Encode data
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        // Security Check 6: Limit data size
        if (strlen($json) > 1048576) { // 1MB
            throw new RuntimeException("Input data too large (max 1MB)");
        }

        // Prepare command with proper escaping
        $escapedJson = escapeshellarg($json);
        $escapedScript = escapeshellarg($scriptPath);
        $escapedPython = escapeshellarg($this->pythonPath);

        $command = "{$escapedPython} {$escapedScript} {$escapedJson}";

        // Log execution attempt
        $this->log('INFO', "Executing: {$scriptName} with " . strlen($json) . " bytes of data");

        // Execute with timeout and capture output
        $startTime = microtime(true);

        try {
            $result = $this->executeWithTimeout($command);
            $duration = microtime(true) - $startTime;

            // Parse result
            $output = $this->parseOutput($result['stdout'], $scriptName);

            // Log success
            $this->log('INFO', "Completed: {$scriptName} in " . round($duration * 1000, 2) . "ms");

            return $output;
        } catch (Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->log('ERROR', "Failed: {$scriptName} after " . round($duration * 1000, 2) . "ms - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate script name for security.
     */
    private function validateScriptName(string $scriptName): void
    {
        // Check for path traversal attempts
        if (strpos($scriptName, '..') !== false) {
            throw new SecurityException("Path traversal detected in script name");
        }

        // Check for absolute paths
        if (strpos($scriptName, '/') === 0 || strpos($scriptName, '\\') === 0) {
            throw new SecurityException("Absolute paths not allowed in script name");
        }

        // Check for shell metacharacters
        $dangerousChars = ['&', '|', ';', '`', '$', '(', ')', '<', '>', '\n', '\r'];
        foreach ($dangerousChars as $char) {
            if (strpos($scriptName, $char) !== false) {
                throw new SecurityException("Invalid characters in script name");
            }
        }

        // Validate file extension
        if (!str_ends_with($scriptName, '.py')) {
            throw new SecurityException("Only .py files allowed");
        }
    }

    /**
     * Validate data before passing to Python.
     */
    private function validateData(array $data): void
    {
        // Check recursion depth (prevent deeply nested attacks)
        $this->checkArrayDepth($data, 0, 10);

        // Check for suspicious patterns in string values
        array_walk_recursive($data, function ($value, $key) {
            if (is_string($value)) {
                // Check for null bytes (potential for binary exploitation)
                if (strpos($value, "\0") !== false) {
                    throw new SecurityException("Null bytes not allowed in data");
                }

                // Check string length
                if (strlen($value) > 100000) { // 100KB per string
                    throw new SecurityException("Individual string too large (max 100KB)");
                }
            }
        });
    }

    /**
     * Check array depth to prevent deeply nested structures.
     */
    private function checkArrayDepth(array $array, int $depth, int $maxDepth): void
    {
        if ($depth > $maxDepth) {
            throw new SecurityException("Data structure too deeply nested (max {$maxDepth} levels)");
        }

        foreach ($array as $value) {
            if (is_array($value)) {
                $this->checkArrayDepth($value, $depth + 1, $maxDepth);
            }
        }
    }

    /**
     * Execute command with timeout using proc_open.
     */
    private function executeWithTimeout(string $command): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start process');
        }

        // Close stdin immediately
        fclose($pipes[0]);

        // Set non-blocking mode for reading
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $startTime = time();

        // Read output with timeout
        while (true) {
            $status = proc_get_status($process);

            // Check timeout
            if (time() - $startTime > $this->timeoutSeconds) {
                proc_terminate($process, 9); // SIGKILL
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                throw new RuntimeException("Script execution timed out after {$this->timeoutSeconds} seconds");
            }

            // Read available data
            $read = [$pipes[1], $pipes[2]];
            $write = null;
            $except = null;

            if (stream_select($read, $write, $except, 0, 200000)) { // 200ms timeout
                foreach ($read as $pipe) {
                    $data = fread($pipe, 8192);
                    if ($data !== false && $data !== '') {
                        if ($pipe === $pipes[1]) {
                            $stdout .= $data;
                        } else {
                            $stderr .= $data;
                        }

                        // Check output size limit
                        if (strlen($stdout) + strlen($stderr) > $this->maxOutputSize) {
                            proc_terminate($process, 9);
                            fclose($pipes[1]);
                            fclose($pipes[2]);
                            proc_close($process);
                            throw new RuntimeException("Output size limit exceeded");
                        }
                    }
                }
            }

            // Check if process has exited
            if (!$status['running']) {
                // Read any remaining data
                $stdout .= stream_get_contents($pipes[1]);
                $stderr .= stream_get_contents($pipes[2]);
                break;
            }

            usleep(100000); // Sleep 100ms
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException("Script exited with code {$exitCode}. Error: {$stderr}");
        }

        return [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'exit_code' => $exitCode
        ];
    }

    /**
     * Parse and validate Python output.
     */
    private function parseOutput(string $output, string $scriptName): array
    {
        $output = trim($output);

        if (empty($output)) {
            throw new RuntimeException("Empty output from script: {$scriptName}");
        }

        try {
            $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Invalid JSON from script: " . $e->getMessage() . "\nOutput: " . substr($output, 0, 200));
        }

        // Check for error in result
        if (isset($result['error'])) {
            throw new RuntimeException("Python error: {$result['error']}");
        }

        return $result;
    }

    /**
     * Log execution events.
     */
    private function log(string $level, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}\n";

        // In production, use proper logging (Monolog, etc.)
        error_log($logEntry, 3, $this->logFile);
    }

    /**
     * Get execution statistics from logs.
     */
    public function getStats(): array
    {
        if (!file_exists($this->logFile)) {
            return ['executions' => 0, 'errors' => 0, 'security_violations' => 0];
        }

        $logs = file_get_contents($this->logFile);

        return [
            'executions' => substr_count($logs, '[INFO] Executing:'),
            'completions' => substr_count($logs, '[INFO] Completed:'),
            'errors' => substr_count($logs, '[ERROR]'),
            'security_violations' => substr_count($logs, '[SECURITY]'),
            'log_file' => $this->logFile
        ];
    }
}

/**
 * Custom exception for security violations.
 */
class SecurityException extends RuntimeException {}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        echo "=== Secure Shell Executor Demo ===\n\n";

        // Initialize with security settings
        $scriptsDir = realpath(__DIR__ . '/../03-sentiment-analysis') ?: __DIR__ . '/../03-sentiment-analysis';

        $executor = new SecureShellExecutor(
            pythonPath: 'python3',  // Or result of `which python3`
            scriptsDir: $scriptsDir,
            allowedScripts: ['predict.py'],  // Whitelist only allowed scripts
            timeoutSeconds: 10,
            logFile: __DIR__ . '/secure_executor.log'
        );

        echo "Executor initialized with:\n";
        echo "  - Python: python3\n";
        echo "  - Scripts dir: {$scriptsDir}\n";
        echo "  - Whitelist: predict.py only\n";
        echo "  - Timeout: 10 seconds\n\n";

        // Test 1: Valid execution
        echo "Test 1: Valid execution\n";
        try {
            $result = $executor->execute('predict.py', [
                'text' => 'This is a secure and well-designed system!'
            ]);
            echo "✅ Success: Sentiment = {$result['sentiment']} ";
            echo "(" . round($result['confidence'] * 100, 1) . "% confident)\n\n";
        } catch (Exception $e) {
            echo "⚠️  Note: {$e->getMessage()}\n";
            echo "   (This is expected if model hasn't been trained yet)\n\n";
        }

        // Test 2: Invalid script name (security check)
        echo "Test 2: Path traversal attempt (should be blocked)\n";
        try {
            $executor->execute('../../../etc/passwd', ['text' => 'test']);
            echo "❌ Security check failed!\n\n";
        } catch (SecurityException $e) {
            echo "✅ Blocked: {$e->getMessage()}\n\n";
        }

        // Test 3: Script not in whitelist
        echo "Test 3: Non-whitelisted script (should be blocked)\n";
        try {
            $executor->execute('train_model.py', ['text' => 'test']);
            echo "❌ Whitelist check failed!\n\n";
        } catch (SecurityException $e) {
            echo "✅ Blocked: {$e->getMessage()}\n\n";
        }

        // Test 4: Invalid characters
        echo "Test 4: Shell injection attempt (should be blocked)\n";
        try {
            $executor->execute('predict.py; rm -rf /', ['text' => 'test']);
            echo "❌ Injection check failed!\n\n";
        } catch (SecurityException $e) {
            echo "✅ Blocked: {$e->getMessage()}\n\n";
        }

        // Show statistics
        echo "=== Execution Statistics ===\n";
        $stats = $executor->getStats();
        echo "Executions attempted: {$stats['executions']}\n";
        echo "Completions: {$stats['completions']}\n";
        echo "Errors: {$stats['errors']}\n";
        echo "Security violations: {$stats['security_violations']}\n";
        echo "Log file: {$stats['log_file']}\n\n";

        echo "✅ Security demonstration complete!\n\n";
        echo "Key Security Features:\n";
        echo "  ✓ Script whitelisting\n";
        echo "  ✓ Path traversal prevention\n";
        echo "  ✓ Shell injection protection\n";
        echo "  ✓ Input validation\n";
        echo "  ✓ Timeout enforcement\n";
        echo "  ✓ Output size limits\n";
        echo "  ✓ Comprehensive logging\n";
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        exit(1);
    }
}
