<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Virus Total Upload</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center px-4 py-12">

    <!-- Spinner shown while scans are still pending -->
    <div id="spinner" class="{{ $complete ? 'hidden' : 'flex' }}  flex-col items-center gap-3">
        <div class="w-10 h-10 border-4 border-slate-200 border-t-indigo-600 rounded-full animate-spin"></div>
        <p class="text-sm text-slate-500">
            Waiting on {{ $totalPending }} scan{{ $totalPending === 1 ? '' : 's' }}...
        </p>
    </div>

    <!-- Content shown once everything has finished processing -->
    <div id="content" class="w-full max-w-6xl {{ $complete ? '' : 'hidden' }}">
        <!-- Scan results will go here -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-5">
        <!-- Displays the metrics on the page -->
        <x-dashboard.metrics.total-files :numOfFiles="$totalFiles" />
        <x-dashboard.metrics.total-malicous :malicous="$totalMalicous" />
        <x-dashboard.metrics.total-suspicous :suspicous="$totalSuspicous" />
        <x-dashboard.metrics.total-harmless :harmless="$totalHarmless" />
        <x-dashboard.metrics.total-undetected :undetected="totalUndetected" />

        <!-- Session data table-->
        <div class="md:col-span-3 flex flex-col h-full">
            <x-dashboard.charts.session-table :data="$completeRecords" />
        </div>

        <!-- Chart to show points per category -->
        <x-dashboard.charts.category-chart />

        <!-- Stacked bar chart to show points per file -->
        <div class="md:col-span-5 rounded-xl bg-white p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 mb-4">
                <p class="text-sm font-medium text-slate-600">Points breakdown per file</p>
            </div>

            <div class="relative h-80 w-full max-w-3xl mx-auto">
                <canvas id="scanStacked"></canvas>
            </div>
        </div>

    <!-- Button to allow users to start a new scan -->
    <div class="md:col-span-5 flex flex-col">
        <a 
            href="/upload"
            class="text-center w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors cursor-pointer"
        >
            Start new scan
        </a>
    </div>
</div>
    </div>

    <script>
        // Runs the check, then reads the updated session flag
        async function pollForCompletion() {
            const res = await fetch(`{{ route('scans.status') }}`);
            const data = await res.json();

            if (data.all_scans_complete) {
                window.location.reload();
            } else {
                setTimeout(pollForCompletion, 5000);
            }
        }

        if (!document.getElementById('spinner').classList.contains('hidden')) {
            pollForCompletion();
        }
    </script>

    <!-- Brings in handlers -->
    <script src="/js/dashboard/charts/categoryChart.js"></script>
    <script src="/js/dashboard/charts/perFileChart.js"></script>
</body>
</html>