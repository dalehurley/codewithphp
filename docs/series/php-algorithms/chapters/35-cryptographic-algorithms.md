---
title: "Cryptographic Algorithms"
description: "Essential cryptographic algorithms for secure PHP applications including hashing, encryption, digital signatures, and key management"
series: "php-algorithms"
chapter: 35
order: 35
difficulty: "advanced"
prerequisites: ["Security Basics", "Hash Functions", "Number Theory"]
---

# Chapter 35: Cryptographic Algorithms

## Introduction

Cryptographic algorithms protect data confidentiality, integrity, and authenticity. This chapter covers cryptographic fundamentals essential for secure application development in PHP.

**IMPORTANT**: This chapter is educational. For production systems, always use well-tested cryptographic libraries like `libsodium` or PHP's built-in functions.

## Cryptographic Hash Functions

Hash functions convert arbitrary data into fixed-size digests. Cryptographic hashes are:
- **Deterministic**: Same input → same output
- **One-way**: Computationally infeasible to reverse
- **Collision-resistant**: Hard to find two inputs with same hash
- **Avalanche effect**: Small input change → drastically different hash

### Using PHP's Built-in Hash Functions

```php
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

```php
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

## Password Hashing

**NEVER use simple hashes for passwords!** Use password-specific algorithms designed to be slow.

### Modern Password Hashing

```php
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

## Encryption and Decryption

### Symmetric Encryption (Same key for encrypt/decrypt)

```php
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

### Using Libsodium (Recommended)

```php
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

## Cryptographically Secure Random Numbers

### Random Number Generation

```php
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

## Digital Signatures

### Using Libsodium for Signing

```php
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

## Key Derivation

### Password-Based Key Derivation

```php
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

## Real-World Applications

### 1. Encrypted Session Storage

```php
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

### 2. API Request Signing

```php
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

### 3. Two-Factor Authentication (TOTP)

```php
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

## Security Best Practices

### 1. Never Roll Your Own Crypto

```php
// ❌ DON'T: Custom encryption
function badEncrypt($data, $key) {
    return base64_encode($data ^ $key);  // INSECURE!
}

// ✅ DO: Use proven libraries
$encrypted = SodiumEncryption::encrypt($data, $key);
```

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

### 3. Generate Secure Random Values

```php
// ❌ DON'T: Predictable randomness
$token = md5(time() . rand());  // INSECURE!

// ✅ DO: Cryptographically secure
$token = SecureRandom::token(32);
```

### 4. Store Keys Securely

```php
// ❌ DON'T: Hardcode keys
$key = 'my-secret-key';

// ✅ DO: Store in environment variables
$key = getenv('ENCRYPTION_KEY');

// ✅ DO: Use key management service (AWS KMS, etc.)
```

## Summary

Cryptographic algorithms provide essential security:

- **Hashing**: SHA-256, HMAC for integrity
- **Password Hashing**: Argon2id, bcrypt for passwords
- **Encryption**: AES-256-GCM, Libsodium for data protection
- **Random Numbers**: random_bytes() for unpredictability
- **Signatures**: Digital signatures for authenticity
- **Key Derivation**: PBKDF2, Argon2 for key generation

**Critical Rules**:
1. Use established libraries (Libsodium, OpenSSL)
2. Never implement custom crypto
3. Keep keys secret and secure
4. Use timing-safe comparisons
5. Generate cryptographically secure random numbers

## Next Steps

- **Chapter 25: Security Patterns** - Application security practices
- **Chapter 29: Performance Optimization** - Efficient crypto implementation
- **Chapter 30: Real-World Case Studies** - Production security patterns

## Practice Exercises

1. Implement encrypted file storage system
2. Build API authentication with HMAC signatures
3. Create secure session management system
4. Implement password reset with secure tokens
5. Build end-to-end encrypted messaging system
