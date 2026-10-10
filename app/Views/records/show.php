<?php 
    // Brings in the sidebar and links
    require __DIR__ . "/../components/ui/sidebar.php";
    require __DIR__ . "/../../../config/links.php";
    require __DIR__ . "/../components/ui/modal.php";

    // Guards against unset errors
    $records = $data["records"] ?? null;
    $subdomains = $data["subdomains"] ?? [];
    $feedback = $data["feedback"] ?? null;

    // Maps subdomain ids to FQDNs so the table can show a name instead of an id
    $subdomain_names = [];

    foreach ($subdomains as $subdomain) {
        $subdomain_names[(int) $subdomain["id"]] = $subdomain["fqdn"] ?? "";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | Records</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="min-h-screen flex overflow-x-clip bg-ocean-deep-50">
    <?php renderSidebar($site_links, "DNS Records"); ?>

    <main class="flex-1 min-w-0 px-4 py-8 md:px-8">
        <!-- Container for the dashboard -->
        <div class="w-full max-w-6xl mx-auto">
            <!-- Feedback from the last action -->
            <?php if (!empty($feedback)): ?>
                <div class="mb-4 rounded-lg border border-ocean-deep-200 bg-white px-4 py-3 text-sm text-ocean-deep-800 shadow-sm">
                    <?= htmlspecialchars($feedback) ?>
                </div>
            <?php endif; ?>

            <!-- Flexbox for the table -->
            <div class="overflow-x-auto rounded-lg border border-ocean-deep-200 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-ocean-deep-100 text-xs uppercase tracking-wider text-ocean-deep-700">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">ID</th>
                            <th scope="col" class="px-4 py-3 font-medium">Subdomain</th>
                            <th scope="col" class="px-4 py-3 font-medium">Type</th>
                            <th scope="col" class="px-4 py-3 font-medium">Value</th>
                            <th scope="col" class="px-4 py-3 font-medium">TTL</th>
                            <th scope="col" class="px-4 py-3 font-medium">Priority</th>
                            <th scope="col" class="px-4 py-3 font-medium">Last Verified</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ocean-deep-100 text-ocean-deep-800">
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $record): ?>
                                <tr class="transition-colors hover:bg-ocean-deep-50">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-ocean-deep-950">
                                        <?= htmlspecialchars((string) ($record["id"] ?? "")) ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($subdomain_names[(int) ($record["subdomain_id"] ?? 0)] ?? "-") ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-ocean-deep-950">
                                        <?= htmlspecialchars($record["record_type"] ?? "-") ?>
                                    </td>
                                    <td class="max-w-md break-all px-4 py-3">
                                        <?= htmlspecialchars($record["value"] ?? "-") ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <?= htmlspecialchars((string) ($record["ttl"] ?? "-")) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <?= htmlspecialchars((string) ($record["priority"] ?? "-")) ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <?= htmlspecialchars($record["last_verified"] ?? "-") ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            data-id="<?= htmlspecialchars((string) ($record["id"] ?? "")) ?>"
                                            data-subdomain-id="<?= htmlspecialchars((string) ($record["subdomain_id"] ?? "")) ?>"
                                            data-type="<?= htmlspecialchars($record["record_type"] ?? "") ?>"
                                            data-value="<?= htmlspecialchars($record["value"] ?? "") ?>"
                                            data-ttl="<?= htmlspecialchars((string) ($record["ttl"] ?? "")) ?>"
                                            data-priority="<?= htmlspecialchars((string) ($record["priority"] ?? "")) ?>"
                                            onclick="openEditRecordModal(this)"
                                            class="font-medium text-ocean-deep-500 hover:text-ocean-deep-700"
                                        >
                                            <i width="20" height="20" class="shrink-0" data-lucide="square-pen"></i>
                                        </button>
                                        <button
                                            type="button"
                                            data-id="<?= htmlspecialchars((string) ($record["id"] ?? "")) ?>"
                                            data-type="<?= htmlspecialchars($record["record_type"] ?? "") ?>"
                                            data-value="<?= htmlspecialchars($record["value"] ?? "") ?>"
                                            onclick="openDeleteRecordModal(this)"
                                            class="font-medium text-ocean-deep-500 hover:text-ocean-deep-700"
                                        >
                                            <i width="20" height="20" class="shrink-0" data-lucide="trash-2"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-ocean-deep-400">
                                    No records found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Opens the add record modal -->
            <div class="mt-4 w-full">
                <button 
                    type="button" 
                    onclick="openModal('addRecordModal')"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-ocean-deep-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-ocean-deep-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-ocean-deep-400 focus-visible:ring-offset-2"
                >
                    Add Record
                </button>
            </div>
        </div>

        <!-- Renders the record modals -->
        <?php include __DIR__ . "/partials/add_record_modal.php"?>
        <?php include __DIR__ . "/partials/edit_record_modal.php"?>
        <?php include __DIR__ . "/partials/delete_record_modal.php"?>
    </main>

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        lucide.createIcons()
    </script>
    <script src="/assets/js/components/sidebar.js"></script>
    <script src="/assets/js/components/modal.js"></script>
    <script src="/assets/js/forms/records/recordValidation.js"></script>
    <script src="/assets/js/forms/records/add_record/addRecord.js"></script>
    <script src="/assets/js/forms/records/edit_record/editRecord.js"></script>
    <script src="/assets/js/forms/records/delete_record/deleteRecord.js"></script>
</body>
</html>