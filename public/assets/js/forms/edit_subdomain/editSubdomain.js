// Stores a cached list of existing subdomains (full FQDNs)
let editExistingSubdomains = []

// Stores the original FQDN so the duplicate check can ignore it
let editingSubdomainName = ""

// Stores the parent domain suffix of the subdomain being edited, e.g. ".example.com"
let editingParentSuffix = ""

// Same rule as valid_prefix() in the controller
const EDIT_SUBDOMAIN_REGEX = /^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))*$/

// Same values as STATUSES in the controller
const EDIT_STATUSES = ["active", "inactive", "unknown"]

// Tracks whether each field is currently valid
let editSubdomainValid = false
let editStatusValid = false

// Fills the edit modal with the clicked row's data, then opens it
function openEditSubdomainModal(button) {
    const fqdn = button.dataset.name.trim().toLowerCase()
    const parentName = button.dataset.parent.trim().toLowerCase()

    // Strips ".parent.com" so only the prefix is editable
    editingParentSuffix = "." + parentName
    const prefix = fqdn.endsWith(editingParentSuffix)
        ? fqdn.slice(0, -editingParentSuffix.length)
        : fqdn

    document.getElementById("editSubdomainId").value = button.dataset.id
    document.getElementById("editSubdomainNameField").value = prefix
    document.getElementById("editParentSuffix").textContent = editingParentSuffix
    document.getElementById("editStatusField").value = button.dataset.status

    editingSubdomainName = fqdn

    // Runs the validators so the button state is correct as soon as the modal opens
    validateEditSubdomainName()
    validateEditStatus()

    openModal("editSubdomainModal")
}

// Loads the subdomains from the PHP endpoint
async function loadEditSubdomains() {
    try {
        const response = await fetch("/subdomains/json")
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        const data = await response.json()

        // Stores just the name property of each subdomain, normalised for comparison
        editExistingSubdomains = data.map(subdomain => subdomain.name.trim().toLowerCase())
    } catch (error) {
        console.error("Could not load subdomains:", error)
        editExistingSubdomains = []
    }
}

// Enables or disables the submit button based on both fields being valid
function updateEditSubmitButton() {
    // Stores the fields needed
    const submitButton = document.getElementById("editSubmitBtn")
    const enabled = editSubdomainValid && editStatusValid

    // Adds or removes the disabled attribute on the button
    submitButton.toggleAttribute("disabled", !enabled)

    // Swaps between the cursor pointer and the disabled one
    submitButton.classList.toggle("cursor-pointer", enabled)
    submitButton.classList.toggle("cursor-not-allowed", !enabled)
}

// Function to validate the subdomain name
function validateEditSubdomainName() {
    // Stores the fields needed
    const prefix = document.getElementById("editSubdomainNameField").value.trim().toLowerCase()
    const subdomainError = document.getElementById("editSubdomainNameError")

    // Builds the fqdn from the prefix and the fixed parent, e.g. api + .example.com
    const fqdn = prefix + editingParentSuffix

    // Performs a length check
    if (prefix.length === 0) {
        subdomainError.innerText = "Please enter a subdomain"
        subdomainError.classList.remove("hidden")
        editSubdomainValid = false
    } else if (prefix.length > 190 || !EDIT_SUBDOMAIN_REGEX.test(prefix)) {
        // Format check, matches the server-side rule
        subdomainError.innerText = "Use letters, numbers and hyphens only (no leading/trailing hyphen)"
        subdomainError.classList.remove("hidden")
        editSubdomainValid = false
    } else if (fqdn !== editingSubdomainName && editExistingSubdomains.includes(fqdn)) {
        // Duplicate check against the data from /subdomains/json, ignoring the subdomain being edited
        subdomainError.innerText = "This subdomain already exists"
        subdomainError.classList.remove("hidden")
        editSubdomainValid = false
    } else {
        // Resets everything
        subdomainError.classList.add("hidden")
        editSubdomainValid = true
    }

    // Updates the submit button based on both fields
    updateEditSubmitButton()
}

// Function to validate the status
function validateEditStatus() {
    // Stores the fields needed
    const status = document.getElementById("editStatusField").value
    const statusError = document.getElementById("editStatusError")

    // Checks the value is one the controller accepts
    if (!EDIT_STATUSES.includes(status)) {
        statusError.innerText = "Please select a valid status"
        statusError.classList.remove("hidden")
        editStatusValid = false
    } else {
        // Resets everything
        statusError.classList.add("hidden")
        editStatusValid = true
    }

    // Updates the submit button based on both fields
    updateEditSubmitButton()
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("editSubdomainNameField").addEventListener("input", validateEditSubdomainName)
    document.getElementById("editStatusField").addEventListener("change", validateEditStatus)
    loadEditSubdomains()
})