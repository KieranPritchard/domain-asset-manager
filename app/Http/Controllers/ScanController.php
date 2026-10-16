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
            // Gets the session id
            $sessionId = $request->session()->get('recorded_session_id');
            
            // Checks if there is not a session id
            if (! $sessionId) {
                return redirect()->route('upload.create');
            }

            // Submits the queued and the pending
            $vt->submitQueued();
            $totalPending = $vt->refreshPending($sessionId);

            // Fetching the complete records
            $completeRecords = VirustotalScan::where('session_id', $request->session()->get('recorded_session_id'))
                ->orderByDesc('created_at')
                ->get();
            
            // Calculates the metrics
            $totalFiles = VirustotalScan::where('session_id', $request->session()->get('recorded_session_id'))->count();
            $totalHarmless = VirustotalScan::where('session_id', $request->session()->get('recorded_session_id'))->sum('harmless');
            $totalSuspicous = VirustotalScan::where('session_id', $request->session()->get('recorded_session_id'))->sum('suspicious');
            $totalMalicous = VirustotalScan::where('session_id', $request->session()->get('recorded_session_id'))->sum('malicious');
            $totalUndetected = VirustotalScan::where('session_id', $request->session()->get('recorded_session_id'))->sum('undetected');

            // Returns the view with the total pending
            return view('scans.dashboard', [
                'totalPending' => $totalPending,
                'complete'     => $totalPending === 0,
                'completeRecords'=> $completeRecords,
                // Handles the metrics
                'totalFiles'=> $totalFiles,
                'totalHarmless'=> $totalHarmless,
                'totalSuspicous'=> $totalSuspicous,
                'totalMalicous' => $totalMalicous,
                'totalUndetected'=> $totalUndetected,

            ]);
        }

        //TODO method which includes json return of the sessions data for the charts to use

        // Method to get the status
        public function status(Request $request, VirusTotalService $vt)
        {
            // Gets the session and checks if the session is a valid id
            $sessionId = $request->session()->get('recorded_session_id');
            if (! $sessionId) {
                return response()->json(['all_scans_complete' => true, 'pending' => 0]);
            }
            
            // Runs the same things as the other method
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
