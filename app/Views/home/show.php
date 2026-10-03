<?php 
    // Brings in the sidebar and links
    require __DIR__ . "/../components/ui/sidebar.php";
    require __DIR__ . "/../../../config/links.php";

    // Guards against unset errors
    $domains = $data["domains"] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | Home</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="min-h-screen flex bg-slate-50">
    <?php renderSidebar($site_links, "Home"); ?>

    <main class="flex-1 min-w-0 px-4 py-8 md:px-8">
        <!-- Container for the dashboard -->
        <div class="w-full max-w-6xl mx-auto">
            <!-- Grid for the dashboard -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">
                <?php include __DIR__ . "/../components/dashboard/metrics/total_domains.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/total_subdomains.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/total_records.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/active_domains.php" ?>
                <?php include __DIR__ . "/../components/dashboard/metrics/inactive_domains.php" ?>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        lucide.createIcons()
    </script>
    <script src="/assets/js/components/sidebar.js"></script>
    <script src="/assets/js/dashboard/metrics.js"></script>
</body>
</html>