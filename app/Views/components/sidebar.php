<?php
    function renderSidebar(array $nav_links, string $current):void {
?>
    <aside
        id="sidebar"
        class="relative h-screen bg-ocean-deep-400 text-ocean-deep-950 transition-all duration-300 ease-in-out flex flex-col justify-between p-4 w-64"
    >
        <!-- Top section: header and toggle button -->
        <div>
            <div class="flex items-center justify-between mb-8 px-2">
                <span id="sidebar-header" class="text-xl font-bold tracking-wide text-ocean-deep-50">
                    Domain Assets
                </span>
                <button
                    id="sidebar-toggle"
                    type="button"
                    aria-label="Toggle Sidebar"
                    class="p-1.5 rounded-lg hover:bg-ocean-deep-900/60 text-ocean-deep-300 hover:text-ocean-deep-100 transition-colors ml-auto"
                >
                    <i width="20" height="20" data-lucide="menu"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1.5">
                <?php foreach ($nav_links as $nav_item): ?>
                    <a
                        href="<?= htmlspecialchars($nav_item["link"]) ?>"
                        class="flex items-center gap-4 px-3 py-2.5 rounded-lg font-medium transition-colors <?= $nav_item["label"] === $current ? 'bg-ocean-deep-600 text-ocean-deep-100 shadow-sm' : 'text-ocean-deep-300/80 hover:bg-ocean-deep-900/50 hover:text-ocean-deep-100' ?>"
                    >
                        <i width="20" height="20" class="shrink-0" data-lucide="<?= htmlspecialchars($nav_item["icon"]) ?>"></i>
                        <span class="sidebar-label truncate transition-opacity duration-200">
                            <?= htmlspecialchars($nav_item["label"]) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Bottom section: logout -->
        <div id="sidebar-divider" class="border-t border-ocean-deep-900/80 pt-4">
            <a
                href="/logout"
                class="w-full flex items-center gap-4 px-3 py-2.5 rounded-lg text-ocean-deep-100/80 hover:bg-red-500/10 hover:text-red-400 transition-colors"
            >
                <i width="20" height="20" class="shrink-0" data-lucide="logout"></i>
                <span class="sidebar-label font-medium truncate">Logout</span>
            </a>
        </div>
    </aside>
<?php } ?>