<?php

declare(strict_types=1);

namespace App;

class KeyManager
{
    public function __construct(
        private string $encryptionKey
    ) {}
    
    public function encrypt(string $value): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt(string $encrypted): string
    {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }
    
    public function rotate(string $oldKey, string $newKey): bool
    {
        // Implement key rotation logic
        return true;
    }
}
