<?php

namespace App\Services;

use App\Models\Deployment;
use App\Models\Website;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * DeploymentService
 *
 * Handles all website deployments to CoCalc Ubuntu via the HTTP Webhook Receiver.
 *
 * Architecture:
 *   EcoHost (Local) → POST /api/deploy → CoCalc Receiver → Extracts files → Serves via Cloudflare
 *
 * The local Laravel app NEVER hosts the user websites.
 * All websites are stored and served exclusively from CoCalc Ubuntu.
 */
class DeploymentService
{
    public function __construct(
        private StorageService $storageService,
        private CoCalcReceiverService $cocalcService,
    ) {}

    public function deploy(Website $website): Deployment
    {
        $deployment = Deployment::create([
            'uuid'       => (string) Str::uuid(),
            'website_id' => $website->id,
            'user_id'    => $website->user_id,
            'status'     => 'deploying',
            'log_output' => 'Deployment initiated at ' . now()->toDateTimeString(),
        ]);

        $website->update(['status' => 'deploying']);

        try {
            $log = [];
            $log[] = '[' . now()->format('H:i:s') . '] Deployment started: ' . $website->name;
            $log[] = '[' . now()->format('H:i:s') . '] Target: EcoHost Cloud Engine';
            $log[] = '[' . now()->format('H:i:s') . '] Receiver: ' . config('services.cocalc.receiver_url');

            // ----------------------------------------------------------------
            // Step 1: Check local files or existing CoCalc deployment
            // ----------------------------------------------------------------
            $srcPath        = $this->storageService->absolutePath($website->storage_path);
            $hasLocalFiles  = is_dir($srcPath);
            $hasCoCalcFiles = str_starts_with($website->public_path ?? '', 'cocalc://');

            if ($hasLocalFiles) {
                $log[] = '[' . now()->format('H:i:s') . '] Source files verified locally.';
                $log[] = '[' . now()->format('H:i:s') . '] Packaging ZIP and deploying to EcoHost Cloud...';

                $cocalcResult = $this->cocalcService->deployToCoCalc($website);

            } else if ($hasCoCalcFiles) {
                $log[] = '[' . now()->format('H:i:s') . '] Files already hosted on EcoHost Cloud. Syncing Live URL...';

                $health = $this->cocalcService->healthCheck();
                if (!$health['online']) {
                    throw new RuntimeException("CoCalc receiver is offline: " . ($health['error'] ?? 'Connection failed'));
                }

                $cocalcUrl = config('services.cocalc.receiver_url');
                $liveUrl   = rtrim($cocalcUrl, '/') . '/site/' . $website->slug . '/';

                $cocalcResult = [
                    'message'     => 'Synced Live URL with active Cloudflare Tunnel.',
                    'cocalc_path' => '/home/user/websites/public_sites/' . $website->slug,
                    'file_count'  => 1,
                    'live_url'    => $liveUrl,
                ];

            } else {
                throw new RuntimeException("Source files not found at: {$srcPath}. Please re-upload the website ZIP.");
            }

            $log[] = '[' . now()->format('H:i:s') . '] CoCalc Response: ' . ($cocalcResult['message'] ?? 'OK');
            $log[] = '[' . now()->format('H:i:s') . '] CoCalc Storage: '  . ($cocalcResult['cocalc_path'] ?? 'N/A');
            $log[] = '[' . now()->format('H:i:s') . '] Files Deployed: '  . ($cocalcResult['file_count'] ?? 0);

            // ----------------------------------------------------------------
            // Step 3: Get the live Cloudflare URL returned by CoCalc
            // CoCalc builds: {CLOUDFLARE_TUNNEL_URL}/storage/sites/{uuid}/
            // ----------------------------------------------------------------
            $liveUrl = $cocalcResult['live_url'] ?? null;

            if ($liveUrl) {
                $log[] = '[' . now()->format('H:i:s') . '] ✅ Live URL: ' . $liveUrl;
                $finalStatus = 'live';
            } else {
                // Files deployed on CoCalc but cloudflared not running yet
                $warning = $cocalcResult['tunnel_warning'] ?? 'Cloudflare tunnel not configured on CoCalc.';
                $log[] = '[' . now()->format('H:i:s') . '] ⚠️  ' . $warning;
                $log[] = '[' . now()->format('H:i:s') . '] Files are on CoCalc at: ' . ($cocalcResult['cocalc_path'] ?? 'N/A');
                $log[] = '[' . now()->format('H:i:s') . '] Start cloudflared on CoCalc and redeploy to get a live URL.';
                $finalStatus = 'deployed'; // On CoCalc but no public URL yet
            }

            // ----------------------------------------------------------------
            // Step 4: Save result to database
            // ----------------------------------------------------------------
            $deployment->update([
                'status'      => $finalStatus,
                'live_url'    => $liveUrl,
                'log_output'  => implode("\n", $log),
                'deployed_at' => now(),
            ]);

            $website->update([
                'status'      => $finalStatus,
                'live_url'    => $liveUrl,
                'public_path' => 'cocalc://' . ($cocalcResult['cocalc_path'] ?? ''),
            ]);

        } catch (Throwable $e) {
            $errorLog  = isset($log) ? implode("\n", $log) : '';
            $errorLog .= "\n[FATAL] Cannot connect to CoCalc receiver at " . config('services.cocalc.receiver_url') . '. Error: ' . $e->getMessage();
            $errorLog .= "\n[FILE]  " . $e->getFile() . ':' . $e->getLine();

            $deployment->update([
                'status'     => 'failed',
                'log_output' => $errorLog,
            ]);

            $website->update(['status' => 'failed']);
        }

        return $deployment->fresh();
    }
}
