<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use ZipArchive;

/**
 * CoCalcReceiverService
 *
 * Communicates with the CoCalc Ubuntu Webhook Receiver (deploy_receiver.py).
 *
 * Uses multipart/form-data for ZIP transfer (not base64) because:
 *  - 33% smaller payload — no base64 encoding overhead
 *  - Streams binary data directly without encoding/decoding
 *  - Standard HTTP file upload format
 */
class CoCalcReceiverService
{
    private string $receiverUrl;
    private string $secretToken;

    public function __construct()
    {
        $this->receiverUrl = rtrim(config('services.cocalc.receiver_url', 'http://localhost:9000'), '/');
        $this->secretToken = config('services.cocalc.secret_key', 'ecohost_cocalc_secret_key_2026');
    }

    /**
     * Health check — verify the CoCalc receiver is reachable.
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->receiverUrl}/health");

            return $response->successful()
                ? ['online' => true,  'data'  => $response->json()]
                : ['online' => false, 'error' => "HTTP {$response->status()}"];

        } catch (\Throwable $e) {
            return ['online' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Package the website files into a ZIP and send them to CoCalc Ubuntu
     * via multipart/form-data POST to /api/deploy.
     *
     * @throws RuntimeException on connection failure or non-success response
     */
    public function deployToCoCalc(Website $website): array
    {
        $srcPath = storage_path('app/' . $website->storage_path);

        if (!is_dir($srcPath)) {
            throw new RuntimeException("Source files not found at: {$srcPath}");
        }

        // Build a temporary ZIP of the extracted site files
        $tempZip = tempnam(sys_get_temp_dir(), 'ecohost_deploy_') . '.zip';

        try {
            $this->packageDirectory($srcPath, $tempZip);

            $zipSize = filesize($tempZip);
            if ($zipSize === 0) {
                throw new RuntimeException("Packaged ZIP is empty — no files found in source directory.");
            }

            // Send as multipart/form-data
            // Laravel's attach() sets Content-Type to multipart/form-data automatically
            $response = Http::withHeaders([
                    'X-EcoHost-Token' => $this->secretToken,
                    'Accept'          => 'application/json',
                ])
                ->timeout(120)  // large sites can take a while
                ->attach('zip_file', file_get_contents($tempZip), 'site.zip')
                ->post("{$this->receiverUrl}/api/deploy", [
                    'user_id'   => (string) $website->user_id,
                    'site_uuid' => (string) $website->uuid,
                ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException(
                "Cannot connect to CoCalc receiver at {$this->receiverUrl}. " .
                "Ensure deploy_receiver.py is running on CoCalc and the URL in COCALC_RECEIVER_URL is correct."
            );
        } finally {
            @unlink($tempZip);
        }

        if ($response->failed()) {
            $msg = $response->json('message') ?? ("HTTP {$response->status()}: " . $response->body());
            throw new RuntimeException("CoCalc receiver error: {$msg}");
        }

        $result = $response->json();

        if (($result['status'] ?? '') !== 'success') {
            throw new RuntimeException("CoCalc deployment failed: " . ($result['message'] ?? 'Unknown error.'));
        }

        return $result;
    }

    /**
     * Request CoCalc to delete all files for a given website.
     */
    public function deleteFromCoCalc(Website $website): bool
    {
        try {
            $response = Http::withHeaders([
                    'X-EcoHost-Token' => $this->secretToken,
                    'Accept'          => 'application/json',
                ])
                ->timeout(15)
                ->delete("{$this->receiverUrl}/api/site/{$website->uuid}");

            if ($response->successful()) {
                Log::info("CoCalc: deleted remote files for site {$website->uuid}");
                return true;
            }

            Log::warning("CoCalc: delete returned HTTP {$response->status()} for site {$website->uuid}");
            return false;

        } catch (\Throwable $e) {
            Log::warning("CoCalc: delete request failed for site {$website->uuid}: {$e->getMessage()}");
            return false; // Non-fatal — local DB cleanup still proceeds
        }
    }

    /**
     * Recursively package a directory into a ZIP archive.
     */
    private function packageDirectory(string $srcDir, string $destZip): void
    {
        $zip = new ZipArchive();

        if ($zip->open($destZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Cannot create temporary ZIP at: {$destZip}");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $realPath     = $file->getRealPath();
                $relativePath = substr($realPath, strlen(rtrim($srcDir, '/\\')) + 1);
                // Normalize directory separator to forward slash inside ZIP
                $relativePath = str_replace('\\', '/', $relativePath);
                $zip->addFile($realPath, $relativePath);
            }
        }

        $zip->close();
    }
}
