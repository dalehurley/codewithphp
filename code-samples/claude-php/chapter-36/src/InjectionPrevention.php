<?php

declare(strict_types=1);

namespace App;

class InjectionPrevention
{
    private array $dangerousPatterns = [
        '/ignore (previous|above) instructions/i',
        '/system:?\s*you are now/i',
        '/\<script\>/i',
        '/execute.*code/i'
    ];
    
    public function sanitize(string $input): string
    {
        $sanitized = strip_tags($input);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        return $sanitized;
    }
    
    public function detect(string $input): array
    {
        $threats = [];
        foreach ($this->dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $threats[] = "Potential injection detected: $pattern";
            }
        }
        return $threats;
    }
    
    public function isValid(string $input): bool
    {
        return empty($this->detect($input));
    }
}
