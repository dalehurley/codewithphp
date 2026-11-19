# Establish a Shared Key with X-Wing and ML-KEM-768

Walk through key encapsulation and decapsulation for two recipients. A key encapsulation mechanism lets a sender derive a shared secret using a recipient’s public key and send a ciphertext the recipient can decapsulate.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/16-sodium-kem/sodium-kem.php
```

Requirements: The `sodium` extension built with libsodium 1.0.22 or later.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/16-sodium-kem/).
