<?php

// filename: sodium-ipcrypt.php

declare(strict_types=1);

$ip = '192.0.2.15';

$key = sodium_crypto_ipcrypt_keygen();
echo 'Deterministic: ', sodium_crypto_ipcrypt_decrypt(sodium_crypto_ipcrypt_encrypt($ip, $key), $key), PHP_EOL;

$ndKey = sodium_crypto_ipcrypt_nd_keygen();
echo 'ND: ', sodium_crypto_ipcrypt_nd_decrypt(sodium_crypto_ipcrypt_nd_encrypt($ip, $ndKey), $ndKey), PHP_EOL;

$ndxKey = sodium_crypto_ipcrypt_ndx_keygen();
echo 'NDX: ', sodium_crypto_ipcrypt_ndx_decrypt(sodium_crypto_ipcrypt_ndx_encrypt($ip, $ndxKey), $ndxKey), PHP_EOL;

$prefixKey = sodium_crypto_ipcrypt_pfx_keygen();
echo 'PFX: ', sodium_crypto_ipcrypt_pfx_decrypt(sodium_crypto_ipcrypt_pfx_encrypt($ip, $prefixKey), $prefixKey), PHP_EOL;

echo 'Binary round trip: ', sodium_bin2ip(sodium_ip2bin($ip)), PHP_EOL;
