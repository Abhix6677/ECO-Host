<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalWebsites = $user->websites()->count();
        $activeWebsites = $user->websites()->where('status', 'live')->count();
        $totalSizeKb = $user->websites()->sum('size_kb');
        
        // Format storage display
        if ($totalSizeKb >= 1024) {
            $storageUsed = number_format($totalSizeKb / 1024, 2) . ' MB';
        } else {
            $storageUsed = number_format($totalSizeKb, 0) . ' KB';
        }

        $recentDeploymentsCount = $user->deployments()->count();
        $recentDeployments = $user->deployments()
            ->with('website')
            ->latest()
            ->take(5)
            ->get();

        $recentWebsites = $user->websites()
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalWebsites',
            'activeWebsites',
            'storageUsed',
            'recentDeploymentsCount',
            'recentDeployments',
            'recentWebsites'
        ));
    }
}
