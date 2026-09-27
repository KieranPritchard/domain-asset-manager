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
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-12">
    <?php renderSidebar($site_links, "Home"); ?>
</body>
</html>