<?php 
    // Builds the subdomain options, escaped for safe output
    $subdomain_options = "";
    foreach ($subdomains as $subdomain) {
        $subdomain_id = (int) $subdomain["id"];
        $subdomain_name = htmlspecialchars($subdomain["fqdn"], ENT_QUOTES);
        $subdomain_options .= "<option value='{$subdomain_id}'>{$subdomain_name}</option>";
    }

    // Shared input styling
    $input_class = "w-full px-3 py-2 text-sm text-ocean-deep-900 bg-white border rounded-lg outline-none transition-all duration-150";

    // Stores the edit record modal content
    $edit_record_content = "
        <form action='/dns-records/update' method='post' class='space-y-4'>
            <!-- Stores the id of the record being edited -->
            <input type='hidden' name='recordId' id='editRecordId'>

            <!-- The subdomain can't change on update, so it's sent hidden and shown read-only -->
            <input type='hidden' name='subdomainId' id='editRecordSubdomainId'>

            <!-- Container for the form details -->
            <div class='space-y-3'>
                <!-- Subdomain field (read-only) -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='editRecordSubdomainDisplay' class='text-sm font-medium text-ocean-deep-700'>
                        Subdomain
                    </label>
                    <select disabled id='editRecordSubdomainDisplay' class='{$input_class} opacity-60 cursor-not-allowed'>
                        {$subdomain_options}
                    </select>
                </div>

                <!-- Record type field -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='editRecordType' class='text-sm font-medium text-ocean-deep-700'>
                        Record Type
                    </label>
                    <select
                        required
                        name='recordType'
                        id='editRecordType'
                        onchange=\"toggleRecordFields('edit')\"
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
                        <label for='editRecordValue' class='text-sm font-medium text-ocean-deep-700'>
                            Value
                        </label>
                        <input
                            required
                            maxlength='512'
                            name='value'
                            id='editRecordValue'
                            type='text'
                            class='{$input_class}'
                        >
                    </div>
                    <!-- Stores the error box -->
                    <div class='flex justify-between -mt-2'>
                        <span id='editRecordValueError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>

                <!-- TTL field -->
                <div class='flex flex-col space-y-1.5 mb-4'>
                    <label for='editRecordTtl' class='text-sm font-medium text-ocean-deep-700'>
                        TTL (seconds, optional)
                    </label>
                    <input
                        min='0'
                        name='ttl'
                        id='editRecordTtl'
                        type='number'
                        placeholder='e.g. 3600'
                        class='{$input_class}'
                    >
                    <span id='editRecordTtlError' class='hidden text-xs font-semibold text-red-600'></span>
                </div>

                <!-- Priority field, only shown for MX and SRV -->
                <div id='editRecordPriorityWrapper' class='hidden flex flex-col space-y-1.5 mb-4'>
                    <label for='editRecordPriority' class='text-sm font-medium text-ocean-deep-700'>
                        Priority
                    </label>
                    <input
                        min='0'
                        max='65535'
                        name='priority'
                        id='editRecordPriority'
                        type='number'
                        placeholder='e.g. 10'
                        class='{$input_class}'
                    >
                    <span id='editRecordPriorityError' class='hidden text-xs font-semibold text-red-600'></span>
                </div>
            </div>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button
                    type='button'
                    id='editCancelBtn'
                    onClick=\"closeModal('editRecordModal')\"
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
        "editRecordModal", 
        "Edit Record",
        $edit_record_content
    )
?>