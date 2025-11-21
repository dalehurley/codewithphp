<?php

declare(strict_types=1);

/**
 * Chapter 35: Cryptographic Algorithms - Symmetric Encryption
 *
 * This example demonstrates symmetric encryption:
 * - AES-256-GCM encryption (OpenSSL)
 * - Libsodium encryption (XChaCha20-Poly1305)
 * - File encryption/decryption
 * - Key derivation from passwords
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

/**
 * Symmetric encryption using OpenSSL (AES-256-GCM)
 */
class SymmetricEncryption
{
    private const CIPHER = 'aes-256-gcm';

    /**
     * Generate a random encryption key
     *
     * @return string 256-bit key
     */
    public static function generateKey(): string
    {
        return random_bytes(32); // 256 bits
    }

    /**
     * Encrypt data using AES-256-GCM
     *
     * @param string $plaintext Data to encrypt
     * @param string $key Encryption key (32 bytes)
     * @return array Encrypted data with IV and tag
     * @throws InvalidArgumentException If key length is invalid
     * @throws RuntimeException If encryption fails
     */
    public static function encrypt(string $plaintext, string $key): array
    {
        if (strlen($key) !== 32) {
            throw new InvalidArgumentException("Key must be 32 bytes (256 bits)");
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);

        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',  // Additional authenticated data (AAD)
            16   // Tag length
        );

        if ($ciphertext === false) {
            throw new RuntimeException("Encryption failed: " . openssl_error_string());
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag)
        ];
    }

    /**
     * Decrypt data using AES-256-GCM
     *
     * @param array $encrypted Encrypted data array
     * @param string $key Decryption key (32 bytes)
     * @return string Decrypted plaintext
     * @throws InvalidArgumentException If key length is invalid
     * @throws RuntimeException If decryption fails
     */
    public static function decrypt(array $encrypted, string $key): string
    {
        if (strlen($key) !== 32) {
            throw new InvalidArgumentException("Key must be 32 bytes (256 bits)");
        }

        $ciphertext = base64_decode($encrypted['ciphertext']);
        $iv = base64_decode($encrypted['iv']);
        $tag = base64_decode($encrypted['tag']);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new RuntimeException("Decryption failed - invalid key or tampered data");
        }

        return $plaintext;
    }

    /**
     * Encrypt a file
     *
     * @param string $inputFile Input file path
     * @param string $outputFile Output file path
     * @param string $key Encryption key
     */
    public static function encryptFile(string $inputFile, string $outputFile, string $key): void
    {
        $plaintext = file_get_contents($inputFile);
        if ($plaintext === false) {
            throw new RuntimeException("Cannot read input file");
        }

        $encrypted = self::encrypt($plaintext, $key);
        file_put_contents($outputFile, json_encode($encrypted));
    }

    /**
     * Decrypt a file
     *
     * @param string $inputFile Input file path
     * @param string $outputFile Output file path
     * @param string $key Decryption key
     */
    public static function decryptFile(string $inputFile, string $outputFile, string $key): void
    {
        $encryptedJson = file_get_contents($inputFile);
        if ($encryptedJson === false) {
            throw new RuntimeException("Cannot read input file");
        }

        $encrypted = json_decode($encryptedJson, true);
        $plaintext = self::decrypt($encrypted, $key);
        file_put_contents($outputFile, $plaintext);
    }
}

/**
 * Symmetric encryption using Libsodium (recommended)
 */
class SodiumEncryption
{
    /**
     * Generate a random encryption key
     *
     * @return string Encryption key
     */
    public static function generateKey(): string
    {
        return sodium_crypto_secretbox_keygen();
    }

    /**
     * Encrypt data using XChaCha20-Poly1305
     *
     * @param string $plaintext Data to encrypt
     * @param string $key Encryption key
     * @return string Base64-encoded encrypted data
     */
    public static function encrypt(string $plaintext, string $key): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);

        // Return nonce + ciphertext as single base64 string
        return base64_encode($nonce . $ciphertext);
    }

    /**
     * Decrypt data
     *
     * @param string $encrypted Base64-encoded encrypted data
     * @param string $key Decryption key
     * @return string Decrypted plaintext
     * @throws RuntimeException If decryption fails
     */
    public static function decrypt(string $encrypted, string $key): string
    {
        $decoded = base64_decode($encrypted);

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($plaintext === false) {
            throw new RuntimeException("Decryption failed - invalid key or tampered data");
        }

        return $plaintext;
    }

    /**
     * Encrypt with additional authenticated data (AEAD)
     *
     * @param string $plaintext Data to encrypt
     * @param string $additionalData Authenticated but not encrypted data
     * @param string $key Encryption key
     * @return string Base64-encoded encrypted data
     */
    public static function encryptWithAAD(
        string $plaintext,
        string $additionalData,
        string $key
    ): string {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $additionalData,
            $nonce,
            $key
        );

        return base64_encode($nonce . $ciphertext);
    }

    /**
     * Decrypt data with AAD
     *
     * @param string $encrypted Base64-encoded encrypted data
     * @param string $additionalData Additional data
     * @param string $key Decryption key
     * @return string Decrypted plaintext
     * @throws RuntimeException If decryption fails
     */
    public static function decryptWithAAD(
        string $encrypted,
        string $additionalData,
        string $key
    ): string {
        $decoded = base64_decode($encrypted);

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            $additionalData,
            $nonce,
            $key
        );

        if ($plaintext === false) {
            throw new RuntimeException("Decryption failed");
        }

        return $plaintext;
    }
}

/**
 * Key derivation from passwords
 */
class KeyDerivation
{
    /**
     * Derive key from password using PBKDF2
     *
     * @param string $password Password
     * @param string $salt Salt
     * @param int $iterations Number of iterations
     * @param int $length Key length in bytes
     * @return string Derived key
     */
    public static function deriveKeyPBKDF2(
        string $password,
        string $salt,
        int $iterations = 100000,
        int $length = 32
    ): string {
        return hash_pbkdf2('sha256', $password, $salt, $iterations, $length, true);
    }

    /**
     * Derive key from password using Argon2id
     *
     * @param string $password Password
     * @param string $salt Salt (16 bytes)
     * @return string Derived key (32 bytes)
     */
    public static function deriveKeyArgon2(string $password, string $salt): string
    {
        if (strlen($salt) !== SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            throw new InvalidArgumentException(
                "Salt must be " . SODIUM_CRYPTO_PWHASH_SALTBYTES . " bytes"
            );
        }

        return sodium_crypto_pwhash(
            32,  // Key length
            $password,
            $salt,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );
    }

    /**
     * Generate a random salt
     *
     * @param int $length Salt length
     * @return string Random salt
     */
    public static function generateSalt(int $length = 16): string
    {
        return random_bytes($length);
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Symmetric Encryption Demo ===\n\n";

// Example 1: Basic AES-256-GCM encryption
echo "1. AES-256-GCM Encryption (OpenSSL):\n";
$key = SymmetricEncryption::generateKey();
$plaintext = "Sensitive data that needs encryption!";

echo "Original: \"$plaintext\"\n";
echo "Key (hex): " . bin2hex($key) . "\n\n";

$encrypted = SymmetricEncryption::encrypt($plaintext, $key);
echo "Encrypted components:\n";
echo "   Ciphertext: " . substr($encrypted['ciphertext'], 0, 40) . "...\n";
echo "   IV: " . $encrypted['iv'] . "\n";
echo "   Tag: " . $encrypted['tag'] . "\n";

$decrypted = SymmetricEncryption::decrypt($encrypted, $key);
echo "\nDecrypted: \"$decrypted\"\n";
echo "Match: " . ($plaintext === $decrypted ? "✓ Yes" : "✗ No") . "\n";

// Example 2: Tamper detection
echo "\n2. Tamper Detection:\n";
$tamperedData = $encrypted;
$tamperedData['ciphertext'] = base64_encode('tampered data here!!!');

try {
    $result = SymmetricEncryption::decrypt($tamperedData, $key);
    echo "✗ Tampered data was decrypted (should not happen)\n";
} catch (RuntimeException $e) {
    echo "✓ Tampering detected: {$e->getMessage()}\n";
}

// Example 3: Libsodium encryption
echo "\n3. Libsodium Encryption (XChaCha20-Poly1305):\n";
$sodiumKey = SodiumEncryption::generateKey();
$plaintext = "Secret message for Libsodium";

echo "Original: \"$plaintext\"\n";

$encrypted = SodiumEncryption::encrypt($plaintext, $sodiumKey);
echo "Encrypted: " . substr($encrypted, 0, 40) . "...\n";

$decrypted = SodiumEncryption::decrypt($encrypted, $sodiumKey);
echo "Decrypted: \"$decrypted\"\n";
echo "Match: " . ($plaintext === $decrypted ? "✓ Yes" : "✗ No") . "\n";

// Example 4: Encryption with Additional Authenticated Data (AAD)
echo "\n4. AEAD - Additional Authenticated Data:\n";
$message = "Account balance: $10,000";
$metadata = json_encode(['user_id' => 12345, 'timestamp' => time()]);

echo "Message: \"$message\"\n";
echo "Metadata: $metadata\n";

$encrypted = SodiumEncryption::encryptWithAAD($message, $metadata, $sodiumKey);
echo "Encrypted: " . substr($encrypted, 0, 40) . "...\n";

$decrypted = SodiumEncryption::decryptWithAAD($encrypted, $metadata, $sodiumKey);
echo "Decrypted: \"$decrypted\"\n";

// Try with wrong metadata
echo "\nTrying with modified metadata:\n";
$wrongMetadata = json_encode(['user_id' => 99999, 'timestamp' => time()]);
try {
    SodiumEncryption::decryptWithAAD($encrypted, $wrongMetadata, $sodiumKey);
    echo "✗ Decryption succeeded (should fail)\n";
} catch (RuntimeException $e) {
    echo "✓ Metadata tampering detected\n";
}

// Example 5: File encryption
echo "\n5. File Encryption:\n";
$testFile = sys_get_temp_dir() . '/test_encryption.txt';
$encryptedFile = sys_get_temp_dir() . '/test_encryption.enc';
$decryptedFile = sys_get_temp_dir() . '/test_decrypted.txt';

file_put_contents($testFile, "This is confidential file content.\nLine 2 of data.\nLine 3 of data.");

echo "Original file created: $testFile\n";
echo "Original size: " . filesize($testFile) . " bytes\n";

SymmetricEncryption::encryptFile($testFile, $encryptedFile, $key);
echo "Encrypted file: $encryptedFile\n";
echo "Encrypted size: " . filesize($encryptedFile) . " bytes\n";

SymmetricEncryption::decryptFile($encryptedFile, $decryptedFile, $key);
echo "Decrypted file: $decryptedFile\n";

$originalContent = file_get_contents($testFile);
$decryptedContent = file_get_contents($decryptedFile);
echo "Content match: " . ($originalContent === $decryptedContent ? "✓ Yes" : "✗ No") . "\n";

// Example 6: Key derivation from password
echo "\n6. Key Derivation from Password:\n";
$password = "UserPassword123!";
$salt = KeyDerivation::generateSalt(16);

echo "Password: $password\n";
echo "Salt (hex): " . bin2hex($salt) . "\n";

// PBKDF2
$start = microtime(true);
$key1 = KeyDerivation::deriveKeyPBKDF2($password, $salt);
$pbkdf2Time = microtime(true) - $start;

echo "\nPBKDF2 derived key (hex): " . bin2hex($key1) . "\n";
echo "Time: " . number_format($pbkdf2Time * 1000, 2) . " ms\n";

// Argon2id
$start = microtime(true);
$key2 = KeyDerivation::deriveKeyArgon2($password, $salt);
$argon2Time = microtime(true) - $start;

echo "\nArgon2id derived key (hex): " . bin2hex($key2) . "\n";
echo "Time: " . number_format($argon2Time * 1000, 2) . " ms\n";

// Use derived key for encryption
$encrypted = SodiumEncryption::encrypt("Data encrypted with derived key", $key2);
$decrypted = SodiumEncryption::decrypt($encrypted, $key2);
echo "\nEncryption with derived key: ✓ Success\n";

// Example 7: Performance comparison
echo "\n7. Performance Comparison:\n";
$testData = str_repeat("Performance test data! ", 100);
$iterations = 1000;

echo "Encrypting " . strlen($testData) . " bytes, $iterations times:\n";

// AES-256-GCM (OpenSSL)
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    SymmetricEncryption::encrypt($testData, $key);
}
$aesTime = microtime(true) - $start;

printf("   AES-256-GCM:  %6.2f ms/op\n", ($aesTime / $iterations) * 1000);

// XChaCha20-Poly1305 (Libsodium)
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    SodiumEncryption::encrypt($testData, $sodiumKey);
}
$sodiumTime = microtime(true) - $start;

printf("   XChaCha20:    %6.2f ms/op\n", ($sodiumTime / $iterations) * 1000);

$faster = $aesTime < $sodiumTime ? "AES-256-GCM" : "XChaCha20";
$ratio = max($aesTime, $sodiumTime) / min($aesTime, $sodiumTime);
echo "\n$faster was " . number_format($ratio, 2) . "x faster\n";

// Cleanup
unlink($testFile);
unlink($encryptedFile);
unlink($decryptedFile);

echo "\n=== Demo Complete ===\n";
