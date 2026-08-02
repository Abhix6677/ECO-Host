<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Website;
use App\Services\CloudflareTunnelService;
use App\Services\DeploymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeploymentController extends Controller
{
    public function __construct(
        private DeploymentService $deploymentService,
        private CloudflareTunnelService $tunnelService,
        private \App\Services\CoCalcReceiverService $cocalcService,
    ) {}

    /**
     * Show all deployments for the authenticated user.
     */
    public function index()
    {
        $deployments = Auth::user()
            ->deployments()
            ->with('website')
            ->latest()
            ->paginate(15);

        $nodeHealth = $this->cocalcService->healthCheck();
        $isTunnelConfigured = $nodeHealth['online'];

        return view('deployments.index', compact('deployments', 'isTunnelConfigured'));
    }

    /**
     * Trigger deployment (or re-deployment) for a website.
     * Handles both initial deploy and redeploy.
     */
    public function deploy(Website $website)
    {
        // Authorize: only the owner can deploy their site
        if ($website->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Reject if a deployment is already in progress
        if ($website->status === 'deploying') {
            return back()->with('error', 'A deployment is already in progress for this website. Please wait.');
        }

        // Reject if the website has never been uploaded (no storage path and not on CoCalc)
        $hasLocalFiles  = $website->storage_path && is_dir(storage_path('app/' . $website->storage_path));
        $hasCoCalcFiles = str_starts_with($website->public_path ?? '', 'cocalc://');

        if (!$hasLocalFiles && !$hasCoCalcFiles) {
            return back()->with('error', 'Website source files are missing. Please re-upload the website.');
        }

        $deployment = $this->deploymentService->deploy($website);

        if ($deployment->status === 'live') {
            return redirect()
                ->route('websites.index')
                ->with('success', "🚀 \"{$website->name}\" is now live! <a href=\"{$deployment->live_url}\" target=\"_blank\" class=\"underline font-bold\">Open Live URL →</a>");
        }

        if ($deployment->status === 'deployed') {
            return redirect()
                ->route('websites.show', $website)
                ->with('warning', "📦 \"{$website->name}\" was deployed to CoCalc Ubuntu but no Cloudflare tunnel is active yet. Start cloudflared on CoCalc and redeploy.");
        }

        return redirect()
            ->route('websites.show', $website)
            ->with('error', "❌ Deployment failed for \"{$website->name}\". Check the deployment logs for details.");
    }
}
