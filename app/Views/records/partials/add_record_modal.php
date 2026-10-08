<?php 
    // Builds the subdomain options, escaped for safe output
    $subdomain_options = "<option value='' disabled selected>Select a subdomain</option>";
    foreach ($subdomains as $subdomain) {
        $subdomain_id = (int) $subdomain["id"];
        $subdomain_name = htmlspecialchars($subdomain["fqdn"], ENT_QUOTES);
        $subdomain_options .= "<option value='{$subdomain_id}'>{$subdomain_name}</option>";
    }

    // Shared input styling
    $input_class = "w-full px-3 py-2 text-sm text-ocean-deep-900 bg-white border rounded-lg outline-none transition-all duration-150";

    // Stores the add record modal content
    $add_record_content = "
        <form action='/records/create' method='post' class='space-y-4'>
            <!-- Container for the form details -->
            <div class='space-y-3'>
                <!-- Subdomain field -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='addRecordSubdomainId' class='text-sm font-medium text-ocean-deep-700'>
                        Subdomain
                    </label>
                    <select required name='subdomainId' id='addRecordSubdomainId' class='{$input_class}'>
                        {$subdomain_options}
                    </select>
                </div>

                <!-- Record type field -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='addRecordType' class='text-sm font-medium text-ocean-deep-700'>
                        Record Type
                    </label>
                    <select
                        required
                        name='recordType'
                        id='addRecordType'
                        onchange=\"toggleRecordFields('add')\"
                        class='{$input_class}'
                    >
                        <option value='A'>A</option>
                        <option value='AAAA'>AAAA</option>
                        <option value='CNAME'>CNAME</option>
                        <option value='MX'>MX</option>
                        <option value='TXT'>TXT</option>
                        <option value='NS'>NS</option>
                        <option value='SOA'>SOA</option>
                        <option value='SRV'>SRV</option>
                    </select>
                </div>

                <!-- Value field -->
                <div>
                    <div class='flex flex-col space-y-1.5 mb-4'>
                        <label for='addRecordValue' class='text-sm font-medium text-ocean-deep-700'>
                            Value
                        </label>
                        <input
                            required
                            maxlength='512'
                            name='value'
                            id='addRecordValue'
                            type='text'
                            placeholder='e.g. 203.0.113.10'
                            class='{$input_class}'
                        >
                    </div>
                    <!-- Stores the error box -->
                    <div class='flex justify-between -mt-2'>
                        <span id='addRecordValueError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>

                <!-- TTL field -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='addRecordTtl' class='text-sm font-medium text-ocean-deep-700'>
                        TTL (seconds, optional)
                    </label>
                    <input
                        min='0'
                        name='ttl'
                        id='addRecordTtl'
                        type='number'
                        placeholder='e.g. 3600'
                        class='{$input_class}'
                    >
                </div>

                <!-- Priority field, only shown for MX and SRV -->
                <div id='addRecordPriorityWrapper' class='hidden flex flex-col space-y-1.5 mb-4'>
                    <label for='addRecordPriority' class='text-sm font-medium text-ocean-deep-700'>
                        Priority
                    </label>
                    <input
                        min='0'
                        max='65535'
                        name='priority'
                        id='addRecordPriority'
                        type='number'
                        placeholder='e.g. 10'
                        class='{$input_class}'
                    >
                </div>
            </div>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button
                    type='button'
                    id='addCancelBtn'
                    onClick=\"closeModal('addRecordModal')\"
                    class='w-full p-2 border border-ocean-deep-600 text-ocean-deep-600 hover:bg-ocean-deep-600/60 rounded-lg transition-colors cursor-pointer'
                >
                    Cancel
                </button>
                <button
                    id='addSubmitBtn'
                    type='submit'
                    class='w-full p-2 bg-ocean-deep-600 text-white hover:bg-ocean-deep-700 rounded-lg transition-colors cursor-pointer'
                >
                    Submit
                </button>
            </div>
        </form>
    ";

    renderModal(
        "addRecordModal", 
        "Create New Record",
        $add_record_content
    )
?>