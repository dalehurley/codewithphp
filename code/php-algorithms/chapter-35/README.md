# Chapter 35: Cryptographic Algorithms - Code Samples

This directory contains comprehensive, runnable PHP code examples for Chapter 35 of the PHP Algorithms series, focusing on cryptographic algorithms for secure applications.

## Overview

Cryptographic algorithms protect data confidentiality, integrity, and authenticity. These examples demonstrate essential cryptographic operations using PHP's built-in functions and industry-standard libraries.

**IMPORTANT**: These examples are educational. For production systems, always use well-tested cryptographic libraries and follow current security best practices.

## Code Samples

### 1. Hash Functions (`01-hash-functions.php`)

**Purpose**: Demonstrates cryptographic hash functions for data integrity and authentication.

**Key Concepts**:
- SHA-256/SHA-512 hashing
- HMAC (Hash-based Message Authentication Code)
- File hashing and integrity verification
- Streaming hash for large files
- Timing-safe comparisons

**Classes**:
- `SecureHash`: Hash generation and verification utilities
- `IntegrityChecker`: File integrity monitoring system

**Use Cases**:
- Data integrity verification
- API request signing
- File checksum validation
- Message authentication

**Security Notes**:
- ❌ MD5 and SHA-1 are cryptographically broken - DO NOT USE
- ✓ Use SHA-256, SHA-512, or BLAKE2b
- Always use `hash_equals()` for timing-safe comparison

**Run**:
```bash
php 01-hash-functions.php
```

---

### 2. Password Hashing (`02-password-hashing.php`)

**Purpose**: Secure password storage using modern password hashing algorithms.

**Key Concepts**:
- Argon2id password hashing (recommended)
- bcrypt password hashing (good alternative)
- Password verification (timing-safe)
- Rehashing detection for security upgrades
- Password strength validation
- Secure password generation

**Classes**:
- `PasswordManager`: Password hashing and verification
- `UserAuthSystem`: Complete authentication system example

**Use Cases**:
- User authentication systems
- Password storage and verification
- Password strength requirements
- Security parameter upgrades

**Critical Rules**:
- **NEVER** use simple hashes (MD5, SHA-1, SHA-256) for passwords
- **ALWAYS** use `password_hash()` with Argon2id or bcrypt
- **ALWAYS** use `password_verify()` for verification
- Store only the hash, never the plaintext password

**Run**:
```bash
php 02-password-hashing.php
```

---

### 3. Symmetric Encryption (`03-symmetric-encryption.php`)

**Purpose**: Encrypt and decrypt data using symmetric key algorithms.

**Key Concepts**:
- AES-256-GCM encryption (OpenSSL)
- XChaCha20-Poly1305 encryption (Libsodium - recommended)
- Authenticated encryption (AEAD)
- File encryption/decryption
- Key derivation from passwords (PBKDF2, Argon2id)

**Classes**:
- `SymmetricEncryption`: AES-256-GCM using OpenSSL
- `SodiumEncryption`: Modern encryption using Libsodium
- `KeyDerivation`: Password-based key derivation

**Use Cases**:
- Database field encryption
- File encryption
- Session data encryption
- Secure data storage

**Security Notes**:
- Use authenticated encryption (GCM mode or Libsodium)
- Never reuse IV/nonce with the same key
- Store keys securely (environment variables, key management systems)
- Use key derivation for password-based encryption

**Run**:
```bash
php 03-symmetric-encryption.php
```

---

### 4. Secure Random Generation (`04-secure-random.php`)

**Purpose**: Generate cryptographically secure random numbers and tokens.

**Key Concepts**:
- Cryptographically secure random bytes
- Random integers in range
- Random strings and tokens
- UUID generation
- TOTP (Time-based One-Time Password) implementation
- Secure array shuffling

**Classes**:
- `SecureRandom`: Secure random generation utilities
- `TOTP`: Two-factor authentication implementation

**Use Cases**:
- Session tokens generation
- API keys creation
- Password reset tokens
- Two-factor authentication
- Cryptographic keys and salts

**Critical Rules**:
- **NEVER** use `rand()`, `mt_rand()`, or `uniqid()` for security
- **ALWAYS** use `random_bytes()` and `random_int()`
- Tokens should be at least 32 bytes for security
- Use timing-safe comparison for token verification

**Run**:
```bash
php 04-secure-random.php
```

---

## Running All Examples

To run all examples in sequence:

```bash
for file in 0*.php; do
    echo "Running $file..."
    php "$file"
    echo "---"
done
```

## Requirements

- PHP 8.0 or higher
- OpenSSL extension (typically included)
- Sodium extension (included in PHP 7.2+)
- No external dependencies required

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
if ($hash === $expectedHash) { }

// ✅ DO: Timing-safe comparison
if (hash_equals($hash, $expectedHash)) { }
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
```

### 5. Use Modern Algorithms

| Purpose | ❌ Avoid | ✓ Use |
|---------|---------|-------|
| Hashing | MD5, SHA-1 | SHA-256, BLAKE2b |
| Passwords | SHA-256, bcrypt(cost<12) | Argon2id, bcrypt(cost>=12) |
| Encryption | DES, RC4, ECB mode | AES-256-GCM, XChaCha20 |
| Random | rand(), mt_rand() | random_bytes(), random_int() |

## Algorithm Summary

| Algorithm | Purpose | Security Level | Performance |
|-----------|---------|----------------|-------------|
| SHA-256 | General hashing | ✓ Strong | Fast |
| SHA-512 | High-security hashing | ✓ Strong | Fast |
| BLAKE2b | Modern hashing | ✓ Strong | Very fast |
| Argon2id | Password hashing | ✓ Very Strong | Intentionally slow |
| bcrypt | Password hashing | ✓ Strong | Intentionally slow |
| AES-256-GCM | Encryption | ✓ Strong | Fast |
| XChaCha20 | Modern encryption | ✓ Strong | Very fast |
| PBKDF2 | Key derivation | ✓ Good | Configurable |

## Real-World Applications

1. **User Authentication**
   - Secure password storage
   - Session management
   - Two-factor authentication

2. **API Security**
   - Request signing (HMAC)
   - API key generation
   - Token-based authentication

3. **Data Protection**
   - Database encryption
   - File encryption
   - Secure data transmission

4. **Integrity Verification**
   - File integrity checking
   - Software distribution
   - Backup verification

## Common Pitfalls

1. **Using Weak Algorithms**
   ```php
   // ❌ NEVER do this
   $hash = md5($password);  // Broken!
   $encrypted = mcrypt_encrypt(...);  // Deprecated!
   ```

2. **Incorrect IV/Nonce Handling**
   ```php
   // ❌ NEVER reuse IV
   $iv = "1234567890123456";  // Static IV - INSECURE!

   // ✓ Generate random IV for each encryption
   $iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
   ```

3. **Weak Password Requirements**
   ```php
   // ❌ Too weak
   if (strlen($password) >= 6) { /* ... */ }

   // ✓ Strong requirements
   if (PasswordManager::strength($password)['score'] >= 6) { /* ... */ }
   ```

4. **Insecure Random**
   ```php
   // ❌ Predictable
   $token = uniqid();  // NOT cryptographically secure!

   // ✓ Secure
   $token = SecureRandom::token(32);
   ```

## Further Reading

- [OWASP Cryptographic Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cryptographic_Storage_Cheat_Sheet.html)
- [PHP password_hash() documentation](https://www.php.net/manual/en/function.password-hash.php)
- [Libsodium documentation](https://libsodium.gitbook.io/doc/)
- [NIST Cryptographic Standards](https://csrc.nist.gov/projects/cryptographic-standards-and-guidelines)

## License

MIT License - Free to use for learning and commercial purposes.

---

**Part of the PHP Algorithms Series**
Chapter 35: Cryptographic Algorithms

⚠️ **Security Warning**: Always consult with security professionals for production systems. Keep cryptographic libraries up to date and follow the latest security recommendations.
