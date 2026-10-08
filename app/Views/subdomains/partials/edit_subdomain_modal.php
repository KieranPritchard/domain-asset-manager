<?php 
    // Stores the edit subdomain modal content
    $edit_subdomain_content = "
        <form action='/subdomains/update' method='post' class='space-y-4'>
            <!-- Stores the id of the subdomain being edited -->
            <input type='hidden' name='subdomainId' id='editSubdomainId'>

            <!-- Container for the form details -->
            <div class='space-y-3'>
                <!-- Name field -->
                <div>
                    <div class='flex flex-col space-y-1.5 mb-4'>
                        <label for='editSubdomainNameField' class='text-sm font-medium text-ocean-deep-700'>
                            Subdomain
                        </label>
                        <div class='flex items-center gap-2'>
                            <input 
                                required
                                onkeyup=\"validateSubdomainName('editSubdomainNameField', 'editSubdomainNameError')\"
                                name='subdomainName'
                                id='editSubdomainNameField'
                                type='text'
                                placeholder='e.g. api or dev.api'
                                class='w-full px-3 py-2 text-sm text-ocean-deep-900 bg-white border rounded-lg outline-none transition-all duration-150'
                            >
                            <!-- Parent domain can't change on update, so it's shown read-only -->
                            <span id='editParentSuffix' class='text-sm text-ocean-deep-700 whitespace-nowrap'></span>
                        </div>
                    </div>
                    <!-- Stores the error box -->
                    <div class='flex justify-between -mt-2'>
                        <span id='editSubdomainNameError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>
                <!-- Status field -->
                <div>
                    <div class='flex flex-col space-y-1.5 mb-4'>
                        <label for='editStatusField' class='text-sm font-medium text-ocean-deep-700'>
                            Status
                        </label>
                        <select 
                            required
                            name='status'
                            id='editStatusField'
                            class='w-full px-3 py-2 text-sm text-ocean-deep-900 bg-white border rounded-lg outline-none transition-all duration-150'
                        >
                            <option value='active'>Active</option>
                            <option value='inactive'>Inactive</option>
                            <option value='unknown'>Unknown</option>
                        </select>
                    </div>
                    <!-- Stores the error box -->
                    <div class='flex justify-between -mt-2'>
                        <span id='editStatusError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button
                    type='button'
                    id='editCancelBtn'
                    onClick=\"closeModal('editSubdomainModal')\"
                    class='w-full p-2 border border-ocean-deep-600 text-ocean-deep-600 hover:bg-ocean-deep-600/60 rounded-lg transition-colors cursor-pointer'
                >
                    Cancel
                </button>
                <button
                    id='editSubmitBtn'
                    type='submit'
                    class='w-full p-2 bg-ocean-deep-600 text-white hover:bg-ocean-deep-700 rounded-lg transition-colors cursor-pointer'
                >
                    Save Changes
                </button>
            </div>
        </form>
    ";

    renderModal(
        "editSubdomainModal", 
        "Edit Subdomain",
        $edit_subdomain_content
    )
?>