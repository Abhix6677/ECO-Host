<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteRequest;
use App\Models\Website;
use App\Services\CoCalcReceiverService;
use App\Services\GitHubImportService;
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
        private GitHubImportService $githubService,
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
     * Show the upload & GitHub import form.
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
     * Endpoint to fetch live deployment & EcoHost visitor HTTP logs via JSON for real-time polling.
     */
    public function logs(Website $website)
    {
        if ($website->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $latestDeployment = $website->deployments()->latest()->first();
        $deploymentLog = $latestDeployment ? $latestDeployment->log_output : '';

        // Fetch live visitor HTTP hits from receiver
        $visitorLogs = $this->cocalcService->getSiteVisitorLogs($website);

        $combinedLog = $deploymentLog;
        if (!empty($visitorLogs)) {
            $combinedLog .= "\n\n--- 🌐 LIVE VISITOR TRAFFIC LOGS (ECOHOST CLOUD) ---\n";
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
     * Handle ZIP upload OR GitHub repo import, validate, extract and store metadata.
     */
    public function store(StoreWebsiteRequest $request)
    {
        $user     = Auth::user();
        $siteUuid = (string) Str::uuid();

        // Build site name slug (unique per user: site_{uuid_short})
        $slug = Str::slug($request->input('name')) . '-' . Str::substr($siteUuid, 0, 8);

        // Resolve absolute extraction destination
        $storagePath = $this->storageService->getSiteStoragePath($user->id, $siteUuid);
        $destAbsPath = $this->storageService->absolutePath($storagePath);

        $tempUploadedZip = null;

        try {
            // --- Determine Source: GitHub Repo URL OR File Upload ----------------------
            if ($request->filled('github_url')) {
                $importRes = $this->githubService->downloadGitHubRepoZip($request->input('github_url'));
                $zipFile          = $importRes['file'];
                $originalFilename = "github:{$importRes['owner']}/{$importRes['repo']} ({$importRes['branch']})";
                $tempUploadedZip  = $zipFile->getRealPath();
            } else {
                $zipFile          = $request->file('zip_file');
                $originalFilename = $zipFile->getClientOriginalName();
            }

            // --- Validate ZIP & Extract -----------------------------------------------
            $result = $this->zipService->validateAndExtract(
                $zipFile,
                $destAbsPath
            );

            // --- Persist Website Record ------------------------------------------------
            $website = Website::create([
                'uuid'              => $siteUuid,
                'user_id'           => $user->id,
                'name'              => $request->input('name'),
                'slug'              => $slug,
                'original_filename' => $originalFilename,
                'storage_path'      => $storagePath,
                'public_path'       => null,
                'size_kb'           => $result['totalSizeKb'],
                'status'            => 'ready',
                'live_url'          => null,
            ]);

            // Clean up temporary downloaded file if GitHub import was used
            if ($tempUploadedZip && file_exists($tempUploadedZip)) {
                @unlink($tempUploadedZip);
            }

            $sourceLabel = $request->filled('github_url') ? 'imported from GitHub' : 'uploaded';

            return redirect()
                ->route('websites.index')
                ->with('success', "✅ \"{$website->name}\" {$sourceLabel} successfully with {$result['fileCount']} files ({$result['totalSizeKb']} KB). Ready to deploy!");

        } catch (RuntimeException $e) {
            // Clean up any partially extracted files or downloaded zip if operation failed
            if (is_dir($destAbsPath)) {
                $this->storageService->deleteDirectory($destAbsPath);
            }
            if ($tempUploadedZip && file_exists($tempUploadedZip)) {
                @unlink($tempUploadedZip);
            }

            return back()
                ->withInput()
                ->with('error', '❌ Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a website: remove DB record & all associated remote storage files.
     */
    public function destroy(Website $website)
    {
        if ($website->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $siteName = $website->name;

        // Always attempt remote file deletion (non-fatal if receiver is offline)
        $this->cocalcService->deleteFromCoCalc($website);

        // Delete local source directory if exists
        if ($website->storage_path) {
            $absStoragePath = $this->storageService->absolutePath($website->storage_path);
            if (is_dir($absStoragePath)) {
                $this->storageService->deleteDirectory($absStoragePath);
            }
        }

        $website->delete();

        return redirect()
            ->route('websites.index')
            ->with('success', "🗑️ \"{$siteName}\" was successfully deleted.");
    }
}
