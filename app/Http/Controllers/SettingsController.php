<?php

namespace App\Http\Controllers;

use App\Services\CoCalcReceiverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function __construct(
        private CoCalcReceiverService $cocalcService
    ) {}

    /**
     * Display the Settings & System Configuration page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // System health check for remote node
        $nodeHealth = $this->cocalcService->healthCheck();

        $nodeInfo = [
            'url'           => config('services.cocalc.receiver_url'),
            'online'        => $nodeHealth['online'],
            'storage_limit' => '150 MB per ZIP',
            'max_files'     => '2,000 files per archive',
            'environment'   => 'EcoHost 24/7 Cloud Engine',
            'token_masked'  => 'ecohost_***_' . substr(config('services.cocalc.secret_key', '2026'), -4),
        ];

        return view('settings.index', compact('user', 'nodeInfo'));
    }

    /**
     * Update user profile information (Name, Email).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile information updated successfully!');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Update EcoHost Cloud Node Receiver URL in .env
     */
    public function updateNodeUrl(Request $request)
    {
        $validated = $request->validate([
            'receiver_url' => ['required', 'url'],
        ]);

        $url = rtrim($validated['receiver_url'], '/');

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (preg_match('/^COCALC_RECEIVER_URL=.*$/m', $envContent)) {
                $envContent = preg_replace('/^COCALC_RECEIVER_URL=.*$/m', 'COCALC_RECEIVER_URL=' . $url, $envContent);
            } else {
                $envContent .= "\nCOCALC_RECEIVER_URL=" . $url . "\n";
            }
            file_put_contents($envPath, $envContent);
        }

        return back()->with('success', 'EcoHost Cloud Engine Node URL updated successfully to: ' . $url);
    }

    /**
     * Auto-registration endpoint called by CoCalc deploy_receiver.py
     * when it boots up with a new Cloudflare URL.
     */
    public function registerNode(Request $request)
    {
        $secret = $request->input('secret');
        $expectedSecret = config('services.cocalc.secret_key', 'ecohost_cocalc_secret_key_2026');

        if ($secret !== $expectedSecret) {
            return response()->json(['status' => 'error', 'message' => 'Invalid secret key'], 401);
        }

        $url = rtrim($request->input('url', ''), '/');
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid URL provided'], 422);
        }

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (preg_match('/^COCALC_RECEIVER_URL=.*$/m', $envContent)) {
                $envContent = preg_replace('/^COCALC_RECEIVER_URL=.*$/m', 'COCALC_RECEIVER_URL=' . $url, $envContent);
            } else {
                $envContent .= "\nCOCALC_RECEIVER_URL=" . $url . "\n";
            }
            file_put_contents($envPath, $envContent);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'EcoHost Node URL registered successfully',
            'url'     => $url
        ]);
    }
}
