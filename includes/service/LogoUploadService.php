<?php

declare(strict_types=1);

/** Validates and stores a single uploaded logo image. Used by both admin-entered and self-registered salons. */
final class LogoUploadService
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    /** Allowed logo types, keyed by the actual file content (never the client-supplied name/type) to its stored extension. */
    private const EXTENSIONS_BY_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /** @param array<string, mixed>|null $file a single $_FILES entry */
    public function validate(?array $file): ?string
    {
        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'The logo failed to upload. Please try again.';
        }

        if ($file['size'] > self::MAX_BYTES) {
            return 'Logo must be smaller than 2MB.';
        }

        $mimeType = mime_content_type($file['tmp_name']) ?: '';

        if (!isset(self::EXTENSIONS_BY_MIME[$mimeType])) {
            return 'Logo must be a JPG, PNG, or WEBP image.';
        }

        return null;
    }

    /**
     * Stores a pre-validated file under uploads/salons and returns its stored relative path,
     * or null when no file was submitted.
     *
     * @param array<string, mixed>|null $file
     */
    public function store(?array $file): ?string
    {
        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $mimeType = mime_content_type($file['tmp_name']) ?: '';
        $extension = self::EXTENSIONS_BY_MIME[$mimeType] ?? null;

        if ($extension === null) {
            throw new RuntimeException('Unsupported logo file type.');
        }

        $directory = UPLOAD_ROOT . '/salons';

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Could not prepare the upload directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            throw new RuntimeException('Could not save the uploaded logo.');
        }

        return 'salons/' . $filename;
    }

    public function delete(?string $storedPath): void
    {
        if ($storedPath !== null) {
            @unlink(UPLOAD_ROOT . '/' . $storedPath);
        }
    }
}
