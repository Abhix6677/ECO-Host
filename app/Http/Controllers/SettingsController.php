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
}
