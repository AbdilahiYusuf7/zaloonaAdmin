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
     * Uploads a pre-validated file to the Node backend's image storage (Cloudflare R2) and
     * returns its public URL, or null when no file was submitted. Logos can't live on this
     * container's local disk — it's wiped on every redeploy.
     *
     * @param array<string, mixed>|null $file
     */
    public function store(?array $file): ?string
    {
        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $mimeType = mime_content_type($file['tmp_name']) ?: '';

        if (!isset(self::EXTENSIONS_BY_MIME[$mimeType])) {
            throw new RuntimeException('Unsupported logo file type.');
        }

        $curlFile = new CURLFile($file['tmp_name'], $mimeType, $file['name']);
        $response = $this->callNodeApi('/api/internal/images', [
            'image' => $curlFile,
            'subfolder' => 'salons',
        ]);

        if (!isset($response['url'])) {
            throw new RuntimeException('Could not save the uploaded logo.');
        }

        return $response['url'];
    }

    public function delete(?string $storedPath): void
    {
        if ($storedPath === null) {
            return;
        }

        // Older rows store a bare relative path from before uploads moved to R2; nothing to clean up there.
        if (!str_starts_with($storedPath, 'http://') && !str_starts_with($storedPath, 'https://')) {
            return;
        }

        try {
            $this->callNodeApi('/api/internal/images/delete', json_encode(['url' => $storedPath]), true);
        } catch (Throwable) {
            // Best-effort cleanup; a failed delete just leaves an orphaned object in R2.
        }
    }

    /** @param array<string, mixed>|string $body */
    private function callNodeApi(string $path, array|string $body, bool $isJson = false): array
    {
        $url = rtrim((string) env('NODE_API_URL', ''), '/') . $path;
        $key = env('NODE_INTERNAL_API_KEY', '');

        if ($url === '' || $key === '') {
            throw new RuntimeException('Image storage is not configured.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array_filter([
                'X-Internal-Key: ' . $key,
                $isJson ? 'Content-Type: application/json' : null,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
        ]);

        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Could not reach image storage: ' . $error);
        }
        if ($status >= 400) {
            throw new RuntimeException('Image storage request failed with status ' . $status);
        }

        $decoded = $raw !== '' ? json_decode($raw, true) : [];
        return is_array($decoded) ? $decoded : [];
    }
}
