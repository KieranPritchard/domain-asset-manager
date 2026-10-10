<?php 
    // Brings in the sidebar and links
    require __DIR__ . "/../components/ui/sidebar.php";
    require __DIR__ . "/../../../config/links.php";
    require __DIR__ . "/../components/ui/modal.php";

    // Guards against unset errors
    $domains = $data["domains"] ?? null;
    $feedback = $data["feedback"] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | Domains</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="min-h-screen flex overflow-x-clip bg-ocean-deep-50">
    <?php renderSidebar($site_links, "Domains"); ?>

    <main class="flex-1 min-w-0 px-4 py-8 md:px-8">
        <!-- Container for the dashboard -->
        <div class="w-full max-w-6xl mx-auto">
            <!-- Flexbox for the table -->
            <div class="overflow-x-auto rounded-lg border border-ocean-deep-200 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-ocean-deep-100 text-xs uppercase tracking-wider text-ocean-deep-700">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">ID</th>
                            <th scope="col" class="px-4 py-3 font-medium">Domain</th>
                            <th scope="col" class="px-4 py-3 font-medium">Registrar</th>
                            <th scope="col" class="px-4 py-3 font-medium">Created At</th>
                            <th scope="col" class="px-4 py-3 font-medium">Updated At</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ocean-deep-100 text-ocean-deep-800">
                        <?php if (!empty($domains)): ?>
                            <?php foreach ($domains as $domain): ?>
                                <tr class="transition-colors hover:bg-ocean-deep-50">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-ocean-deep-950">
                                        <?= htmlspecialchars($domain["id"] ?? "") ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-ocean-deep-950">
                                        <?= htmlspecialchars($domain["name"] ?? "") ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($domain["registrar"] ?? "-") ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($domain["created_at"] ?? "-") ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($domain["updated_at"] ?? "-") ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            data-id="<?= htmlspecialchars($domain["id"] ?? "") ?>"
                                            data-name="<?= htmlspecialchars($domain["name"] ?? "") ?>"
                                            data-registrar="<?= htmlspecialchars($domain["registrar"] ?? "") ?>"
                                            onclick="openEditDomainModal(this)"
                                            class="font-medium text-ocean-deep-500 hover:text-ocean-deep-700"
                                        >
                                            <i width="20" height="20" class="shrink-0" data-lucide="square-pen"></i>
                                        </button>
                                        <button
                                            type="button"
                                            data-id="<?= htmlspecialchars($domain["id"] ?? "") ?>"
                                            data-name="<?= htmlspecialchars($domain["name"] ?? "") ?>"
                                            onclick="openDeleteDomainModal(this)"
                                            class="font-medium text-ocean-deep-500 hover:text-ocean-deep-700"
                                        >
                                            <i width="20" height="20" class="shrink-0" data-lucide="trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-ocean-deep-400">
                                    No domains found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Placeholder button, to be wired up later -->
            <div class="mt-4 w-full">
                <button 
                    type="button" 
                    onclick="openModal('addDomainModal')"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-ocean-deep-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-ocean-deep-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-ocean-deep-400 focus-visible:ring-offset-2"
                >
                    Add Domain
                </button>
            </div>
        </div>

        <!-- Renders the add domain model -->
        <?php include __DIR__ . "/partials/add_domain_modal.php"?>
        <?php include __DIR__ . "/partials/edit_domain_modal.php"?>
        <?php include __DIR__ . "/partials/delete_domain_modal.php"?>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        lucide.createIcons()
    </script>
    <script src="/assets/js/components/sidebar.js"></script>
    <script src="/assets/js/components/modal.js"></script>
    <script src="/assets/js/forms/domains/add_domain/addDomain.js"></script>
    <script src="/assets/js/forms/domains/edit_domain/editDomain.js"></script>
    <script src="/assets/js/forms/domains/delete_domain/deleteDomain.js"></script>
</body>
</html>