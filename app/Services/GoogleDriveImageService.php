<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoogleDriveImageService
{
    private const MAX_BYTES = 4 * 1024 * 1024;

    /**
     * Unduh gambar dari tautan share Google Drive tanpa membutuhkan OAuth.
     *
     * @return array{contents: string, extension: string}
     *
     * @throws ValidationException
     */
    public function download(string $linkOrId): array
    {
        $fileId = $this->extractFileId($linkOrId);

        if ($fileId === null) {
            throw ValidationException::withMessages([
                'drive_link' => 'Tautan Google Drive tidak valid. Tempel link share file atau ID file Drive.',
            ]);
        }

        $urls = [
            'https://drive.usercontent.google.com/download?id='.$fileId.'&export=download&authuser=0',
            'https://drive.google.com/uc?export=download&id='.$fileId,
        ];

        $lastMessage = 'Gagal mengunduh file dari Google Drive.';

        foreach ($urls as $url) {
            try {
                $response = $this->request($url);
            } catch (Throwable) {
                $lastMessage = 'Tidak dapat terhubung ke Google Drive. Periksa koneksi internet lalu coba lagi.';

                continue;
            }

            if (! $response->successful()) {
                $lastMessage = 'File Google Drive tidak dapat diakses. Pastikan link dibagikan ke "Anyone with the link".';

                continue;
            }

            $contents = $response->body();

            if ($contents === '' || strlen($contents) > self::MAX_BYTES) {
                $lastMessage = 'Ukuran foto dari Google Drive maksimal 4 MB.';

                continue;
            }

            $extension = $this->detectImageExtension($contents);

            if ($extension === null) {
                $lastMessage = 'File Google Drive bukan gambar JPG, PNG, atau WEBP, atau link belum dapat diakses publik.';

                continue;
            }

            return [
                'contents' => $contents,
                'extension' => $extension,
            ];
        }

        throw ValidationException::withMessages([
            'drive_link' => $lastMessage,
        ]);
    }

    private function request(string $url): Response
    {
        $request = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
        ])
            ->connectTimeout(5)
            ->timeout(20);

        $caBundle = $this->caBundle();

        if ($caBundle !== null) {
            $request = $request->withOptions(['verify' => $caBundle]);
        }

        return $request->get($url);
    }

    private function extractFileId(string $linkOrId): ?string
    {
        $value = trim($linkOrId);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9_-]{20,}$/', $value) === 1) {
            return $value;
        }

        $host = strtolower((string) parse_url($value, PHP_URL_HOST));

        if ($host !== '' && ! in_array($host, [
            'drive.google.com',
            'drive.usercontent.google.com',
            'docs.google.com',
        ], true)) {
            return null;
        }

        $patterns = [
            '/drive\.google\.com\/file\/d\/([A-Za-z0-9_-]+)/i',
            '/drive\.usercontent\.google\.com\/download\?[^#]*\bid=([A-Za-z0-9_-]+)/i',
            '/drive\.google\.com\/(?:open|uc)\?[^#]*\bid=([A-Za-z0-9_-]+)/i',
            '/docs\.google\.com\/[^\/]+\/d\/([A-Za-z0-9_-]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches) === 1) {
                return $matches[1];
            }
        }

        if (preg_match('/[?&]id=([A-Za-z0-9_-]+)/', $value, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function detectImageExtension(string $contents): ?string
    {
        $info = @getimagesizefromstring($contents);

        return match ($info['mime'] ?? null) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
    }

    private function caBundle(): ?string
    {
        $candidates = [
            config('services.midtrans.ca_bundle'),
            storage_path('certs/cacert.pem'),
            'C:\\laragon\\etc\\ssl\\cacert.pem',
            '/etc/ssl/certs/ca-certificates.crt',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
