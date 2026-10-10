<?php
// Queues a toast to show on the next page load (use before a redirect)
function flashToast(string $type, string $title, string $message = ''): void {
    $_SESSION['toasts'][] = ['type' => $type, 'title' => $title, 'message' => $message];
}

// Put once in your layout, after toast.js and lucide
function renderToasts(): void {
    $toasts = $_SESSION['toasts'] ?? [];
    unset($_SESSION['toasts']);
?>
    <div id="toast-container" class="fixed bottom-4 right-4 z-60 flex w-full max-w-sm flex-col gap-3 pointer-events-none"></div>

    <?php if ($toasts): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?= json_encode($toasts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                .forEach(t => showToast(t.type, t.title, t.message))
        })
    </script>
    <?php endif; ?>
<?php } ?>