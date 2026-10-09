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

    renderModal("addSubdomainModal", "Create New Subdomain", $add_subdomain_content);
?>