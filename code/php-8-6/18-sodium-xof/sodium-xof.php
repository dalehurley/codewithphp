<?php

// filename: sodium-xof.php

declare(strict_types=1);

$data = 'order-42';
$digest = sodium_crypto_xof_shake128(32, $data);

$state = sodium_crypto_xof_shake128_init();
sodium_crypto_xof_shake128_update($state, 'order-');
sodium_crypto_xof_shake128_update($state, '42');
echo 'SHAKE128 chunks agree: ', hash_equals($digest, sodium_crypto_xof_shake128_squeeze($state, 32))
    ? 'yes' : 'no', PHP_EOL;

echo 'SHAKE256 bytes: ', strlen(sodium_crypto_xof_shake256(32, $data)), PHP_EOL;
echo 'TurboSHAKE128 bytes: ', strlen(sodium_crypto_xof_turboshake128(16, $data)), PHP_EOL;
echo 'TurboSHAKE256 bytes: ', strlen(sodium_crypto_xof_turboshake256(64, $data)), PHP_EOL;
