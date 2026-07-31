<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteRequest;
use App\Models\Website;
use App\Services\CoCalcReceiverService;
use App\Services\StorageService;
use App\Services\ZipValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteController extends Controller
{
    public function __construct(
        private ZipValidationService $zipService,
        private StorageService $storageService,
        private CoCalcReceiverService $cocalcService,
    ) {}

    /**
     * Display the list of the authenticated user's websites.
     */
    public function index()
    {
        $websites = Auth::user()
            ->websites()
            ->latest()
            ->paginate(10);

        return view('websites.index', compact('websites'));
    }

    /**
     * Show the upload form.
     */
    public function create()
    {
        return view('websites.create');
    }

    /**
     * Display detailed view of a specific website.
     */
    public function show(Website $website)
    {
        if ($website->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $deployments = $website->deployments()->latest()->get();
        $visitorLogs = $this->cocalcService->getSiteVisitorLogs($website);

        return view('websites.show', compact('website', 'deployments', 'visitorLogs'));
    }

    /**
     * Endpoint to fetch live deployment & CoCalc visitor HTTP logs via JSON for real-time polling.
     */
    public function logs(Website $website)
    {
        if ($website->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $latestDeployment = $website->deployments()->latest()->first();
        $deploymentLog = $latestDeployment ? $latestDeployment->log_output : '';

        // Fetch live visitor HTTP hits from CoCalc receiver
        $visitorLogs = $this->cocalcService->getSiteVisitorLogs($website);

        $combinedLog = $deploymentLog;
        if (!empty($visitorLogs)) {
            $combinedLog .= "\n\n--- 🌐 LIVE VISITOR TRAFFIC LOGS (COCALC UBUNTU) ---\n";
            $combinedLog .= implode("\n", $visitorLogs);
        }

        return response()->json([
            'status'        => 'success',
            'website'       => $website->name,
            'deployment_id' => $latestDeployment?->uuid,
            'log_output'    => $combinedLog,
            'hit_count'     => count($visitorLogs),
            'updated_at'    => now()->format('H:i:s'),
        ]);
    }

    /**
     * Handle ZIP upload, validate, extract and store metadata.
     */
    public function store(StoreWebsiteRequest $request)
    {
        $user    = Auth::user();
        $siteUuid = (string) Str::uuid();

        // Build site name slug (unique per user: site_{uuid_short})
        $slug = Str::slug($request->input('name')) . '-' . Str::substr($siteUuid, 0, 8);

        // Resolve absolute extraction destination
        $storagePath = $this->storageService->getSiteStoragePath($user->id, $siteUuid);
        $destAbsPath = $this->storageService->absolutePath($storagePath);

        try {
            // --- Validate ZIP & Extract -----------------------------------------------
            $result = $this->zipService->validateAndExtract(
                $request->file('zip_file'),
                $destAbsPath
            );

            // --- Persist Website Record ------------------------------------------------
            $website = Website::create([
                'uuid'              => $siteUuid,
                'user_id'           => $user->id,
                'name'              => $request->input('name'),
                'slug'              => $slug,
                'original_filename' => $request->file('zip_file')->getClientOriginalName(),
                'storage_path'      => $storagePath,
                'public_path'       => null,
                'size_kb'           => $result['totalSizeKb'],
                'status'            => 'ready',
                'live_url'          => null,
            ]);

            return redirect()
                ->route('websites.index')
                ->with('success', "✅ \"{$website->name}\" uploaded successfully with {$result['fileCount']} files ({$result['totalSizeKb']} KB). Ready to deploy!");

        } catch (RuntimeException $e) {
            // Clean up any partially extracted files if the operation failed
            if (is_dir($destAbsPath)) {
                $this->storageService->deleteDirectory($destAbsPath);
            }

            return back()
                ->withInput()
                ->with('error', '❌ Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a website: remove DB record & all associated local/CoCalc storage files.
     */
    public function destroy(Website $website)
    {
        // Authorize: only the owner can delete
        if ($website->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $siteName = $website->name;

        // Always attempt CoCalc remote file deletion (non-fatal if receiver is offline)
        $this->cocalcService->deleteFromCoCalc($website);

        // Delete extracted storage files locally
        $absStoragePath = $this->storageService->absolutePath($website->storage_path);
        if (is_dir($absStoragePath)) {
            $this->storageService->deleteDirectory($absStoragePath);
        }

        // Delete deployed public files locally (only if it's a local path, not a CoCalc path)
        if ($website->public_path && !str_starts_with($website->public_path, 'cocalc://')) {
            $absPublicPath = $this->storageService->absolutePath($website->public_path);
            if (is_dir($absPublicPath)) {
                $this->storageService->deleteDirectory($absPublicPath);
            }
        }

        // Cascade deletes deployments due to DB foreign key constraint
        $website->delete();

        return redirect()
            ->route('websites.index')
            ->with('success', "🗑️ \"{$siteName}\" and all associated files have been permanently deleted.");
    }
}
