<?php

namespace App\Http\Controllers;

use App\Models\VirustotalScan;
use App\Services\VirusTotalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Controller for the scan
class ScanController extends Controller
{
    // Method to display the dashboard
    public function dashboard(Request $request, VirusTotalService $vt): View|RedirectResponse
    {
        // Gets the session id
        $sessionId = $request->session()->get('recorded_session_id');

        // Checks if there is not a session id
        if (! $sessionId) {
            return redirect()->route('upload.create');
        }

        // Submits the queued and the pending
        $vt->submitQueued();
        $totalPending = $vt->refreshPending((int) $sessionId);

        // Fetching the complete records
        $completeRecords = VirustotalScan::where('session_id', $sessionId)
            ->orderByDesc('created_at')
            ->get();

        // Calculates the metrics
        $totalFiles = VirustotalScan::where('session_id', $sessionId)->count();
        $totalHarmless = (int) VirustotalScan::where('session_id', $sessionId)->sum('harmless');
        $totalSuspicous = (int) VirustotalScan::where('session_id', $sessionId)->sum('suspicious');
        $totalMalicous = (int) VirustotalScan::where('session_id', $sessionId)->sum('malicious');
        $totalUndetected = (int) VirustotalScan::where('session_id', $sessionId)->sum('undetected');

        // Returns the view with the total pending
        return view('dashboard', [
            'totalPending' => $totalPending,
            'complete' => $totalPending === 0,
            'completeRecords' => $completeRecords,
            'totalFiles' => $totalFiles,
            'totalHarmless' => $totalHarmless,
            'totalSuspicous' => $totalSuspicous,
            'totalMalicous' => $totalMalicous,
            'totalUndetected' => $totalUndetected,
        ]);
    }

    // Method which includes json return of the sessions data for the charts to use
    public function json(Request $request): JsonResponse
    {
        $sessionId = $request->session()->get('recorded_session_id');
        if (! $sessionId) {
            return response()->json([]);
        }

        $completeRecords = VirustotalScan::where('session_id', $sessionId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($completeRecords);
    }

    // Method to get the status
    public function status(Request $request, VirusTotalService $vt): JsonResponse
    {
        $sessionId = $request->session()->get('recorded_session_id');
        if (! $sessionId) {
            return response()->json(['all_scans_complete' => true, 'pending' => 0]);
        }

        $vt->submitQueued();
        $pending = $vt->refreshPending((int) $sessionId);

        return response()->json([
            'all_scans_complete' => $pending === 0,
            'pending' => $pending,
        ]);
    }

    // Method to get the index
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->session()->get('recorded_session_id');
        if (! $sessionId) {
            return response()->json([]);
        }

        return response()->json(
            VirustotalScan::where('session_id', $sessionId)
                ->orderByDesc('created_at')
                ->get()
        );
    }
}
