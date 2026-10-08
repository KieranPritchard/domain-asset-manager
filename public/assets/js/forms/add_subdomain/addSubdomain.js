// Stores a cached list of existing subdomains (full FQDNs)
let existingSubdomains = []

// Same rule as valid_prefix() in the controller
const SUBDOMAIN_REGEX = /^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))*$/

// Tracks whether each field is currently valid
let parentValid = false
let subdomainValid = false

// Loads the subdomains from the PHP endpoint
async function loadSubdomains() {
    try {
        const response = await fetch("/subdomains/json")
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        const data = await response.json()

        // Stores just the name property of each subdomain, normalised for comparison
        existingSubdomains = data.map(subdomain => subdomain.name.trim().toLowerCase())
    } catch (error) {
        console.error("Could not load subdomains:", error)
        existingSubdomains = []
    }

    // Re-runs the duplicate check if the user started typing before the data arrived
    if (document.getElementById("addSubdomainName").value.trim().length > 0) {
        validateAddSubdomainName()
    }
}

// Enables or disables the submit button based on both fields being valid
function updateAddSubmitButton() {
    // Stores the fields needed
    const submitButton = document.getElementById("addSubmitBtn")
    const enabled = parentValid && subdomainValid

    // Adds or removes the disabled attribute on the button
    submitButton.toggleAttribute("disabled", !enabled)

    // Swaps between the cursor pointer and the disabled one
    submitButton.classList.toggle("cursor-pointer", enabled)
    submitButton.classList.toggle("cursor-not-allowed", !enabled)
}

// Function to validate the parent domain select
function validateAddParentDomain() {
    // Stores the fields needed
    const parentField = document.getElementById("addDomainId")
    const parentError = document.getElementById("addDomainError")

    // Checks a real domain has been picked (the placeholder option has an empty value)
    if (parentField.value === "") {
        parentError.innerText = "Please select a parent domain"
        parentError.classList.remove("hidden")
        parentValid = false
    } else {
        // Resets everything
        parentError.classList.add("hidden")
        parentValid = true
    }

    // Updates the submit button based on both fields
    updateAddSubmitButton()
}

// Function to validate the subdomain name
function validateAddSubdomainName() {
    // Stores the fields needed
    const subdomainField = document.getElementById("addSubdomainName")
    const subdomainError = document.getElementById("addSubdomainError")
    const parentField = document.getElementById("addDomainId")

    const prefix = subdomainField.value.trim().toLowerCase()

    // Builds the fqdn from the selected parent, e.g. api + example.com
    const parentName = parentField.selectedOptions[0]?.text.trim().toLowerCase() ?? ""
    const fqdn = `${prefix}.${parentName}`

    // Performs a length check
    if (prefix.length === 0) {
        subdomainError.innerText = "Please enter a subdomain"
        subdomainError.classList.remove("hidden")
        subdomainValid = false
    } else if (prefix.length > 190 || !SUBDOMAIN_REGEX.test(prefix)) {
        // Format check, matches the server-side rule
        subdomainError.innerText = "Use letters, numbers and hyphens only (no leading/trailing hyphen)"
        subdomainError.classList.remove("hidden")
        subdomainValid = false
    } else if (parentField.value !== "" && existingSubdomains.includes(fqdn)) {
        // Duplicate check against the data from /subdomains/json
        subdomainError.innerText = "This subdomain already exists"
        subdomainError.classList.remove("hidden")
        subdomainValid = false
    } else {
        // Resets everything
        subdomainError.classList.add("hidden")
        subdomainValid = true
    }

    // Updates the submit button based on both fields
    updateAddSubmitButton()
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("addSubdomainName").addEventListener("input", validateAddSubdomainName)

    // Changing the parent changes the fqdn, so both checks need to run again
    document.getElementById("addDomainId").addEventListener("change", () => {
        validateAddParentDomain()
        if (document.getElementById("addSubdomainName").value.trim().length > 0) {
            validateAddSubdomainName()
        }
    })

    // Sets the initial state so the button starts disabled without showing errors up front
    updateAddSubmitButton()
    loadSubdomains()
})