<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CloudflareTunnelService
{
    /**
     * The environment variable name that holds a pre-configured Cloudflare Tunnel base URL.
     * If the user runs `cloudflared tunnel --url http://localhost:8000` and stores the
     * resulting trycloudflare.com URL here, we'll use it directly.
     *
     * Example .env value:  CLOUDFLARE_TUNNEL_URL=https://example-abc.trycloudflare.com
     */
    private const ENV_TUNNEL_URL = 'CLOUDFLARE_TUNNEL_URL';

    /**
     * Generate the publicly accessible URL for a deployed site.
     *
     * Priority order:
     *  1. CLOUDFLARE_TUNNEL_URL env var (user has cloudflared running)
     *  2. APP_URL (local dev fallback — serves via Laravel's built-in web server)
     *
     * The static files for site {uuid} will be accessible at:
     *   {BASE_URL}/sites/{uuid}/
     *
     * Because we copy the extracted files into `storage/app/public/sites/{uuid}/`
     * and Laravel's `storage:link` creates `public/storage -> storage/app/public`,
     * the files are reachable at `{APP_URL}/storage/sites/{uuid}/index.html`
     * — or at the tunnel URL if cloudflared is active.
     */
    public function getLiveUrl(string $siteUuid): string
    {
        $tunnelBase = $this->getTunnelBaseUrl();
        return rtrim($tunnelBase, '/') . '/storage/sites/' . $siteUuid . '/';
    }

    /**
     * Returns the configured Cloudflare Tunnel base URL if set,
     * otherwise falls back to APP_URL for local development.
     */
    public function getTunnelBaseUrl(): string
    {
        $envUrl = env(self::ENV_TUNNEL_URL);

        if (!empty($envUrl) && filter_var($envUrl, FILTER_VALIDATE_URL)) {
            return rtrim($envUrl, '/');
        }

        // Fallback: local development URL
        return rtrim(config('app.url', 'http://localhost:8000'), '/');
    }

    /**
     * Detect whether the `cloudflared` binary is available on the system PATH.
     *
     * Used for displaying the tunnel status in the UI and providing
     * accurate setup instructions when the binary is missing.
     */
    public function isTunnelBinaryAvailable(): bool
    {
        $command = PHP_OS_FAMILY === 'Windows'
            ? 'where cloudflared 2>NUL'
            : 'which cloudflared 2>/dev/null';

        exec($command, $output, $exitCode);
        return $exitCode === 0;
    }

    /**
     * Returns whether a live Cloudflare Tunnel URL is actually configured.
     */
    public function isTunnelConfigured(): bool
    {
        $envUrl = env(self::ENV_TUNNEL_URL);
        return !empty($envUrl) && filter_var($envUrl, FILTER_VALIDATE_URL);
    }
}
