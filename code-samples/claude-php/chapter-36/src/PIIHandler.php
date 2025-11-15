<?php

declare(strict_types=1);

namespace App;

class PIIHandler
{
    private array $piiPatterns = [
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
        'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
        'credit_card' => '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/'
    ];
    
    public function detect(string $text): array
    {
        $detected = [];
        foreach ($this->piiPatterns as $type => $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                $detected[$type] = $matches[0];
            }
        }
        return $detected;
    }
    
    public function redact(string $text): string
    {
        foreach ($this->piiPatterns as $pattern) {
            $text = preg_replace($pattern, '[REDACTED]', $text);
        }
        return $text;
    }
    
    public function mask(string $value, int $visibleChars = 4): string
    {
        $len = strlen($value);
        if ($len <= $visibleChars) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - $visibleChars) . substr($value, -$visibleChars);
    }
}
