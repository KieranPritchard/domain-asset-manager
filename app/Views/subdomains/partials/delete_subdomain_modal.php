<?php 
    // Stores the delete subdomain modal content
    $delete_subdomain_content = "
        <form action='/subdomains/delete' method='post' class='space-y-4'>
            <!-- Stores the id of the subdomain being deleted -->
            <input type='hidden' name='subdomainId' id='deleteSubdomainId'>

            <!-- Confirmation message -->
            <p class='text-sm text-ocean-deep-700'>
                Are you sure you want to delete
                <span id='deleteSubdomainName' class='font-semibold text-ocean-deep-950'></span>?
                This action cannot be undone.
            </p>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button
                    type='button'
                    id='deleteCancelBtn'
                    onClick=\"closeModal('deleteSubdomainModal')\"
                    class='w-full p-2 border border-ocean-deep-600 text-ocean-deep-600 hover:bg-ocean-deep-600/60 rounded-lg transition-colors cursor-pointer'
                >
                    Cancel
                </button>
                <button
                    id='deleteSubmitBtn'
                    type='submit'
                    class='w-full p-2 bg-red-600 text-white hover:bg-red-700 rounded-lg transition-colors cursor-pointer'
                >
                    Delete
                </button>
            </div>
        </form>
    ";

    renderModal(
        "deleteSubdomainModal", 
        "Delete Subdomain",
        $delete_subdomain_content
    )
?>