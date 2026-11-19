# Authenticate TLS Streams with External PSKs

Use an external pre-shared key on a loopback connection. The client presents an identity and the server uses it to select the key.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/20-tls-pre-shared-keys/tls-pre-shared-keys.php
```

Requirements: The `openssl` extension with TLS 1.3, `proc_open()`, and access to loopback sockets.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/20-tls-pre-shared-keys/).
