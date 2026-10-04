<?php 
    // Stores the edit domain modal content
    $edit_domain_content = "
        <form action='/domains/update' method='post' class='space-y-4'>
            <!-- Stores the id of the domain being edited -->
            <input type='hidden' name='domainId' id='editDomainId'>

            <!-- Container for the form details -->
            <div class='space-y-3'>
                <!-- Name field -->
                <div>
                    <div class='flex flex-col space-y-1.5 mb-4'>
                        <label for='editDomainNameField' class='text-sm font-medium text-ocean-deep-700'>
                            Domain Name
                        </label>
                        <input 
                            required
                            name='domainName'
                            id='editDomainNameField'
                            type='text'
                            placeholder='Enter your Domain Name'
                            class='w-full px-3 py-2 text-sm text-ocean-deep-900 bg-white border rounded-lg outline-none transition-all duration-150'
                        >
                    </div>
                    <!-- Stores the error box -->
                    <div class='flex justify-between -mt-2'>
                        <span id='editDomainNameError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>
                <!-- Registar field -->
                <div>
                    <div class='flex flex-col space-y-1.5 mb-4'>
                        <label for='editRegistarField' class='text-sm font-medium text-ocean-deep-700'>
                            Registar
                        </label>
                        <input 
                            required
                            name='registar'
                            id='editRegistarField'
                            type='text'
                            placeholder='Enter your Registar'
                            class='w-full px-3 py-2 text-sm text-ocean-deep-900 bg-white border rounded-lg outline-none transition-all duration-150'
                        >
                    </div>
                    <!-- Stores the error box -->
                    <div class='flex justify-between -mt-2'>
                        <span id='editRegistarError' class='hidden text-xs font-semibold text-red-600'></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class='flex justify-between gap-2 pt-3 border-t border-slate-100'>
                <button
                    type='button'
                    id='editCancelBtn'
                    onClick=\"closeModal('editDomainModal')\"
                    class='w-full p-2 border border-ocean-deep-600 text-ocean-deep-600 hover:bg-ocean-deep-600/60 rounded-lg transition-colors cursor-pointer'
                >
                    Cancel
                </button>
                <button
                    id='editSubmitBtn'
                    type='submit'
                    class='w-full p-2 bg-ocean-deep-600 text-white hover:bg-ocean-deep-700 rounded-lg transition-colors cursor-pointer'
                >
                    Submit
                </button>
            </div>
        </form>
    ";

    renderModal(
        "editDomainModal", 
        "Edit Domain",
        $edit_domain_content
    )
?>