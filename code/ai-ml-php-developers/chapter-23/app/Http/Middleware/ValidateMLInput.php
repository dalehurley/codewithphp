<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateMLInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize text input
        if ($request->has('text')) {
            $text = $request->input('text');

            // Remove potentially dangerous content
            $text = $this->sanitizeText($text);

            // Check for excessive length (potential DoS)
            if (strlen($text) > 10000) {
                Log::warning('ML input exceeds maximum length', [
                    'ip' => $request->ip(),
                    'length' => strlen($text),
                ]);

                return response()->json([
                    'error' => 'Input text too long',
                    'message' => 'Maximum allowed length is 10,000 characters',
                ], 422);
            }

            // Check for suspicious patterns
            if ($this->containsSuspiciousPatterns($text)) {
                Log::warning('Suspicious ML input detected', [
                    'ip' => $request->ip(),
                    'text_preview' => substr($text, 0, 100),
                ]);

                return response()->json([
                    'error' => 'Invalid input',
                    'message' => 'Input contains invalid characters or patterns',
                ], 422);
            }

            // Update request with sanitized text
            $request->merge(['text' => $text]);
        }

        // Validate numeric inputs
        if ($request->has('limit')) {
            $limit = (int) $request->input('limit');

            if ($limit < 1 || $limit > 100) {
                return response()->json([
                    'error' => 'Invalid limit parameter',
                    'message' => 'Limit must be between 1 and 100',
                ], 422);
            }

            $request->merge(['limit' => $limit]);
        }

        // Log ML request for monitoring
        Log::info('ML request validated', [
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
            'has_text' => $request->has('text'),
            'text_length' => $request->has('text') ? strlen($request->input('text')) : 0,
        ]);

        return $next($request);
    }

    /**
     * Sanitize text input by removing potentially dangerous content.
     */
    private function sanitizeText(string $text): string
    {
        // Remove null bytes
        $text = str_replace("\0", '', $text);

        // Remove control characters (except newlines and tabs)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);

        // Normalize whitespace
        $text = preg_replace('/\s+/u', ' ', $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Check for suspicious patterns that might indicate attacks.
     */
    private function containsSuspiciousPatterns(string $text): bool
    {
        // Check for excessive special characters (potential injection)
        $specialCharCount = preg_match_all('/[<>{}|\\\\\[\]`]/u', $text);
        if ($specialCharCount > strlen($text) * 0.3) {
            return true;
        }

        // Check for SQL injection patterns
        $sqlPatterns = [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
        ];

        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        // Check for script injection
        if (preg_match('/<script|javascript:|onerror=/i', $text)) {
            return true;
        }

        return false;
    }
}
