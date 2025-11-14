<?php

declare(strict_types=1);

/**
 * Chapter 35: Cryptographic Algorithms - Hash Functions
 *
 * This example demonstrates cryptographic hash functions:
 * - SHA-256/SHA-512 hashing
 * - HMAC (Hash-based Message Authentication Code)
 * - File hashing and integrity verification
 * - Streaming hash for large files
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

/**
 * Secure hashing utilities using PHP's built-in cryptographic functions
 */
class SecureHash
{
    /**
     * Generate SHA-256 hash of data
     *
     * @param string $data Data to hash
     * @param bool $binary Return binary output instead of hex
     * @return string Hash value
     */
    public static function hash(string $data, bool $binary = false): string
    {
        return hash('sha256', $data, $binary);
    }

    /**
     * Generate SHA-512 hash for higher security
     *
     * @param string $data Data to hash
     * @param bool $binary Return binary output instead of hex
     * @return string Hash value
     */
    public static function hash512(string $data, bool $binary = false): string
    {
        return hash('sha512', $data, $binary);
    }

    /**
     * Generate BLAKE2b hash (modern, fast alternative)
     *
     * @param string $data Data to hash
     * @param bool $binary Return binary output instead of hex
     * @return string Hash value
     */
    public static function blake2b(string $data, bool $binary = false): string
    {
        return hash('blake2b512', $data, $binary);
    }

    /**
     * Generate HMAC (Hash-based Message Authentication Code)
     * Provides both authenticity and integrity
     *
     * @param string $data Data to authenticate
     * @param string $key Secret key
     * @param string $algo Hash algorithm (default: sha256)
     * @return string HMAC value
     */
    public static function hmac(string $data, string $key, string $algo = 'sha256'): string
    {
        return hash_hmac($algo, $data, $key);
    }

    /**
     * Verify HMAC using timing-safe comparison
     *
     * @param string $data Original data
     * @param string $key Secret key
     * @param string $expectedHmac Expected HMAC value
     * @param string $algo Hash algorithm
     * @return bool True if HMAC is valid
     */
    public static function verifyHmac(
        string $data,
        string $key,
        string $expectedHmac,
        string $algo = 'sha256'
    ): bool {
        $actualHmac = self::hmac($data, $key, $algo);
        return hash_equals($expectedHmac, $actualHmac);
    }

    /**
     * Hash a file using SHA-256
     *
     * @param string $filename Path to file
     * @return string File hash
     * @throws RuntimeException If file cannot be read
     */
    public static function hashFile(string $filename): string
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new RuntimeException("Cannot read file: $filename");
        }

        return hash_file('sha256', $filename);
    }

    /**
     * Hash large file using streaming to avoid memory issues
     *
     * @param string $filename Path to file
     * @param int $chunkSize Bytes to read per chunk
     * @return string File hash
     * @throws RuntimeException If file cannot be read
     */
    public static function hashLargeFile(string $filename, int $chunkSize = 8192): string
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new RuntimeException("Cannot read file: $filename");
        }

        $context = hash_init('sha256');
        $handle = fopen($filename, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Cannot open file: $filename");
        }

        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            if ($chunk !== false) {
                hash_update($context, $chunk);
            }
        }

        fclose($handle);
        return hash_final($context);
    }

    /**
     * Generate checksum for data with algorithm identifier
     *
     * @param string $data Data to checksum
     * @param string $algo Algorithm to use
     * @return string Checksum with algorithm prefix
     */
    public static function checksum(string $data, string $algo = 'sha256'): string
    {
        return $algo . ':' . hash($algo, $data);
    }

    /**
     * Verify checksum
     *
     * @param string $data Original data
     * @param string $checksum Checksum to verify
     * @return bool True if checksum is valid
     */
    public static function verifyChecksum(string $data, string $checksum): bool
    {
        $parts = explode(':', $checksum, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$algo, $expectedHash] = $parts;
        $actualHash = hash($algo, $data);

        return hash_equals($expectedHash, $actualHash);
    }
}

/**
 * Content integrity checker for files and data
 */
class IntegrityChecker
{
    private array $checksums = [];

    /**
     * Add a file to the integrity checker
     *
     * @param string $path File path
     * @throws RuntimeException If file cannot be read
     */
    public function addFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File not found: $path");
        }

        $this->checksums[$path] = SecureHash::hashFile($path);
    }

    /**
     * Add multiple files from a directory
     *
     * @param string $directory Directory path
     * @param string $pattern File pattern (default: *)
     */
    public function addDirectory(string $directory, string $pattern = '*'): void
    {
        $files = glob($directory . '/' . $pattern);

        foreach ($files as $file) {
            if (is_file($file)) {
                $this->addFile($file);
            }
        }
    }

    /**
     * Verify a file's integrity
     *
     * @param string $path File path
     * @return bool True if file is unmodified
     * @throws RuntimeException If file has no stored checksum
     */
    public function verify(string $path): bool
    {
        if (!isset($this->checksums[$path])) {
            throw new RuntimeException("No checksum for: $path");
        }

        if (!file_exists($path)) {
            return false;
        }

        $currentHash = SecureHash::hashFile($path);
        return hash_equals($this->checksums[$path], $currentHash);
    }

    /**
     * Verify all tracked files
     *
     * @return array Results array with path => bool
     */
    public function verifyAll(): array
    {
        $results = [];

        foreach ($this->checksums as $path => $expectedHash) {
            try {
                $results[$path] = $this->verify($path);
            } catch (RuntimeException $e) {
                $results[$path] = false;
            }
        }

        return $results;
    }

    /**
     * Export checksums as JSON
     *
     * @return string JSON string
     */
    public function export(): string
    {
        return json_encode($this->checksums, JSON_PRETTY_PRINT);
    }

    /**
     * Import checksums from JSON
     *
     * @param string $json JSON string
     * @throws InvalidArgumentException If JSON is invalid
     */
    public function import(string $json): void
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new InvalidArgumentException("Invalid JSON data");
        }

        $this->checksums = $data;
    }

    /**
     * Get all stored checksums
     *
     * @return array Checksums array
     */
    public function getChecksums(): array
    {
        return $this->checksums;
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Cryptographic Hash Functions Demo ===\n\n";

// Example 1: Basic hashing
echo "1. Basic Hashing:\n";
$data = "Hello, Cryptography!";

echo "Data: \"$data\"\n";
echo "SHA-256: " . SecureHash::hash($data) . "\n";
echo "SHA-512: " . SecureHash::hash512($data) . "\n";
echo "BLAKE2b: " . SecureHash::blake2b($data) . "\n";

// Demonstrate avalanche effect
$data2 = "Hello, Cryptography?"; // Changed last character
echo "\nAvalanche Effect (one character change):\n";
echo "Original: " . SecureHash::hash($data) . "\n";
echo "Modified: " . SecureHash::hash($data2) . "\n";

// Example 2: HMAC for message authentication
echo "\n2. HMAC (Message Authentication):\n";
$message = "Transfer $1000 to account 12345";
$secretKey = "supersecretkey123";

$hmac = SecureHash::hmac($message, $secretKey);
echo "Message: \"$message\"\n";
echo "HMAC: $hmac\n";

// Verify HMAC
$isValid = SecureHash::verifyHmac($message, $secretKey, $hmac);
echo "Verification: " . ($isValid ? "✓ Valid" : "✗ Invalid") . "\n";

// Tampered message
$tamperedMessage = "Transfer $9999 to account 12345";
$isTamperedValid = SecureHash::verifyHmac($tamperedMessage, $secretKey, $hmac);
echo "Tampered message verification: " .
     ($isTamperedValid ? "✓ Valid" : "✗ Invalid (as expected)") . "\n";

// Example 3: Checksums with algorithm identifier
echo "\n3. Checksums with Algorithm Identifier:\n";
$data = "Important data that needs verification";
$checksum = SecureHash::checksum($data);

echo "Data: \"$data\"\n";
echo "Checksum: $checksum\n";

$verified = SecureHash::verifyChecksum($data, $checksum);
echo "Verification: " . ($verified ? "✓ Valid" : "✗ Invalid") . "\n";

// Example 4: File hashing
echo "\n4. File Hashing:\n";

// Create temporary test files
$testFile1 = sys_get_temp_dir() . '/test_file1.txt';
$testFile2 = sys_get_temp_dir() . '/test_file2.txt';

file_put_contents($testFile1, "This is a test file for hashing.");
file_put_contents($testFile2, "This is a test file for hashing.");

echo "File 1 hash: " . SecureHash::hashFile($testFile1) . "\n";
echo "File 2 hash: " . SecureHash::hashFile($testFile2) . "\n";
echo "Hashes match: " .
     (SecureHash::hashFile($testFile1) === SecureHash::hashFile($testFile2) ? "Yes" : "No") . "\n";

// Modify file 2
file_put_contents($testFile2, "This is a modified test file.");
echo "\nAfter modification:\n";
echo "File 2 hash: " . SecureHash::hashFile($testFile2) . "\n";
echo "Hashes match: " .
     (SecureHash::hashFile($testFile1) === SecureHash::hashFile($testFile2) ? "Yes" : "No") . "\n";

// Example 5: Integrity checker
echo "\n5. File Integrity Checker:\n";

$checker = new IntegrityChecker();

// Add files to checker
$checker->addFile($testFile1);
$checker->addFile($testFile2);

echo "Added 2 files to integrity checker\n";
echo "Initial verification:\n";

$results = $checker->verifyAll();
foreach ($results as $file => $valid) {
    $basename = basename($file);
    echo "   $basename: " . ($valid ? "✓ OK" : "✗ MODIFIED") . "\n";
}

// Modify a file
file_put_contents($testFile1, "File has been tampered with!");

echo "\nAfter tampering with file 1:\n";
$results = $checker->verifyAll();
foreach ($results as $file => $valid) {
    $basename = basename($file);
    echo "   $basename: " . ($valid ? "✓ OK" : "✗ MODIFIED") . "\n";
}

// Export/Import checksums
echo "\n6. Export/Import Checksums:\n";
$exported = $checker->export();
echo "Exported checksums:\n$exported\n";

// Create new checker and import
$checker2 = new IntegrityChecker();
$checker2->import($exported);

echo "\nVerification after import:\n";
foreach ($checker2->getChecksums() as $path => $hash) {
    $basename = basename($path);
    echo "   $basename: " . substr($hash, 0, 16) . "...\n";
}

// Example 7: Performance comparison
echo "\n7. Hash Algorithm Performance Comparison:\n";

$testData = str_repeat("Performance test data! ", 1000);
$iterations = 1000;

$algorithms = ['md5', 'sha1', 'sha256', 'sha512', 'blake2b512'];

foreach ($algorithms as $algo) {
    $start = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        hash($algo, $testData);
    }

    $elapsed = microtime(true) - $start;
    $avgTime = ($elapsed / $iterations) * 1000;

    $security = match($algo) {
        'md5' => '❌ Broken',
        'sha1' => '❌ Weak',
        'sha256' => '✓ Strong',
        'sha512' => '✓ Strong',
        'blake2b512' => '✓ Strong',
        default => 'Unknown'
    };

    printf("   %-12s: %6.3f ms/hash - %s\n", $algo, $avgTime, $security);
}

// Example 8: Real-world use case - API signature
echo "\n8. Real-World Example - API Request Signing:\n";

class ApiRequest
{
    public function __construct(
        private string $method,
        private string $endpoint,
        private array $params,
        private string $secretKey
    ) {}

    public function sign(): string
    {
        $timestamp = time();

        // Create canonical request string
        ksort($this->params);
        $paramString = http_build_query($this->params);
        $canonical = implode("\n", [
            $this->method,
            $this->endpoint,
            $paramString,
            (string) $timestamp
        ]);

        // Generate signature
        $signature = SecureHash::hmac($canonical, $this->secretKey);

        return base64_encode($signature . ':' . $timestamp);
    }

    public function verify(string $signature): bool
    {
        $decoded = base64_decode($signature);
        [$providedSig, $timestamp] = explode(':', $decoded);

        // Check timestamp (5 minute window)
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        // Recreate canonical request
        ksort($this->params);
        $paramString = http_build_query($this->params);
        $canonical = implode("\n", [
            $this->method,
            $this->endpoint,
            $paramString,
            $timestamp
        ]);

        $expectedSig = SecureHash::hmac($canonical, $this->secretKey);

        return hash_equals($expectedSig, $providedSig);
    }
}

$apiRequest = new ApiRequest(
    'POST',
    '/api/v1/users',
    ['name' => 'John Doe', 'email' => 'john@example.com'],
    'api-secret-key-12345'
);

$signature = $apiRequest->sign();
echo "API Request Signature: $signature\n";

$isValid = $apiRequest->verify($signature);
echo "Signature verification: " . ($isValid ? "✓ Valid" : "✗ Invalid") . "\n";

// Cleanup
unlink($testFile1);
unlink($testFile2);

echo "\n=== Demo Complete ===\n";
