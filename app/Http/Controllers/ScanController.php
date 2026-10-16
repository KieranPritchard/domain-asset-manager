<?php
    namespace App\Http\Controllers;

    use App\Models\VirustotalScan;
    use App\Services\VirusTotalService;
    use Illuminate\Http\Request;

    // Controller for the scan
    class ScanController extends Controller
    {
        // Method to display the dashboard
        public function dashboard(Request $request, VirusTotalService $vt)
        {
            $sessionId = $request->session()->get('recorded_session_id');
            if (! $sessionId) {
                return redirect()->route('upload.create');
            }

            $vt->submitQueued();
            $totalPending = $vt->refreshPending($sessionId);

            return view('scans.dashboard', [
                'totalPending' => $totalPending,
                'complete'     => $totalPending === 0,
            ]);
        }

        // Method to get the status
        public function status(Request $request, VirusTotalService $vt)
        {
            $sessionId = $request->session()->get('recorded_session_id');
            if (! $sessionId) {
                return response()->json(['all_scans_complete' => true, 'pending' => 0]);
            }

            $vt->submitQueued();
            $pending = $vt->refreshPending($sessionId);

            return response()->json([
                'all_scans_complete' => $pending === 0,
                'pending'            => $pending,
            ]);
        }
        // Method to get the index
        public function index(Request $request)
        {
            return VirustotalScan::where('session_id', $request->session()->get('recorded_session_id'))
                ->orderByDesc('created_at')
                ->get();
        }
    }
