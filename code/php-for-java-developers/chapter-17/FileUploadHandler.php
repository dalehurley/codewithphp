<?php

declare(strict_types=1);

namespace App\Upload;

use InvalidArgumentException;
use RuntimeException;

class FileUploadHandler
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Validate and upload file
     */
    public function upload(array $file, string $uploadDir): array
    {
        // Check for upload errors
        $this->checkUploadError($file['error']);

        // Validate file size
        $this->validateFileSize($file['size']);

        // Validate file type
        $this->validateFileType($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file['name']);
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        return [
            'filename' => $filename,
            'path' => $destination,
            'size' => $file['size'],
            'type' => $file['type'],
        ];
    }

    /**
     * Check for PHP upload errors
     */
    private function checkUploadError(int $error): void
    {
        $errorMessages = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension stopped the file upload',
        ];

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException($errorMessages[$error] ?? 'Unknown upload error');
        }
    }

    /**
     * Validate file size
     */
    private function validateFileSize(int $size): void
    {
        if ($size === 0) {
            throw new InvalidArgumentException('File is empty');
        }

        if ($size > self::MAX_FILE_SIZE) {
            $maxSizeMB = self::MAX_FILE_SIZE / (1024 * 1024);
            throw new InvalidArgumentException("File size exceeds maximum of {$maxSizeMB}MB");
        }
    }

    /**
     * Validate file type using multiple methods
     */
    private function validateFileType(array $file): void
    {
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('File type not allowed');
        }

        // Validate MIME type (can be spoofed, so we also check content)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('Invalid file type detected');
        }

        // Additional validation: Check file signature (magic bytes)
        $this->validateFileSignature($file['tmp_name'], $extension);
    }

    /**
     * Validate file signature (magic bytes) to prevent spoofing
     */
    private function validateFileSignature(string $filePath, string $extension): void
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Cannot read file for validation');
        }

        $signature = fread($handle, 8);
        fclose($handle);

        $validSignatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'gif' => ["\x47\x49\x46\x38"],
            'webp' => ["RIFF", "WEBP"],
        ];

        if (!isset($validSignatures[$extension])) {
            throw new InvalidArgumentException('Unknown file extension');
        }

        $isValid = false;
        foreach ($validSignatures[$extension] as $sig) {
            if (strpos($signature, $sig) === 0) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            throw new InvalidArgumentException('File signature does not match file type');
        }
    }

    /**
     * Generate secure filename to prevent directory traversal
     */
    private function generateSecureFilename(string $originalName): string
    {
        // Remove path information
        $filename = basename($originalName);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Add timestamp and random string to prevent overwrites
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $random = bin2hex(random_bytes(8));

        return sprintf('%s_%s_%s.%s', $name, time(), $random, $extension);
    }
}





