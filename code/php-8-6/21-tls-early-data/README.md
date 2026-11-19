# Send TLS 1.3 Early Data

Send a read-only status probe when resuming a loopback TLS session. TLS 1.3 early data can reach the server before the handshake completes.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/21-tls-early-data/tls-early-data.php
```

Requirements: The `openssl` extension with TLS 1.3, `proc_open()`, and access to loopback sockets. The `openssl` command generates a self-signed certificate trusted only by the lab client.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/21-tls-early-data/).
