<?php
    // Builds the parent domain options, escaped for safe output
    $domain_options = "<option value='' disabled selected>Select a domain</option>";
    foreach ($domains as $domain) {
        $domain_id = (int) $domain["id"];
        $domain_name = htmlspecialchars($domain["name"], ENT_QUOTES);
        $domain_options .= "<option value='{$domain_id}'>{$domain_name}</option>";
    }

    // Shared input styling
    $input_class = "w-full px-3 py-2 text-sm text-ocean-deep-900 bg-white border rounded-lg outline-none transition-all duration-150";

    // Shared button styling
    $cancel_class = "w-full p-2 border border-ocean-deep-600 text-ocean-deep-600 hover:bg-ocean-deep-600/60 rounded-lg transition-colors cursor-pointer";
    $submit_class = "w-full p-2 bg-ocean-deep-600 text-white hover:bg-ocean-deep-700 rounded-lg transition-colors cursor-pointer";
    $danger_class = "w-full p-2 bg-red-600 text-white hover:bg-red-700 rounded-lg transition-colors cursor-pointer";

    // ---------- Add subdomain ----------
    $add_subdomain_content = "
        <form action='/subdomains/create' method='post' class='space-y-4'>
            <div class='space-y-3'>
                <!-- Parent domain -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='addDomainId' class='text-sm font-medium text-ocean-deep-700'>
                        Parent Domain
                    </label>
                    <select required name='domainId' id='addDomainId' class='{$input_class}'>
                        {$domain_options}
                    </select>
                </div>

                <!-- Subdomain name -->
                <div>
                    <div class='flex flex-col space-y-1.5 mb-4'>
                        <label for='addSubdomainName' class='text-sm font-medium text-ocean-deep-700'>
                            Subdomain
                        </label>
                        <input
                            required
                            onkeyup='validateSubdomainName(\"addSubdomainName\", \"addSubdomainError\")'
                            name='subdomainName'
                            id='addSubdomainName'
                            type='text'
                            placeholder='e.g. api or dev.api'
                            class='{$input_class}'
                        >
                    </div>
                    <div class='flex justify-between -mt-2'>
                        <span id='addSubdomainError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button type='button' onclick=\"closeModal('addSubdomainModal')\" class='{$cancel_class}'>
                    Cancel
                </button>
                <button type='submit' class='{$submit_class}'>
                    Submit
                </button>
            </div>
        </form>
    ";

    // ---------- Edit subdomain ----------
    $edit_subdomain_content = "
        <form action='/subdomains/update' method='post' class='space-y-4'>
            <input type='hidden' name='subdomainId' id='editSubdomainId'>

            <div class='space-y-3'>
                <!-- Subdomain name -->
                <div>
                    <div class='flex flex-col space-y-1.5 mb-4'>
                        <label for='editSubdomainName' class='text-sm font-medium text-ocean-deep-700'>
                            Subdomain
                        </label>
                        <div class='flex items-center gap-2'>
                            <input
                                required
                                onkeyup='validateSubdomainName(\"editSubdomainName\", \"editSubdomainError\")'
                                name='subdomainName'
                                id='editSubdomainName'
                                type='text'
                                class='{$input_class}'
                            >
                            <!-- Parent domain can't change on update, so it's shown read-only -->
                            <span id='editParentSuffix' class='text-sm text-ocean-deep-700 whitespace-nowrap'></span>
                        </div>
                    </div>
                    <div class='flex justify-between -mt-2'>
                        <span id='editSubdomainError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>

                <!-- Status -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='editStatus' class='text-sm font-medium text-ocean-deep-700'>
                        Status
                    </label>
                    <select required name='status' id='editStatus' class='{$input_class}'>
                        <option value='active'>Active</option>
                        <option value='inactive'>Inactive</option>
                        <option value='unknown'>Unknown</option>
                    </select>
                </div>
            </div>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button type='button' onclick=\"closeModal('editSubdomainModal')\" class='{$cancel_class}'>
                    Cancel
                </button>
                <button type='submit' class='{$submit_class}'>
                    Save Changes
                </button>
            </div>
        </form>
    ";

    // ---------- Delete subdomain ----------
    $delete_subdomain_content = "
        <form action='/subdomains/delete' method='post' class='space-y-4'>
            <input type='hidden' name='subdomainId' id='deleteSubdomainId'>

            <p class='text-sm text-ocean-deep-700'>
                Are you sure you want to delete
                <span id='deleteSubdomainName' class='font-semibold'></span>?
                This can't be undone.
            </p>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button type='button' onclick=\"closeModal('deleteSubdomainModal')\" class='{$cancel_class}'>
                    Cancel
                </button>
                <button type='submit' class='{$danger_class}'>
                    Delete
                </button>
            </div>
        </form>
    ";

    renderModal("addSubdomainModal", "Create New Subdomain", $add_subdomain_content);
    renderModal("editSubdomainModal", "Edit Subdomain", $edit_subdomain_content);
    renderModal("deleteSubdomainModal", "Delete Subdomain", $delete_subdomain_content);
?>