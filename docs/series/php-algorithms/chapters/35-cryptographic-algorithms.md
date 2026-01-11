---
title: "35: Cryptographic Algorithms"
description: "Essential cryptographic algorithms for secure PHP applications including hashing, encryption, digital signatures, and key management"
series: "php-algorithms"
chapter: 35
order: 35
difficulty: "Advanced"
prerequisites: []
estimatedTime: "PT55M"
teaches:
  - 'Master cryptographic hash functions and their applications (SHA-256, HMAC)'
  - 'Implement secure password hashing with modern algorithms (Argon2, bcrypt)'
  - 'Understand encryption fundamentals and when to use symmetric vs asymmetric encryption'
  - 'Apply digital signatures and message authentication for data integrity'
  - 'Generate cryptographically secure random numbers and tokens'
---
![Cryptographic Algorithms](/images/php-algorithms/chapter-35-cryptographic-algorithms-hero-full.webp)


<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/php-algorithms/">PHP Algorithms</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 35</span>
</div>

# 35: Cryptographic Algorithms <span class="difficulty-badge difficulty-advanced">Advanced</span>

## Overview

Cryptographic algorithms are the foundation of digital security, protecting data confidentiality, integrity, and authenticity in modern applications. This chapter explores the essential cryptographic techniques every PHP developer needs to understand and implement correctly. From password hashing to encryption, digital signatures to secure random number generation, you'll learn how to use PHP's battle-tested cryptographic functions to build secure applications.

While cryptography can seem intimidating, PHP provides excellent built-in support through functions like `password_hash()`, `hash_hmac()`, and the `libsodium` extension. This chapter focuses on understanding what these algorithms do, when to use them, and most importantly - how to use them correctly. You'll discover common cryptographic mistakes that lead to security breaches and learn the best practices that protect against them.

By the end of this chapter, you'll have implemented secure password hashing, symmetric encryption, digital signatures, and several real-world applications including encrypted session storage, API request signing, and two-factor authentication. These implementations will follow industry best practices and use PHP's proven cryptographic libraries.

**IMPORTANT**: This chapter is educational. For production systems, always use well-tested cryptographic libraries like `libsodium` or PHP's built-in functions. Never roll your own crypto!

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- Basic understanding of security concepts (confidentiality, integrity, authentication)
- Familiarity with hash functions and their properties
- Understanding of common web security vulnerabilities
- Knowledge of PHP's built-in security functions

**Estimated Time**: ~55 minutes

**Verify your setup:**

```bash
php --version
```

## What You'll Build

By the end of this chapter, you will have created:

- Secure hash function implementations using SHA-256 and HMAC
- Modern password hashing system with Argon2id and bcrypt
- Symmetric encryption system using AES-256-GCM and Libsodium
- Digital signature implementation for message authentication
- Cryptographically secure random number generator
- Key derivation functions using PBKDF2 and Argon2
- Encrypted session storage system
- API request signing and verification system
- Two-factor authentication (TOTP) implementation
- File integrity verification system
- JSON Web Token (JWT) implementation for API authentication

## Objectives

- Master cryptographic hash functions and their applications (SHA-256, HMAC)
- Implement secure password hashing with modern algorithms (Argon2, bcrypt)
- Understand encryption fundamentals and when to use symmetric vs asymmetric encryption
- Apply digital signatures and message authentication for data integrity
- Generate cryptographically secure random numbers and tokens
- Derive encryption keys from passwords securely
- Avoid common cryptographic mistakes that lead to security breaches
- Implement real-world cryptographic applications for production use

## Quick Start

Here's a quick example showing secure password hashing and verification:

```php
# filename: quick-start.php
<?php

declare(strict_types=1);

// Hash a password
$password = 'MySecurePassword123!';
$hash = password_hash($password, PASSWORD_ARGON2ID);

echo "Password hash: $hash\n";

// Verify the password
$isValid = password_verify($password, $hash);
echo "Password valid: " . ($isValid ? 'yes' : 'no') . "\n";

// Generate a secure token
$token = bin2hex(random_bytes(32));
echo "Secure token: $token\n";
```

**Expected Output:**

```
Password hash: $argon2id$v=19$m=65536,t=4,p=3$...
Password valid: yes
Secure token: a1b2c3d4e5f6...
```

This demonstrates the two most common cryptographic operations: password hashing and secure random token generation. Both use PHP's built-in functions, which are secure and easy to use.

## Cryptographic Hash Functions

Hash functions convert arbitrary data into fixed-size digests. Cryptographic hashes are:
- **Deterministic**: Same input → same output
- **One-way**: Computationally infeasible to reverse
- **Collision-resistant**: Hard to find two inputs with same hash
- **Avalanche effect**: Small input change → drastically different hash

### Using PHP's Built-in Hash Functions

PHP provides excellent built-in hash functions through the `hash()` and `hash_hmac()` functions. These are cryptographically secure and suitable for most applications.

```php
# filename: SecureHash.php
<?php

declare(strict_types=1);

class SecureHash {
    // Recommended: SHA-256 or better
    public static function hash(string $data): string {
        return hash('sha256', $data);
    }

    public static function hashBinary(string $data): string {
        return hash('sha256', $data, true);
    }

    // For passwords: Use password_hash() instead!
    public static function hashWithSalt(string $data, string $salt): string {
        return hash('sha256', $salt . $data);
    }

    // HMAC: Hash-based Message Authentication Code
    public static function hmac(string $data, string $key): string {
        return hash_hmac('sha256', $data, $key);
    }

    public static function verifyHmac(string $data, string $key, string $expectedHmac): bool {
        $actualHmac = self::hmac($data, $key);
        return hash_equals($expectedHmac, $actualHmac);  // Timing-safe comparison
    }

    // File hashing
    public static function hashFile(string $filename): string {
        return hash_file('sha256', $filename);
    }

    // Streaming hash for large files
    public static function hashLargeFile(string $filename): string {
        $context = hash_init('sha256');
        $handle = fopen($filename, 'r');

        while (!feof($handle)) {
            $chunk = fread($handle, 8192);
            hash_update($context, $chunk);
        }

        fclose($handle);
        return hash_final($context);
    }
}

// Usage
$data = 'Hello, World!';
$hash = SecureHash::hash($data);
echo "SHA-256: $hash\n";

$key = 'secret-key';
$hmac = SecureHash::hmac($data, $key);
echo "HMAC: $hmac\n";

$valid = SecureHash::verifyHmac($data, $key, $hmac);
echo "Valid: " . ($valid ? 'yes' : 'no') . "\n";
```

**Why It Works**: SHA-256 is a cryptographically secure hash function that produces a 256-bit (32-byte) digest. HMAC (Hash-based Message Authentication Code) combines the data with a secret key before hashing, ensuring that only someone with the key can generate a valid hash. The `hash_equals()` function performs timing-safe comparison to prevent timing attacks.

### Hash Algorithm Comparison

| Algorithm | Output Size | Security | Speed | Use Case |
|-----------|-------------|----------|-------|----------|
| MD5 | 128 bits | ❌ Broken | Fast | Legacy only |
| SHA-1 | 160 bits | ❌ Weak | Fast | Legacy only |
| SHA-256 | 256 bits | ✅ Strong | Medium | General purpose |
| SHA-512 | 512 bits | ✅ Strong | Medium | High security |
| SHA-3 | Variable | ✅ Strong | Slower | Modern apps |
| BLAKE2 | Variable | ✅ Strong | Very fast | High performance |

### Content Integrity Verification

File integrity verification ensures files haven't been tampered with. This is essential for security-critical applications, software distribution, and data backups.

```php
# filename: IntegrityChecker.php
<?php

declare(strict_types=1);

class IntegrityChecker {
    private array $checksums = [];

    public function addFile(string $path): void {
        if (!file_exists($path)) {
            throw new Exception("File not found: $path");
        }

        $this->checksums[$path] = SecureHash::hashFile($path);
    }

    public function verify(string $path): bool {
        if (!isset($this->checksums[$path])) {
            throw new Exception("No checksum for: $path");
        }

        $currentHash = SecureHash::hashFile($path);
        return hash_equals($this->checksums[$path], $currentHash);
    }

    public function verifyAll(): array {
        $results = [];

        foreach ($this->checksums as $path => $expectedHash) {
            $results[$path] = $this->verify($path);
        }

        return $results;
    }

    public function export(): string {
        return json_encode($this->checksums, JSON_PRETTY_PRINT);
    }

    public function import(string $json): void {
        $this->checksums = json_decode($json, true);
    }
}

// Usage
$checker = new IntegrityChecker();

$checker->addFile('/path/to/file1.txt');
$checker->addFile('/path/to/file2.txt');

// Save checksums
file_put_contents('checksums.json', $checker->export());

// Later: verify files haven't changed
$checker->import(file_get_contents('checksums.json'));
$results = $checker->verifyAll();

foreach ($results as $file => $valid) {
    echo "$file: " . ($valid ? "OK" : "MODIFIED") . "\n";
}
```

**Why It Works**: File integrity checking computes a hash of each file and stores it. Later, re-computing the hash and comparing it detects any changes. This is essential for security-critical files, software distribution, and backups. The `hash_equals()` function prevents timing attacks when comparing hashes.

## Password Hashing

**NEVER use simple hashes for passwords!** Use password-specific algorithms designed to be slow.

Password hashing requires special algorithms that are intentionally slow to resist brute-force attacks. PHP's `password_hash()` function handles all the complexity for you, using Argon2id (best) or bcrypt (good) by default.

### Modern Password Hashing

```php
# filename: PasswordManager.php
<?php

declare(strict_types=1);

class PasswordManager {
    // Use Argon2id (best) or bcrypt (good)
    const ALGORITHM = PASSWORD_ARGON2ID;

    public static function hash(string $password): string {
        return password_hash($password, self::ALGORITHM, [
            'memory_cost' => 65536,  // 64 MB
            'time_cost' => 4,        // 4 iterations
            'threads' => 2           // 2 parallel threads
        ]);
    }

    public static function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool {
        return password_needs_rehash($hash, self::ALGORITHM, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 2
        ]);
    }

    public static function strength(string $password): array {
        $score = 0;
        $feedback = [];

        // Length check
        if (strlen($password) >= 12) {
            $score += 2;
        } elseif (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = "Password should be at least 8 characters";
        }

        // Complexity checks
        if (preg_match('/[a-z]/', $password)) $score++;
        else $feedback[] = "Add lowercase letters";

        if (preg_match('/[A-Z]/', $password)) $score++;
        else $feedback[] = "Add uppercase letters";

        if (preg_match('/[0-9]/', $password)) $score++;
        else $feedback[] = "Add numbers";

        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
        else $feedback[] = "Add special characters";

        // Common password check (simplified)
        $common = ['password', '123456', 'qwerty', 'admin'];
        if (in_array(strtolower($password), $common)) {
            $score = 0;
            $feedback[] = "This is a commonly used password";
        }

        return [
            'score' => $score,
            'strength' => self::getStrengthLabel($score),
            'feedback' => $feedback
        ];
    }

    private static function getStrengthLabel(int $score): string {
        return match(true) {
            $score >= 6 => 'strong',
            $score >= 4 => 'medium',
            default => 'weak'
        };
    }
}

// Usage
$password = 'MySecureP@ssw0rd!';

// Hash password
$hash = PasswordManager::hash($password);
echo "Hash: $hash\n";

// Verify password
$valid = PasswordManager::verify($password, $hash);
echo "Valid: " . ($valid ? 'yes' : 'no') . "\n";

// Check if rehash needed (after upgrading security params)
if (PasswordManager::needsRehash($hash)) {
    $newHash = PasswordManager::hash($password);
    // Update database with new hash
}

// Check password strength
$strength = PasswordManager::strength($password);
print_r($strength);
```

**Why It Works**: Argon2id is the winner of the Password Hashing Competition and is the recommended algorithm for password hashing. It's memory-hard (resistant to GPU attacks) and time-hard (slow by design). The `password_hash()` function automatically generates a unique salt for each password, and `password_verify()` handles all the complexity of verification. The `password_needs_rehash()` function allows upgrading security parameters over time without forcing users to reset passwords.

## Encryption and Decryption

Encryption protects data confidentiality by converting plaintext into ciphertext that can only be read with the correct key. Symmetric encryption uses the same key for encryption and decryption, making it fast and efficient for most use cases.

### Symmetric Encryption (Same key for encrypt/decrypt)

AES-256-GCM (Galois/Counter Mode) provides authenticated encryption, meaning it both encrypts data and verifies its integrity. This prevents tampering attacks.

```php
# filename: SymmetricEncryption.php
<?php

declare(strict_types=1);

class SymmetricEncryption {
    const CIPHER = 'aes-256-gcm';

    public static function generateKey(): string {
        return random_bytes(32);  // 256 bits
    }

    public static function encrypt(string $plaintext, string $key): array {
        if (strlen($key) !== 32) {
            throw new Exception("Key must be 32 bytes (256 bits)");
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
            throw new Exception("Encryption failed");
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag)
        ];
    }

    public static function decrypt(array $encrypted, string $key): string {
        if (strlen($key) !== 32) {
            throw new Exception("Key must be 32 bytes (256 bits)");
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
            throw new Exception("Decryption failed - invalid key or tampered data");
        }

        return $plaintext;
    }

    public static function encryptFile(string $inputFile, string $outputFile, string $key): void {
        $plaintext = file_get_contents($inputFile);
        $encrypted = self::encrypt($plaintext, $key);
        file_put_contents($outputFile, json_encode($encrypted));
    }

    public static function decryptFile(string $inputFile, string $outputFile, string $key): void {
        $encrypted = json_decode(file_get_contents($inputFile), true);
        $plaintext = self::decrypt($encrypted, $key);
        file_put_contents($outputFile, $plaintext);
    }
}

// Usage
$key = SymmetricEncryption::generateKey();
$plaintext = 'Secret message!';

// Encrypt
$encrypted = SymmetricEncryption::encrypt($plaintext, $key);
echo "Encrypted: " . $encrypted['ciphertext'] . "\n";

// Decrypt
$decrypted = SymmetricEncryption::decrypt($encrypted, $key);
echo "Decrypted: $decrypted\n";

// File encryption
SymmetricEncryption::encryptFile('secret.txt', 'secret.enc', $key);
SymmetricEncryption::decryptFile('secret.enc', 'decrypted.txt', $key);
```

**Why It Works**: AES-256-GCM provides authenticated encryption - it both encrypts data and verifies its integrity. The initialization vector (IV) ensures the same plaintext produces different ciphertext each time, and the authentication tag detects tampering. The IV and tag must be stored with the ciphertext but don't need to be secret.

### Using Libsodium (Recommended)

Libsodium is a modern, easy-to-use cryptographic library that's harder to misuse than OpenSSL. It's the recommended choice for new applications.

```php
# filename: SodiumEncryption.php
<?php

declare(strict_types=1);

class SodiumEncryption {
    public static function generateKey(): string {
        return sodium_crypto_secretbox_keygen();
    }

    public static function encrypt(string $plaintext, string $key): string {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);

        // Return nonce + ciphertext
        return base64_encode($nonce . $ciphertext);
    }

    public static function decrypt(string $encrypted, string $key): string {
        $decoded = base64_decode($encrypted);

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

        if ($plaintext === false) {
            throw new Exception("Decryption failed");
        }

        return $plaintext;
    }

    // Authenticated encryption with associated data (AEAD)
    public static function encryptWithMetadata(string $plaintext, string $metadata, string $key): string {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $metadata,  // Authenticated but not encrypted
            $nonce,
            $key
        );

        return base64_encode($nonce . $ciphertext);
    }

    public static function decryptWithMetadata(string $encrypted, string $metadata, string $key): string {
        $decoded = base64_decode($encrypted);

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            $metadata,
            $nonce,
            $key
        );

        if ($plaintext === false) {
            throw new Exception("Decryption failed");
        }

        return $plaintext;
    }
}

// Usage
$key = SodiumEncryption::generateKey();
$plaintext = 'Classified information';

$encrypted = SodiumEncryption::encrypt($plaintext, $key);
echo "Encrypted: $encrypted\n";

$decrypted = SodiumEncryption::decrypt($encrypted, $key);
echo "Decrypted: $decrypted\n";

// With metadata (e.g., user ID, timestamp)
$metadata = json_encode(['user_id' => 123, 'timestamp' => time()]);
$encryptedWithMeta = SodiumEncryption::encryptWithMetadata($plaintext, $metadata, $key);
$decryptedWithMeta = SodiumEncryption::decryptWithMetadata($encryptedWithMeta, $metadata, $key);
```

**Why It Works**: Libsodium's secretbox provides authenticated encryption with a simpler API than OpenSSL. The AEAD (Authenticated Encryption with Associated Data) mode allows authenticating metadata (like user ID or timestamp) without encrypting it, which is useful when metadata needs to be readable but tamper-proof.

### Asymmetric Encryption (Public Key Cryptography)

Asymmetric encryption uses a pair of keys: a public key for encryption and a private key for decryption. Unlike symmetric encryption, you can share the public key freely - only the holder of the private key can decrypt messages. This solves the key distribution problem but is slower than symmetric encryption.

**When to Use**:
- **Key Exchange**: Encrypt a symmetric key to send securely
- **Small Data**: Encrypting small amounts of data (tokens, keys, passwords)
- **Digital Signatures**: Already covered in Digital Signatures section
- **Hybrid Encryption**: Combine both - use asymmetric to encrypt symmetric key, then use symmetric for data

**Important**: RSA has size limitations (can only encrypt data smaller than the key size). For larger data, use hybrid encryption.

```php
# filename: AsymmetricEncryption.php
<?php

declare(strict_types=1);

class AsymmetricEncryption {
    // Generate RSA key pair
    public static function generateKeyPair(int $keySize = 2048): array {
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => $keySize,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            throw new Exception("Failed to generate key pair");
        }

        // Export private key
        openssl_pkey_export($resource, $privateKey);

        // Export public key
        $publicKeyDetails = openssl_pkey_get_details($resource);
        $publicKey = $publicKeyDetails["key"];

        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }

    // Encrypt with public key
    public static function encrypt(string $plaintext, string $publicKey): string {
        $encrypted = '';
        $success = openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);

        if (!$success) {
            throw new Exception("Encryption failed");
        }

        return base64_encode($encrypted);
    }

    // Decrypt with private key
    public static function decrypt(string $encrypted, string $privateKey): string {
        $decoded = base64_decode($encrypted);
        $decrypted = '';
        $success = openssl_private_decrypt($decoded, $decrypted, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);

        if (!$success) {
            throw new Exception("Decryption failed");
        }

        return $decrypted;
    }

    // Hybrid encryption: Use asymmetric to encrypt symmetric key, then symmetric for data
    public static function hybridEncrypt(string $plaintext, string $publicKey): array {
        // Generate symmetric key for this message
        $symmetricKey = random_bytes(32);

        // Encrypt data with symmetric key
        $encryptedData = SymmetricEncryption::encrypt($plaintext, $symmetricKey);

        // Encrypt symmetric key with public key
        $encryptedKey = self::encrypt(base64_encode($symmetricKey), $publicKey);

        return [
            'data' => $encryptedData,
            'key' => $encryptedKey
        ];
    }

    public static function hybridDecrypt(array $encrypted, string $privateKey): string {
        // Decrypt symmetric key
        $symmetricKey = base64_decode(self::decrypt($encrypted['key'], $privateKey));

        // Decrypt data with symmetric key
        return SymmetricEncryption::decrypt($encrypted['data'], $symmetricKey);
    }
}

// Usage
$keyPair = AsymmetricEncryption::generateKeyPair();

// Small data encryption (e.g., encrypting a password or token)
$message = 'Secret token: abc123';
$encrypted = AsymmetricEncryption::encrypt($message, $keyPair['public']);
echo "Encrypted: $encrypted\n";

$decrypted = AsymmetricEncryption::decrypt($encrypted, $keyPair['private']);
echo "Decrypted: $decrypted\n";

// Hybrid encryption for larger data
$largeData = str_repeat('This is a large message. ', 100);
$hybridEncrypted = AsymmetricEncryption::hybridEncrypt($largeData, $keyPair['public']);
$hybridDecrypted = AsymmetricEncryption::hybridDecrypt($hybridEncrypted, $keyPair['private']);
echo "Hybrid decrypted matches: " . ($largeData === $hybridDecrypted ? 'yes' : 'no') . "\n";
```

**Why It Works**: Asymmetric encryption solves the key distribution problem - you can share the public key freely without compromising security. RSA uses the mathematical difficulty of factoring large numbers. However, RSA is slow and has size limitations (can only encrypt data smaller than the key size minus padding overhead). Hybrid encryption combines the best of both: asymmetric encryption for key exchange, symmetric encryption for data (fast and unlimited size).

**Key Size Recommendations**:
- RSA 2048 bits: Minimum for new applications
- RSA 3072 bits: Recommended for high security
- RSA 4096 bits: Maximum security (slower)

## Key Exchange Protocols

Key exchange protocols allow two parties to establish a shared secret over an insecure channel without pre-sharing keys. This is fundamental to secure communication protocols like TLS.

### Diffie-Hellman Key Exchange

Diffie-Hellman allows two parties to establish a shared secret even if their communication is intercepted. The security comes from the difficulty of the discrete logarithm problem.

```php
# filename: KeyExchange.php
<?php

declare(strict_types=1);

class KeyExchange {
    // Generate Diffie-Hellman parameters
    public static function generateDHParams(int $keySize = 2048): array {
        $config = [
            "private_key_bits" => $keySize,
            "private_key_type" => OPENSSL_KEYTYPE_DH,
        ];

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            throw new Exception("Failed to generate DH parameters");
        }

        $details = openssl_pkey_get_details($resource);
        return [
            'p' => $details['dh']['p'],  // Prime modulus
            'g' => $details['dh']['g'],  // Generator
        ];
    }

    // Generate private/public key pair for DH
    public static function generateDHKeyPair(string $p, string $g): array {
        $config = [
            "p" => $p,
            "g" => $g,
            "private_key_type" => OPENSSL_KEYTYPE_DH,
        ];

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            throw new Exception("Failed to generate DH key pair");
        }

        openssl_pkey_export($resource, $privateKey);
        $details = openssl_pkey_get_details($resource);
        $publicKey = $details['dh']['pub_key'];

        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }

    // Compute shared secret
    public static function computeSharedSecret(string $privateKey, string $peerPublicKey, string $p, string $g): string {
        $config = [
            "p" => $p,
            "g" => $g,
            "pub_key" => $peerPublicKey,
            "priv_key" => $privateKey,
        ];

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            throw new Exception("Failed to compute shared secret");
        }

        $details = openssl_pkey_get_details($resource);
        return bin2hex($details['dh']['shared_secret']);
    }
}

// Usage: Alice and Bob establish shared secret
$params = KeyExchange::generateDHParams();

// Alice generates her key pair
$aliceKeys = KeyExchange::generateDHKeyPair($params['p'], $params['g']);

// Bob generates his key pair
$bobKeys = KeyExchange::generateDHKeyPair($params['p'], $params['g']);

// Alice computes shared secret using Bob's public key
$aliceSecret = KeyExchange::computeSharedSecret(
    $aliceKeys['private'],
    $bobKeys['public'],
    $params['p'],
    $params['g']
);

// Bob computes shared secret using Alice's public key
$bobSecret = KeyExchange::computeSharedSecret(
    $bobKeys['private'],
    $aliceKeys['public'],
    $params['p'],
    $params['g']
);

// Both should have the same shared secret
echo "Shared secrets match: " . ($aliceSecret === $bobSecret ? 'yes' : 'no') . "\n";

// Now use shared secret for symmetric encryption
$sharedKey = hex2bin($aliceSecret);
$encrypted = SymmetricEncryption::encrypt('Secret message', $sharedKey);
```

**Why It Works**: Diffie-Hellman allows two parties to establish a shared secret without pre-sharing keys. Both parties generate public/private key pairs, exchange public keys, and compute the shared secret. Even if an attacker intercepts the public keys, they cannot compute the shared secret without the private keys. The security relies on the discrete logarithm problem being computationally hard.

### Elliptic Curve Diffie-Hellman (ECDH)

ECDH provides the same functionality as DH but uses elliptic curve cryptography, which offers equivalent security with smaller key sizes and better performance.

```php
// Using Libsodium for ECDH
class ECDHKeyExchange {
    public static function generateKeyPair(): array {
        $keyPair = sodium_crypto_box_keypair();
        
        return [
            'public' => base64_encode(sodium_crypto_box_publickey($keyPair)),
            'private' => base64_encode(sodium_crypto_box_secretkey($keyPair))
        ];
    }

    public static function computeSharedSecret(string $privateKey, string $peerPublicKey): string {
        $privateKey = base64_decode($privateKey);
        $peerPublicKey = base64_decode($peerPublicKey);
        
        $sharedSecret = sodium_crypto_box_beforenm($peerPublicKey, $privateKey);
        return base64_encode($sharedSecret);
    }
}

// Usage
$alice = ECDHKeyExchange::generateKeyPair();
$bob = ECDHKeyExchange::generateKeyPair();

$aliceSecret = ECDHKeyExchange::computeSharedSecret($alice['private'], $bob['public']);
$bobSecret = ECDHKeyExchange::computeSharedSecret($bob['private'], $alice['public']);

echo "ECDH secrets match: " . ($aliceSecret === $bobSecret ? 'yes' : 'no') . "\n";
```

**Why It Works**: ECDH provides the same security as traditional DH but with much smaller key sizes (256-bit ECDH ≈ 3072-bit RSA). This makes it faster and more efficient, which is why it's preferred in modern protocols like TLS 1.3.

## Nonce Management

Nonces (number used once) are critical for cryptographic security. They ensure that encrypting the same plaintext multiple times produces different ciphertexts, preventing pattern analysis attacks.

### Nonce Reuse Prevention

**CRITICAL**: Never reuse a nonce/IV with the same key! Nonce reuse completely breaks security in most encryption modes.

```php
# filename: NonceManager.php
<?php

declare(strict_types=1);

class NonceManager {
    private array $usedNonces = [];
    private int $maxStored = 10000;  // Prevent memory issues

    // Generate cryptographically secure nonce
    public static function generate(int $length): string {
        return random_bytes($length);
    }

    // Check if nonce has been used (for stateful systems)
    public function isUsed(string $nonce): bool {
        return isset($this->usedNonces[$nonce]);
    }

    // Mark nonce as used
    public function markUsed(string $nonce): void {
        if (count($this->usedNonces) >= $this->maxStored) {
            // Remove oldest 10% to prevent unbounded growth
            $removeCount = (int)($this->maxStored * 0.1);
            $this->usedNonces = array_slice($this->usedNonces, $removeCount, null, true);
        }
        $this->usedNonces[$nonce] = true;
    }

    // Verify nonce hasn't been used and mark it
    public function verifyAndMark(string $nonce): bool {
        if ($this->isUsed($nonce)) {
            return false;  // Nonce reuse detected!
        }
        $this->markUsed($nonce);
        return true;
    }

    // Counter-based nonce (for stateful systems)
    private int $counter = 0;

    public function getCounterNonce(int $length): string {
        $counterBytes = pack('Q', $this->counter++);  // 64-bit counter
        $randomBytes = random_bytes($length - 8);
        return $counterBytes . $randomBytes;
    }
}

// Usage
$nonceManager = new NonceManager();

// Generate nonce for encryption
$nonce = NonceManager::generate(12);
echo "Nonce: " . bin2hex($nonce) . "\n";

// Verify nonce hasn't been used
if ($nonceManager->verifyAndMark($nonce)) {
    echo "Nonce is valid\n";
} else {
    echo "Nonce reuse detected!\n";
}

// Try to reuse - should fail
if ($nonceManager->verifyAndMark($nonce)) {
    echo "ERROR: Nonce reuse allowed!\n";
} else {
    echo "Nonce reuse correctly prevented\n";
}
```

**Why Nonce Reuse is Dangerous**:
- **AES-GCM**: Reusing a nonce with the same key allows attackers to recover the authentication key and forge messages
- **Stream Ciphers**: Nonce reuse can reveal plaintext through XOR operations
- **CBC Mode**: IV reuse allows pattern analysis attacks

**Best Practices**:
1. Always generate nonces using `random_bytes()` - never use predictable values
2. Use counter-based nonces only in stateful systems where you can guarantee uniqueness
3. Store recently used nonces to detect reuse (for stateful systems)
4. For stateless systems, use random nonces with sufficient length (12 bytes minimum)
5. Never use timestamps or predictable values as nonces

## Cryptographically Secure Random Numbers

Cryptographically secure random number generation is critical for security. Never use `rand()` or `mt_rand()` for security-sensitive operations - they're predictable. Always use `random_bytes()` or `random_int()`.

### Random Number Generation

```php
# filename: SecureRandom.php
<?php

declare(strict_types=1);

class SecureRandom {
    // Random bytes
    public static function bytes(int $length): string {
        return random_bytes($length);
    }

    // Random integer
    public static function int(int $min, int $max): int {
        return random_int($min, $max);
    }

    // Random string (alphanumeric)
    public static function string(int $length): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    // Random hex string
    public static function hex(int $length): string {
        return bin2hex(random_bytes($length));
    }

    // UUID v4
    public static function uuid(): string {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);  // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);  // Variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // Token (URL-safe)
    public static function token(int $length = 32): string {
        return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
    }

    // Shuffle array cryptographically
    public static function shuffle(array $array): array {
        $count = count($array);

        for ($i = $count - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
        }

        return $array;
    }
}

// Usage
echo "Random bytes (hex): " . bin2hex(SecureRandom::bytes(16)) . "\n";
echo "Random int: " . SecureRandom::int(1, 100) . "\n";
echo "Random string: " . SecureRandom::string(20) . "\n";
echo "Random hex: " . SecureRandom::hex(16) . "\n";
echo "UUID: " . SecureRandom::uuid() . "\n";
echo "Token: " . SecureRandom::token() . "\n";

$items = [1, 2, 3, 4, 5];
$shuffled = SecureRandom::shuffle($items);
print_r($shuffled);
```

**Why It Works**: `random_bytes()` and `random_int()` use cryptographically secure random number generators (CSPRNGs) that are unpredictable and suitable for security purposes. The Fisher-Yates shuffle algorithm ensures each permutation is equally likely when using a secure random number generator.

## Digital Signatures

Digital signatures provide authenticity and non-repudiation - proving that a message came from a specific sender and hasn't been modified. They use asymmetric cryptography (public/private key pairs).

### Using Libsodium for Signing

```php
# filename: DigitalSignature.php
<?php

declare(strict_types=1);

class DigitalSignature {
    public static function generateKeyPair(): array {
        $keyPair = sodium_crypto_sign_keypair();

        return [
            'public' => base64_encode(sodium_crypto_sign_publickey($keyPair)),
            'private' => base64_encode(sodium_crypto_sign_secretkey($keyPair))
        ];
    }

    public static function sign(string $message, string $privateKey): string {
        $privateKey = base64_decode($privateKey);
        $signature = sodium_crypto_sign_detached($message, $privateKey);

        return base64_encode($signature);
    }

    public static function verify(string $message, string $signature, string $publicKey): bool {
        $signature = base64_decode($signature);
        $publicKey = base64_decode($publicKey);

        return sodium_crypto_sign_verify_detached($signature, $message, $publicKey);
    }
}

// Usage
$keyPair = DigitalSignature::generateKeyPair();

$message = 'Important document';
$signature = DigitalSignature::sign($message, $keyPair['private']);

echo "Signature: $signature\n";

$valid = DigitalSignature::verify($message, $signature, $keyPair['public']);
echo "Valid: " . ($valid ? 'yes' : 'no') . "\n";

// Tampered message
$tamperedMessage = 'Important document (modified)';
$validTampered = DigitalSignature::verify($tamperedMessage, $signature, $keyPair['public']);
echo "Valid (tampered): " . ($validTampered ? 'yes' : 'no') . "\n";  // no
```

**Why It Works**: Digital signatures use asymmetric cryptography (public/private key pairs). The private key signs messages, and anyone with the public key can verify signatures. Changing even one character in the message invalidates the signature, providing strong integrity guarantees. Libsodium uses Ed25519, a modern, fast signature algorithm.

## Key Derivation

Key derivation functions convert passwords or other low-entropy secrets into strong encryption keys. They're designed to be slow and memory-intensive to resist brute-force attacks.

### Password-Based Key Derivation

```php
# filename: KeyDerivation.php
<?php

declare(strict_types=1);

class KeyDerivation {
    // Derive encryption key from password
    public static function deriveKey(string $password, string $salt, int $length = 32): string {
        // Using PBKDF2
        return hash_pbkdf2('sha256', $password, $salt, 100000, $length, true);
    }

    // Using Argon2 (better)
    public static function deriveKeyArgon2(string $password, string $salt): string {
        return sodium_crypto_pwhash(
            32,  // Key length
            $password,
            $salt,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );
    }

    // Generate deterministic salt from user info
    public static function generateSalt(string $identifier): string {
        // NEVER use this for passwords - use random_bytes()
        // Only for deterministic key derivation from known identifier
        return hash('sha256', $identifier, true);
    }
}

// Usage
$password = 'user-password';
$salt = random_bytes(16);

$key = KeyDerivation::deriveKey($password, $salt);
echo "Derived key: " . bin2hex($key) . "\n";

// Now use $key for encryption
$encrypted = SymmetricEncryption::encrypt('Secret data', $key);
```

**Why It Works**: Key derivation functions convert passwords (which have low entropy) into strong encryption keys. PBKDF2 uses many iterations of hashing to slow down brute-force attacks, while Argon2 is memory-hard, making it resistant to GPU and ASIC attacks. The salt ensures each password produces a different key even if two users have the same password.

## Real-World Applications

### 1. Encrypted Session Storage

Encrypting session data protects sensitive information even if session storage is compromised. This is especially important for shared hosting or when storing sensitive user data.

```php
# filename: EncryptedSession.php
<?php

declare(strict_types=1);

class EncryptedSession {
    private string $key;

    public function __construct(string $secret) {
        $this->key = hash('sha256', $secret, true);
    }

    public function set(string $name, $value): void {
        $serialized = serialize($value);
        $encrypted = SymmetricEncryption::encrypt($serialized, $this->key);
        $_SESSION[$name] = $encrypted;
    }

    public function get(string $name, $default = null) {
        if (!isset($_SESSION[$name])) {
            return $default;
        }

        try {
            $decrypted = SymmetricEncryption::decrypt($_SESSION[$name], $this->key);
            return unserialize($decrypted);
        } catch (Exception $e) {
            return $default;
        }
    }

    public function has(string $name): bool {
        return isset($_SESSION[$name]);
    }

    public function remove(string $name): void {
        unset($_SESSION[$name]);
    }
}

// Usage
session_start();

$session = new EncryptedSession('app-secret-key');

$session->set('user', [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

$user = $session->get('user');
print_r($user);
```

**Why It Works**: Encrypting session data protects sensitive information even if session storage is compromised. The key is derived from an application secret, ensuring only your application can decrypt the data. This is especially important for shared hosting environments or when storing sensitive user information.

### 2. API Request Signing

API request signing prevents tampering and replay attacks. Each request includes a signature that proves it came from an authorized client and hasn't been modified in transit.

```php
# filename: ApiRequestSigner.php
<?php

declare(strict_types=1);

class ApiRequestSigner {
    private string $secret;

    public function __construct(string $secret) {
        $this->secret = $secret;
    }

    public function signRequest(string $method, string $uri, array $params = []): array {
        $timestamp = time();
        $nonce = SecureRandom::hex(16);

        // Build canonical request
        ksort($params);
        $queryString = http_build_query($params);
        $canonical = "$method\n$uri\n$queryString\n$timestamp\n$nonce";

        // Generate signature
        $signature = hash_hmac('sha256', $canonical, $this->secret);

        return [
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'signature' => $signature
        ];
    }

    public function verifyRequest(
        string $method,
        string $uri,
        array $params,
        string $timestamp,
        string $nonce,
        string $signature,
        int $maxAge = 300  // 5 minutes
    ): bool {
        // Check timestamp
        if (abs(time() - $timestamp) > $maxAge) {
            return false;  // Request too old
        }

        // Rebuild canonical request
        ksort($params);
        $queryString = http_build_query($params);
        $canonical = "$method\n$uri\n$queryString\n$timestamp\n$nonce";

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $canonical, $this->secret);

        return hash_equals($expectedSignature, $signature);
    }
}

// Usage (Client)
$signer = new ApiRequestSigner('shared-secret');

$method = 'POST';
$uri = '/api/users';
$params = ['name' => 'John', 'email' => 'john@example.com'];

$auth = $signer->signRequest($method, $uri, $params);

// Send request with auth headers
$headers = [
    'X-Timestamp: ' . $auth['timestamp'],
    'X-Nonce: ' . $auth['nonce'],
    'X-Signature: ' . $auth['signature']
];

// Usage (Server)
$valid = $signer->verifyRequest(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_REQUEST,
    $_SERVER['HTTP_X_TIMESTAMP'],
    $_SERVER['HTTP_X_NONCE'],
    $_SERVER['HTTP_X_SIGNATURE']
);

if (!$valid) {
    http_response_code(401);
    die('Invalid signature');
}
```

**Why It Works**: API request signing prevents tampering and replay attacks. The signature is computed from the request method, URI, parameters, timestamp, and nonce using HMAC. The server recomputes the signature and compares it using `hash_equals()` for timing safety. The timestamp check prevents replay attacks by rejecting old requests.

### 3. Two-Factor Authentication (TOTP)

TOTP (Time-based One-Time Password) is the algorithm used by Google Authenticator and similar apps. It generates time-based codes that change every 30 seconds, providing an additional security layer.

```php
# filename: TOTP.php
<?php

declare(strict_types=1);

class TOTP {
    private const PERIOD = 30;  // 30 seconds
    private const DIGITS = 6;

    public static function generateSecret(): string {
        return base64_encode(random_bytes(20));
    }

    public static function getCode(string $secret, ?int $timestamp = null): string {
        $timestamp = $timestamp ?? time();
        $counter = floor($timestamp / self::PERIOD);

        $secret = base64_decode($secret);

        // Convert counter to binary string
        $counterBinary = pack('N*', 0, $counter);

        // Generate HMAC
        $hash = hash_hmac('sha1', $counterBinary, $secret, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        );

        $code = $code % pow(10, self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code, int $window = 1): bool {
        $timestamp = time();

        // Check current period and adjacent periods (to account for clock drift)
        for ($i = -$window; $i <= $window; $i++) {
            $testTime = $timestamp + ($i * self::PERIOD);
            $testCode = self::getCode($secret, $testTime);

            if (hash_equals($testCode, $code)) {
                return true;
            }
        }

        return false;
    }

    public static function getQRCodeUrl(string $secret, string $label, string $issuer = 'MyApp'): string {
        $url = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            urlencode($issuer),
            urlencode($label),
            $secret,
            urlencode($issuer)
        );

        return 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($url);
    }
}

// Usage
$secret = TOTP::generateSecret();
echo "Secret: $secret\n";

$code = TOTP::getCode($secret);
echo "Current code: $code\n";

// Verify code
$valid = TOTP::verify($secret, $code);
echo "Valid: " . ($valid ? 'yes' : 'no') . "\n";

// QR code for Google Authenticator
$qrUrl = TOTP::getQRCodeUrl($secret, 'user@example.com', 'MyApp');
echo "QR Code: $qrUrl\n";
```

**Why It Works**: TOTP generates time-based codes by computing an HMAC-SHA1 of the current time period (30-second windows) using a shared secret. The code changes every 30 seconds, and the verification allows a small window (±1 period) to account for clock drift. This provides strong two-factor authentication without requiring SMS or email.

### 4. JSON Web Tokens (JWT)

JWT is a compact, URL-safe token format used for stateless authentication and information exchange. It consists of three parts: header, payload, and signature, all base64url-encoded and separated by dots.

**JWT Structure**: `header.payload.signature`

```php
# filename: JWT.php
<?php

declare(strict_types=1);

class JWT {
    // Base64URL encoding (JWT uses URL-safe base64)
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    // Create JWT with HMAC-SHA256
    public static function encode(array $payload, string $secret, array $header = []): string {
        $defaultHeader = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        $header = array_merge($defaultHeader, $header);

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    // Verify and decode JWT with HMAC-SHA256
    public static function decode(string $token, string $secret): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;  // Invalid token format
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $expectedSignatureEncoded = self::base64UrlEncode($expectedSignature);

        if (!hash_equals($expectedSignatureEncoded, $signatureEncoded)) {
            return null;  // Invalid signature
        }

        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        // Verify algorithm
        if (($header['alg'] ?? '') !== 'HS256') {
            return null;  // Algorithm mismatch (prevent algorithm confusion attacks)
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;  // Token expired
        }

        // Check not-before
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            return null;  // Token not yet valid
        }

        return $payload;
    }

    // Create JWT with RSA signature (asymmetric)
    public static function encodeRSA(array $payload, string $privateKey, array $header = []): string {
        $defaultHeader = [
            'typ' => 'JWT',
            'alg' => 'RS256'
        ];
        $header = array_merge($defaultHeader, $header);

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // Sign with private key
        $signature = '';
        openssl_sign("$headerEncoded.$payloadEncoded", $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    // Verify JWT with RSA public key
    public static function decodeRSA(string $token, string $publicKey): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $signature = self::base64UrlDecode($signatureEncoded);
        $isValid = openssl_verify(
            "$headerEncoded.$payloadEncoded",
            $signature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($isValid !== 1) {
            return null;  // Invalid signature
        }

        // Decode and validate
        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (($header['alg'] ?? '') !== 'RS256') {
            return null;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}

// Usage: HMAC-SHA256 JWT
$secret = 'your-secret-key';
$payload = [
    'user_id' => 123,
    'username' => 'john_doe',
    'exp' => time() + 3600,  // Expires in 1 hour
    'iat' => time()  // Issued at
];

$token = JWT::encode($payload, $secret);
echo "JWT Token: $token\n";

$decoded = JWT::decode($token, $secret);
if ($decoded) {
    echo "User ID: " . $decoded['user_id'] . "\n";
    echo "Username: " . $decoded['username'] . "\n";
} else {
    echo "Invalid or expired token\n";
}

// Usage: RSA JWT (for distributed systems)
$keyPair = AsymmetricEncryption::generateKeyPair();
$payload = [
    'user_id' => 456,
    'role' => 'admin',
    'exp' => time() + 3600
];

$rsaToken = JWT::encodeRSA($payload, $keyPair['private']);
echo "RSA JWT: $rsaToken\n";

$rsaDecoded = JWT::decodeRSA($rsaToken, $keyPair['public']);
if ($rsaDecoded) {
    echo "User ID: " . $rsaDecoded['user_id'] . "\n";
    echo "Role: " . $rsaDecoded['role'] . "\n";
}
```

**Why It Works**: JWT provides stateless authentication - the server doesn't need to store session data. The signature ensures the token hasn't been tampered with. HMAC-SHA256 is symmetric (both parties need the secret), while RSA is asymmetric (only the server needs the private key, clients can verify with the public key). The expiration (`exp`) and not-before (`nbf`) claims prevent token reuse after expiration.

**JWT Security Best Practices**:
1. Always verify the algorithm in the header (prevent algorithm confusion attacks)
2. Use short expiration times (15 minutes to 1 hour)
3. Include `iat` (issued at) and `exp` (expiration) claims
4. Use HTTPS to prevent token interception
5. Store sensitive data server-side, not in JWT payload (it's base64-encoded, not encrypted)
6. Use RSA/ECDSA for distributed systems where you can't share secrets
7. Implement token refresh for long-lived sessions

## Key Rotation

Key rotation is the process of replacing encryption keys periodically to limit the impact of key compromise. Even if a key is leaked, rotating it limits how long attackers can use it.

### Key Rotation Strategy

```php
# filename: KeyRotation.php
<?php

declare(strict_types=1);

class KeyRotation {
    private string $currentKey;
    private ?string $previousKey = null;
    private int $rotationInterval;  // Days

    public function __construct(string $currentKey, int $rotationIntervalDays = 90) {
        $this->currentKey = $currentKey;
        $this->rotationInterval = $rotationIntervalDays;
    }

    // Encrypt with current key, include key version
    public function encrypt(string $plaintext): array {
        $encrypted = SymmetricEncryption::encrypt($plaintext, $this->currentKey);
        return [
            'data' => $encrypted,
            'key_version' => $this->getCurrentKeyVersion(),
            'encrypted_at' => time()
        ];
    }

    // Decrypt with current or previous key (during rotation period)
    public function decrypt(array $encrypted): string {
        $keyVersion = $encrypted['key_version'] ?? 'current';
        $key = $this->getKeyForVersion($keyVersion);

        try {
            return SymmetricEncryption::decrypt($encrypted['data'], $key);
        } catch (Exception $e) {
            // Try previous key if current fails (during rotation)
            if ($this->previousKey !== null && $keyVersion === 'current') {
                return SymmetricEncryption::decrypt($encrypted['data'], $this->previousKey);
            }
            throw $e;
        }
    }

    // Rotate to new key
    public function rotate(): void {
        $this->previousKey = $this->currentKey;
        $this->currentKey = SymmetricEncryption::generateKey();
    }

    // Check if rotation is needed
    public function shouldRotate(int $lastRotationTime): bool {
        return (time() - $lastRotationTime) > ($this->rotationInterval * 24 * 3600);
    }

    private function getCurrentKeyVersion(): string {
        return 'current';
    }

    private function getKeyForVersion(string $version): string {
        return match($version) {
            'current' => $this->currentKey,
            'previous' => $this->previousKey ?? $this->currentKey,
            default => throw new Exception("Unknown key version: $version")
        };
    }
}

// Usage
$rotation = new KeyRotation(SymmetricEncryption::generateKey(), 90);  // Rotate every 90 days

// Encrypt data
$encrypted = $rotation->encrypt('Sensitive data');
echo "Encrypted with key version: " . $encrypted['key_version'] . "\n";

// Decrypt data
$decrypted = $rotation->decrypt($encrypted);
echo "Decrypted: $decrypted\n";

// Rotate key
$rotation->rotate();
echo "Key rotated\n";

// Old data still decrypts (using previous key)
$decrypted = $rotation->decrypt($encrypted);
echo "Old data still decrypts: $decrypted\n";

// New data uses new key
$newEncrypted = $rotation->encrypt('New data');
echo "New data encrypted with new key\n";
```

**Why It Works**: Key rotation limits the "blast radius" of a key compromise. If a key is leaked, rotating it means attackers can only decrypt data encrypted before rotation. During rotation, you maintain both keys temporarily to decrypt old data while encrypting new data with the new key. After a grace period, you can discard the old key.

**Rotation Best Practices**:
1. Rotate keys regularly (every 90 days for high-security, annually for lower security)
2. Rotate immediately if compromise is suspected
3. Use key versioning to track which key encrypted which data
4. Maintain previous key temporarily during rotation period
5. Re-encrypt old data with new key when possible (key re-encryption)
6. Log all key rotation events for audit

## Cryptographic Attacks and Mitigations

Understanding common cryptographic attacks helps you avoid vulnerabilities. Here are the most important attacks and how to prevent them.

### 1. Timing Attacks

Timing attacks exploit differences in execution time to leak information about secrets.

```php
// ❌ VULNERABLE: Regular comparison leaks information
function badCompare(string $a, string $b): bool {
    if (strlen($a) !== strlen($b)) {
        return false;  // Leaks length information
    }
    
    for ($i = 0; $i < strlen($a); $i++) {
        if ($a[$i] !== $b[$i]) {
            return false;  // Stops early, leaks position
        }
    }
    return true;
}

// ✅ SECURE: Timing-safe comparison
function secureCompare(string $a, string $b): bool {
    return hash_equals($a, $b);  // Always compares all bytes
}

// Usage
$secret = 'my-secret-token';
$userInput = 'my-secret-token';

// Attacker can measure time differences
$start = microtime(true);
badCompare($secret, $userInput);
$time = microtime(true) - $start;
// Time varies based on where first difference occurs

// Secure version always takes same time
$start = microtime(true);
secureCompare($secret, $userInput);
$time = microtime(true) - $start;
// Time is constant regardless of input
```

**Mitigation**: Always use `hash_equals()` for comparing secrets, tokens, or hashes.

### 2. Padding Oracle Attacks

Padding oracle attacks exploit error messages from decryption failures to recover plaintext.

```php
// ❌ VULNERABLE: Reveals padding errors
function badDecrypt(string $ciphertext, string $key): string {
    try {
        return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    } catch (Exception $e) {
        // Different error messages for padding vs key errors
        if (strpos($e->getMessage(), 'padding') !== false) {
            return 'PADDING_ERROR';  // Leaks information!
        }
        return 'DECRYPTION_ERROR';
    }
}

// ✅ SECURE: Generic error messages
function secureDecrypt(string $ciphertext, string $key): string {
    try {
        $result = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($result === false) {
            throw new Exception("Decryption failed");
        }
        return $result;
    } catch (Exception $e) {
        // Always return same generic error
        throw new Exception("Invalid data");
    }
}
```

**Mitigation**: Use authenticated encryption (GCM, ChaCha20-Poly1305) which prevents padding oracle attacks, or always return generic error messages.

### 3. Side-Channel Attacks

Side-channel attacks exploit physical characteristics like power consumption, timing, or cache behavior.

```php
// ❌ VULNERABLE: Conditional operations leak information
function badCheck(string $password, string $hash): bool {
    if (strlen($password) !== strlen($hash)) {
        return false;  // Early return leaks length
    }
    
    // Branching based on secret leaks information
    for ($i = 0; $i < strlen($password); $i++) {
        if ($password[$i] !== $hash[$i]) {
            return false;  // Early return leaks position
        }
    }
    return true;
}

// ✅ SECURE: Constant-time operations
function secureCheck(string $password, string $hash): bool {
    return password_verify($password, $hash);  // Constant-time implementation
}
```

**Mitigation**: Use constant-time functions (`hash_equals()`, `password_verify()`), avoid branching on secret data, and use established libraries that handle side-channel resistance.

### 4. Chosen Plaintext Attacks

Attackers can encrypt chosen plaintexts and analyze ciphertexts to learn about the key.

```php
// ❌ VULNERABLE: Deterministic encryption (same plaintext = same ciphertext)
function badEncrypt(string $plaintext, string $key): string {
    return openssl_encrypt($plaintext, 'aes-256-ecb', $key);  // ECB mode!
}

// ✅ SECURE: Randomized encryption (IV/nonce ensures uniqueness)
function secureEncrypt(string $plaintext, string $key): string {
    $iv = random_bytes(16);  // Random IV for each encryption
    return openssl_encrypt($plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}
```

**Mitigation**: Always use random IVs/nonces, never use ECB mode, and prefer authenticated encryption modes.

### 5. Replay Attacks

Replay attacks involve intercepting and re-sending valid messages.

```php
// ✅ SECURE: Include timestamp and nonce
class ReplayProtection {
    private array $usedNonces = [];
    private int $maxAge = 300;  // 5 minutes

    public function signRequest(string $data, string $key): array {
        $timestamp = time();
        $nonce = bin2hex(random_bytes(16));
        
        $message = "$timestamp|$nonce|$data";
        $signature = hash_hmac('sha256', $message, $key);
        
        return [
            'data' => $data,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'signature' => $signature
        ];
    }

    public function verifyRequest(array $request, string $key): bool {
        // Check timestamp
        if (abs(time() - $request['timestamp']) > $this->maxAge) {
            return false;  // Request too old
        }

        // Check nonce reuse
        if (isset($this->usedNonces[$request['nonce']])) {
            return false;  // Nonce already used
        }
        $this->usedNonces[$request['nonce']] = true;

        // Verify signature
        $message = "{$request['timestamp']}|{$request['nonce']}|{$request['data']}";
        $expectedSignature = hash_hmac('sha256', $message, $key);
        
        return hash_equals($expectedSignature, $request['signature']);
    }
}
```

**Mitigation**: Include timestamps and nonces in signed messages, verify timestamps are recent, and track used nonces.

## Cryptographic Agility

Cryptographic agility is the ability to upgrade cryptographic algorithms over time without breaking existing systems. This is essential as algorithms become deprecated or broken.

### Algorithm Migration Strategy

```php
# filename: CryptographicAgility.php
<?php

declare(strict_types=1);

class CryptographicAgility {
    // Current recommended algorithms
    private const CURRENT_HASH = 'sha256';
    private const CURRENT_CIPHER = 'aes-256-gcm';
    private const CURRENT_PASSWORD = PASSWORD_ARGON2ID;

    // Detect algorithm from data
    public static function detectAlgorithm(string $data): ?string {
        // Password hashes include algorithm in the hash
        if (strpos($data, '$argon2id$') === 0) {
            return 'argon2id';
        }
        if (strpos($data, '$2y$') === 0) {
            return 'bcrypt';
        }
        
        // Hash length detection
        if (strlen($data) === 64 && ctype_xdigit($data)) {
            return 'sha256';  // Possibly
        }
        
        return null;
    }

    // Check if algorithm needs upgrade
    public static function needsUpgrade(string $algorithm): bool {
        $deprecated = ['md5', 'sha1', 'des', 'rc4'];
        return in_array(strtolower($algorithm), $deprecated);
    }

    // Migrate password hash to new algorithm
    public static function migratePasswordHash(string $oldHash, string $password): string {
        // Verify old hash still works
        if (!password_verify($password, $oldHash)) {
            throw new Exception("Invalid password");
        }

        // Check if already using current algorithm
        if (!password_needs_rehash($oldHash, self::CURRENT_PASSWORD)) {
            return $oldHash;  // Already current
        }

        // Rehash with current algorithm
        return password_hash($password, self::CURRENT_PASSWORD);
    }

    // Migrate encrypted data (requires re-encryption)
    public static function migrateEncryption(
        array $oldEncrypted,
        string $oldKey,
        string $newKey
    ): array {
        // Decrypt with old key
        $plaintext = SymmetricEncryption::decrypt($oldEncrypted, $oldKey);

        // Encrypt with new key and current algorithm
        return SymmetricEncryption::encrypt($plaintext, $newKey);
    }
}

// Usage: Migrate password hash
$oldHash = password_hash('password123', PASSWORD_BCRYPT);
echo "Old hash: $oldHash\n";

// Check if migration needed
if (password_needs_rehash($oldHash, PASSWORD_ARGON2ID)) {
    $newHash = CryptographicAgility::migratePasswordHash($oldHash, 'password123');
    echo "New hash: $newHash\n";
    // Update database with new hash
}
```

**Why It Works**: Cryptographic agility allows gradual migration without breaking existing systems. You detect which algorithm was used, verify it still works, and migrate to newer algorithms when needed. This is especially important for password hashes where you can't decrypt and re-encrypt - you must wait for users to log in.

**Migration Best Practices**:
1. Always support multiple algorithm versions during migration
2. Migrate on-the-fly when possible (e.g., password verification)
3. Log all algorithm usage for audit
4. Set deprecation timelines for old algorithms
5. Test migration paths thoroughly before deploying
6. Keep old keys/algorithms available during transition period

## Security Best Practices

### 1. Never Roll Your Own Crypto

```php
# filename: security-examples.php
<?php

declare(strict_types=1);

// ❌ DON'T: Custom encryption
function badEncrypt($data, $key) {
    return base64_encode($data ^ $key);  // INSECURE!
}

// ✅ DO: Use proven libraries
$encrypted = SodiumEncryption::encrypt($data, $key);
```

**Why It Works**: Cryptographic algorithms require extensive security analysis and testing. Even small mistakes can lead to complete compromise. Always use well-tested libraries like Libsodium or PHP's built-in functions.

### 2. Use Timing-Safe Comparisons

```php
// ❌ DON'T: Regular comparison (timing attack vulnerable)
if ($hash === $expectedHash) {
    // ...
}

// ✅ DO: Timing-safe comparison
if (hash_equals($hash, $expectedHash)) {
    // ...
}
```

**Why It Works**: Regular string comparison (`===`) stops at the first difference, allowing attackers to guess secrets byte-by-byte by measuring response times. `hash_equals()` always compares all bytes, preventing timing attacks.

### 3. Generate Secure Random Values

```php
// ❌ DON'T: Predictable randomness
$token = md5(time() . rand());  // INSECURE!

// ✅ DO: Cryptographically secure
$token = SecureRandom::token(32);
```

**Why It Works**: `rand()` and `time()` are predictable. Attackers can guess tokens generated this way. `random_bytes()` uses cryptographically secure random number generators that are unpredictable.

### 4. Store Keys Securely

```php
// ❌ DON'T: Hardcode keys
$key = 'my-secret-key';

// ✅ DO: Store in environment variables
$key = getenv('ENCRYPTION_KEY');

// ✅ DO: Use key management service (AWS KMS, etc.)
```

**Why It Works**: Hardcoded keys end up in version control and are accessible to anyone with code access. Environment variables and key management services keep keys separate from code and provide access controls.

## Wrap-up

You've completed this chapter on cryptographic algorithms! Here's what you accomplished:

- ✓ Implemented secure hash functions using SHA-256 and HMAC for data integrity
- ✓ Created modern password hashing system with Argon2id and bcrypt
- ✓ Built symmetric encryption using AES-256-GCM and Libsodium
- ✓ Implemented digital signatures for message authentication
- ✓ Generated cryptographically secure random numbers and tokens
- ✓ Derived encryption keys from passwords using PBKDF2 and Argon2
- ✓ Built real-world applications: encrypted sessions, API signing, TOTP, and JWT authentication
- ✓ Learned critical security best practices and common pitfalls to avoid
- ✓ Implemented asymmetric encryption (RSA) and key exchange protocols (DH, ECDH)
- ✓ Understood nonce management and key rotation strategies
- ✓ Learned about cryptographic attacks and how to prevent them
- ✓ Implemented cryptographic agility for algorithm migration

Cryptographic algorithms provide essential security for modern applications:

- **Hashing**: SHA-256, HMAC for integrity verification
- **Password Hashing**: Argon2id, bcrypt for secure password storage
- **Encryption**: AES-256-GCM (symmetric), RSA/ECC (asymmetric), Libsodium for data protection
- **Key Exchange**: Diffie-Hellman, ECDH for establishing shared secrets
- **Random Numbers**: `random_bytes()` for unpredictability
- **Signatures**: Digital signatures for authenticity
- **Key Derivation**: PBKDF2, Argon2 for secure key generation
- **Key Management**: Key rotation, cryptographic agility for algorithm migration
- **Token Authentication**: JWT (JSON Web Tokens) for stateless authentication

**Critical Rules to Remember**:
1. Always use established libraries (Libsodium, OpenSSL) - never roll your own crypto
2. Keep encryption keys secret and secure (use environment variables or key management services)
3. Use timing-safe comparisons (`hash_equals()`) to prevent timing attacks
4. Generate cryptographically secure random numbers (`random_bytes()`, not `rand()`)
5. Use appropriate algorithms for each purpose (password hashing ≠ general hashing)
6. Never reuse nonces/IVs with the same key - always generate random nonces
7. Rotate encryption keys regularly and implement cryptographic agility for algorithm upgrades
8. Use authenticated encryption (GCM, ChaCha20-Poly1305) to prevent padding oracle attacks
9. Include timestamps and nonces in signed messages to prevent replay attacks
10. Understand common attacks (timing, padding oracle, side-channel) and how to mitigate them

These cryptographic techniques form the foundation of secure application development. Understanding when and how to use them correctly protects your applications and users from security breaches.

## Further Reading

- [PHP Password Hashing](https://www.php.net/manual/en/book.password.php) — Official PHP password hashing documentation
- [Libsodium PHP Documentation](https://www.php.net/manual/en/book.sodium.php) — Modern cryptographic library for PHP
- [OWASP Cryptographic Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cryptographic_Storage_Cheat_Sheet.html) — Security best practices for cryptographic storage
- [NIST Cryptographic Standards](https://csrc.nist.gov/projects/cryptographic-standards-and-guidelines) — Official cryptographic standards and guidelines
- [RFC 8018: PKCS #5](https://tools.ietf.org/html/rfc8018) — Password-Based Cryptography Specification (PBKDF2)
- [RFC 7519: JSON Web Token (JWT)](https://tools.ietf.org/html/rfc7519) — JWT specification and best practices
- [JWT.io](https://jwt.io/) — Interactive JWT debugger and library recommendations


<ChapterCheckbox 
  seriesId="php-algorithms"
  chapterId="35"
  label="Cryptographic Algorithms mastered!"
/>
## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 35 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/php-algorithms/chapter-35)**

Clone the repository to run examples:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/php-algorithms/chapter-35
php 01-*.php
```

## Practice Exercises

### Exercise 1: Encrypted File Storage System

**Goal**: Create a secure file storage system that encrypts files before saving them.

**Requirements**:
- Create a class `EncryptedFileStorage` with methods `store()` and `retrieve()`
- Use AES-256-GCM encryption via Libsodium
- Generate a unique key per file or use a master key
- Store encrypted files with metadata (filename, size, timestamp)
- Implement file integrity verification

**Validation**: Test your implementation:

```php
# filename: test-encrypted-storage.php
<?php

declare(strict_types=1);

$storage = new EncryptedFileStorage('master-key');

// Store a file
$storage->store('secret.txt', 'Sensitive data here');

// Retrieve the file
$content = $storage->retrieve('secret.txt');
echo "Retrieved: $content\n";

// Verify integrity
$isValid = $storage->verifyIntegrity('secret.txt');
echo "Integrity valid: " . ($isValid ? 'yes' : 'no') . "\n";
```

Expected output:

```
Retrieved: Sensitive data here
Integrity valid: yes
```

### Exercise 2: API Authentication with HMAC Signatures

**Goal**: Build a complete API authentication system using HMAC signatures.

**Requirements**:
- Create client-side signing class
- Create server-side verification middleware
- Support GET, POST, PUT, DELETE methods
- Include timestamp and nonce for replay protection
- Return proper HTTP status codes (401 for invalid signatures)

**Validation**: Test with multiple requests:

```php
# Client side
$client = new ApiClient('api-key', 'secret-key');
$response = $client->post('/api/users', ['name' => 'John']);

# Server should verify signature and return 200 or 401
```

### Exercise 3: Secure Password Reset System

**Goal**: Implement a secure password reset flow with time-limited tokens.

**Requirements**:
- Generate cryptographically secure reset tokens
- Store tokens with expiration time (e.g., 1 hour)
- Include user ID and timestamp in token
- Verify token before allowing password reset
- Invalidate token after use

**Validation**: Test the complete flow:

```php
# Generate reset token
$token = PasswordReset::generateToken($userId);
echo "Reset token: $token\n";

# Verify token (within expiration)
$isValid = PasswordReset::verifyToken($token, $userId);
echo "Token valid: " . ($isValid ? 'yes' : 'no') . "\n";

# Reset password
PasswordReset::resetPassword($token, $userId, $newPassword);
```

### Exercise 4: End-to-End Encrypted Messaging

**Goal**: Create a messaging system where messages are encrypted before storage.

**Requirements**:
- Each user has a public/private key pair
- Messages are encrypted with recipient's public key
- Only the recipient can decrypt messages
- Implement message signing for authenticity
- Store encrypted messages in database

**Validation**: Test message exchange:

```php
# User A sends encrypted message to User B
$alice = new User('Alice');
$bob = new User('Bob');

$message = 'Secret message';
$encrypted = $alice->sendMessage($bob->getPublicKey(), $message);

# User B receives and decrypts
$decrypted = $bob->receiveMessage($encrypted);
echo "Decrypted: $decrypted\n";
```

Expected output:

```
Decrypted: Secret message
```
