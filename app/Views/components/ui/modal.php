<?php 
    function renderModal(string $id, string $title, string $contentHtml):void {
?>
    <!-- Modal container -->
    <div
        id="<?= htmlspecialchars($id) ?>"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 modal-backdrop"
        onclick="closeModal('<?= htmlspecialchars($id) ?>')"
    >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Modal panel -->
        <div
            class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl border border-slate-200/80"
            onclick="event.stopPropagation()"
        >
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    <?php echo htmlspecialchars($title); ?>
                </h2>
                <button 
                    id="closeModalBtn"
                    type="button"
                    onclick="closeModal('<?php echo htmlspecialchars($id); ?>')" 
                    class="text-gray-400 hover:text-gray-600"
                >
                    <!-- Lucide X Icon SVG -->
                    <i width="24" height="24" class="shrink-0" data-lucide="x"></i>
                </button>
            </div>

            <div class="text-gray-600">
                <?php echo $contentHtml; ?>
            </div>
        </div>
    </div>
<?php } ?>