<?php

declare(strict_types=1);

namespace App\Sanitization;

class InputSanitizer
{
    /**
     * Sanitize string input (remove tags, encode special chars)
     */
    public static function string(string $input): string
    {
        // Remove HTML tags
        $cleaned = strip_tags($input);
        
        // Trim whitespace
        $cleaned = trim($cleaned);
        
        // Remove null bytes (security risk)
        $cleaned = str_replace("\0", '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Sanitize email (remove invalid characters)
     */
    public static function email(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL (remove invalid characters)
     */
    public static function url(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize integer (remove non-numeric characters)
     */
    public static function integer(string $input): int
    {
        return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float (remove non-numeric characters except decimal point)
     */
    public static function float(string $input): float
    {
        return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Escape HTML for safe output (prevent XSS)
     */
    public static function escapeHtml(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize for database storage (prevent SQL injection)
     * Note: Use prepared statements instead, this is just for demonstration
     */
    public static function forDatabase(string $input): string
    {
        // Remove SQL injection attempts
        $dangerous = ['--', ';', '/*', '*/', 'xp_', 'sp_'];
        $cleaned = str_replace($dangerous, '', $input);
        
        // Remove null bytes
        $cleaned = str_replace("\0", '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Sanitize filename (remove dangerous characters)
     */
    public static function filename(string $input): string
    {
        // Remove path components
        $filename = basename($input);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Prevent hidden files
        if (strpos($filename, '.') === 0) {
            $filename = 'file_' . $filename;
        }
        
        return $filename;
    }

    /**
     * Sanitize array of inputs recursively
     */
    public static function array(array $input, ?callable $sanitizer = null): array
    {
        $sanitizer = $sanitizer ?? [self::class, 'string'];
        
        return array_map(function ($value) use ($sanitizer) {
            if (is_array($value)) {
                return self::array($value, $sanitizer);
            }
            if (is_string($value)) {
                return $sanitizer($value);
            }
            return $value;
        }, $input);
    }
}

