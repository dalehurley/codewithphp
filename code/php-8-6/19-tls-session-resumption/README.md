# Resume a TLS Stream Session

Connect twice to a local TLS server and inspect whether the second connection reuses the first session. Resumption can reduce the cost of opening another connection.

Run from the repository root with PHP 8.6.

```bash
php code/php-8-6/19-tls-session-resumption/tls-session-resumption.php
```

Requirements: The `openssl` extension with TLS 1.3, `proc_open()`, and access to loopback sockets. The `openssl` command generates a self-signed certificate trusted only by the lab client.

[Read the chapter on Code with PHP](https://codewithphp.com/series/php-8-6/chapters/19-tls-session-resumption/).
