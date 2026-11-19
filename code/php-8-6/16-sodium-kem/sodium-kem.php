<?php

// filename: sodium-kem.php

declare(strict_types=1);

$xWingKeypair = sodium_crypto_kem_keypair();
[$ciphertext, $sent] = sodium_crypto_kem_enc(sodium_crypto_kem_publickey($xWingKeypair));
$received = sodium_crypto_kem_dec($ciphertext, sodium_crypto_kem_secretkey($xWingKeypair));
echo 'X-Wing keys agree: ', hash_equals($sent, $received) ? 'yes' : 'no', PHP_EOL;

$mlKemKeypair = sodium_crypto_kem_mlkem768_keypair();
[$ciphertext, $sent] = sodium_crypto_kem_mlkem768_enc(sodium_crypto_kem_mlkem768_publickey($mlKemKeypair));
$received = sodium_crypto_kem_mlkem768_dec($ciphertext, sodium_crypto_kem_mlkem768_secretkey($mlKemKeypair));
echo 'ML-KEM-768 keys agree: ', hash_equals($sent, $received) ? 'yes' : 'no', PHP_EOL;
