<?php 
    // Brings in the sidebar and links
    require __DIR__ . "/../components/ui/sidebar.php";
    require __DIR__ . "/../../../config/links.php";

    // Guards against unset errors
    $history = $data["history"] ?? null;
    $subdomains = $data["subdomains"] ?? [];
    $selected_subdomain_id = (int) ($data["selected_subdomain_id"] ?? 0);

    // Maps subdomain ids to FQDNs so the table can show a name instead of an id
    $subdomain_names = [];

    foreach ($subdomains as $subdomain) {
        $subdomain_names[(int) $subdomain["id"]] = $subdomain["fqdn"] ?? "";
    }

    // Badge styling for each change type
    $change_classes = [
        "added" => "bg-green-100 text-green-700",
        "removed" => "bg-red-100 text-red-700",
        "modified" => "bg-amber-100 text-amber-700"
    ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | History</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="min-h-screen flex bg-ocean-deep-50">
    <?php renderSidebar($site_links, "History"); ?>

    <main class="flex-1 min-w-0 px-4 py-8 md:px-8">
        <!-- Container for the dashboard -->
        <div class="w-full max-w-6xl mx-auto">
            <!-- Filters the history down to one subdomain -->
            <form action="/history" method="get" class="mb-4 flex items-center gap-2">
                <label for="historySubdomainFilter" class="text-sm font-medium text-ocean-deep-700">
                    Subdomain
                </label>
                <select
                    name="subdomainId"
                    id="historySubdomainFilter"
                    onchange="this.form.submit()"
                    class="w-full max-w-xs px-3 py-2 text-sm text-ocean-deep-900 bg-white border border-ocean-deep-200 rounded-lg outline-none transition-all duration-150"
                >
                    <option value="0">All subdomains</option>
                    <?php foreach ($subdomains as $subdomain): ?>
                        <option
                            value="<?= (int) $subdomain["id"] ?>"
                            <?= (int) $subdomain["id"] === $selected_subdomain_id ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($subdomain["fqdn"] ?? "") ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <noscript>
                    <button type="submit" class="rounded-lg bg-ocean-deep-600 px-3 py-2 text-sm text-white">Filter</button>
                </noscript>
            </form>

            <!-- Flexbox for the table -->
            <div class="overflow-x-auto rounded-lg border border-ocean-deep-200 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="bg-ocean-deep-100 text-xs uppercase tracking-wider text-ocean-deep-700">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Detected</th>
                            <th scope="col" class="px-4 py-3 font-medium">Subdomain</th>
                            <th scope="col" class="px-4 py-3 font-medium">Type</th>
                            <th scope="col" class="px-4 py-3 font-medium">Change</th>
                            <th scope="col" class="px-4 py-3 font-medium">Old Value</th>
                            <th scope="col" class="px-4 py-3 font-medium">New Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ocean-deep-100 text-ocean-deep-800">
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $entry): ?>
                                <tr class="transition-colors hover:bg-ocean-deep-50">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <?= htmlspecialchars($entry["detected_at"] ?? "-") ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars($subdomain_names[(int) ($entry["subdomain_id"] ?? 0)] ?? "-") ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 font-medium text-ocean-deep-950">
                                        <?= htmlspecialchars($entry["record_type"] ?? "-") ?>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium <?= $change_classes[$entry["change_type"] ?? ""] ?? "bg-ocean-deep-100 text-ocean-deep-700" ?>">
                                            <?= htmlspecialchars($entry["change_type"] ?? "-") ?>
                                        </span>
                                    </td>
                                    <td class="max-w-md break-all px-4 py-3">
                                        <?= htmlspecialchars($entry["old_value"] ?? "-") ?>
                                    </td>
                                    <td class="max-w-md break-all px-4 py-3">
                                        <?= htmlspecialchars($entry["new_value"] ?? "-") ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-ocean-deep-400">
                                    No history found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- The controller limits the list, so say so -->
            <p class="mt-3 text-xs text-ocean-deep-500">
                Showing the latest 100 changes, newest first.
            </p>
        </div>
    </main>

    <!-- Scripts -->
    <script src="/assets/js/components/sidebar.js"></script>
</body>
</html>