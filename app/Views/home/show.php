<?php 
    // Brings in the sidebar and links
    require __DIR__ . "/../components/ui/sidebar.php";
    require __DIR__ . "/../../../config/links.php";

    $dashboard_data = json_encode([
        "domains" => $data["domains"] ?? [],
        "subdomains" => $data["subdomains"] ?? [],
        "records" => $data["records"] ?? []
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | Home</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="min-h-screen flex overflow-x-clip bg-slate-50">
    <?php renderSidebar($site_links, "Home"); ?>

    <main class="flex-1 min-w-0 px-4 py-4 md:px-6">
        <!-- Container for the dashboard -->
        <div class="w-full max-w-7xl mx-auto">
            <!-- Grid for the dashboard -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <?php include __DIR__ . "/../components/dashboard/metrics/total_domains.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/total_subdomains.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/total_records.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/active_domains.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/inactive_domains.php" ?>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm">
                    <h2 class="text-base font-semibold text-slate-900">Subdomain Status</h2>
                    <div class="relative mt-2 h-56">
                        <canvas id="subdomainStatusChart" role="img" aria-label="Subdomains grouped by status"></canvas>
                    </div>
                    <p id="subdomainStatusEmpty" class="hidden text-sm text-slate-500">No subdomains to chart yet.</p>
                </section>

                <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm">
                    <h2 class="text-base font-semibold text-slate-900">DNS Records by Type</h2>
                    <div class="relative mt-2 h-56">
                        <canvas id="recordTypeChart" role="img" aria-label="DNS records grouped by record type"></canvas>
                    </div>
                    <p id="recordTypeEmpty" class="hidden text-sm text-slate-500">No DNS records to chart yet.</p>
                </section>

                <section class="rounded-lg border border-slate-200/80 bg-white p-4 shadow-sm md:col-span-2 xl:col-span-1">
                    <h2 class="text-base font-semibold text-slate-900">Subdomains by Domain</h2>
                    <div class="relative mt-2 h-56">
                        <canvas id="subdomainsByDomainChart" role="img" aria-label="Subdomain count for each domain"></canvas>
                    </div>
                    <p id="subdomainsByDomainEmpty" class="hidden text-sm text-slate-500">Add a domain to see its subdomain counts.</p>
                </section>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        lucide.createIcons()
    </script>
    <script src="/assets/js/components/sidebar.js"></script>
    <script>window.dashboardData = <?= $dashboard_data ?>;</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
    <script src="/assets/js/dashboard/metrics.js"></script>
</body>
</html>