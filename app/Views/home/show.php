<?php 
    // Brings in the sidebar and links
    require __DIR__ . "/../components/sidebar.php";
    require __DIR__ . "/../../../config/links.php";

    // Guards against unset errors
    $domains = $data["domains"] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | Login</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="min-h-screen flex">
    <?php renderSidebar($site_links, "Home"); ?>
    
    <div class="flex-1 flex flex-col items-center bg-slate-50 px-4 py-12">
        <!-- Container for the dashboard -->
        <div class="w-full max-w-6xl">
            <!-- Grid for the dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-5">
                <!-- Displays the metrics on the page -->
            </div>
        </div>
    </div>
    <!-- Javascript links -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        lucide.createIcons()
    </script>
    <script src="/assets/js/components/sidebar.js"></script>
    
</body>
</html>