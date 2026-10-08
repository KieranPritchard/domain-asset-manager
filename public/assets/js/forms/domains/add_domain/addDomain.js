// Stores a cached list of existing domains
let existingDomains = []

// Regex that checks the domain format and requires a valid extension at the end e.g. .com
const DOMAIN_REGEX = /^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:[a-z]{2,63}|xn--[a-z0-9-]{2,59})$/i

// Tracks whether each field is currently valid
let domainValid = false
let registarValid = false

// Loads the domains from the PHP endpoint
async function loadDomains() {
    try {
        const response = await fetch("/domains/json")
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        const data = await response.json()

        // Stores just the name property of each domain, normalised for comparison
        existingDomains = data.map(domain => domain.name.trim().toLowerCase())
    } catch (error) {
        console.error("Could not load domains:", error)
        existingDomains = []
    }
    validateDomainName()
}

// Enables or disables the submit button based on both fields being valid
function updateSubmitButton() {
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const enabled = domainValid && registarValid

    // Adds or removes the disabled attribute on the button
    submitButton.toggleAttribute("disabled", !enabled)

    // Swaps between the curser pointer and the disabled one
    submitButton.classList.toggle("cursor-pointer", enabled)
    submitButton.classList.toggle("cursor-not-allowed", !enabled)
}

// Function to validate the domain Name
function validateDomainName(){
    // Stores the fields needed
    const domainNameField = document.getElementById("domainNameField").value.trim().toLowerCase()
    const domainNameError = document.getElementById("domainNameError")

    // Performs a length check
    if (domainNameField.length === 0) {
        domainNameError.innerText = "Please enter a domain name"
        domainNameError.classList.remove("hidden")
        domainValid = false
    } else if (!DOMAIN_REGEX.test(domainNameField)) {
        // Format check, makes sure the domain has a valid extension e.g. example.com
        domainNameError.innerText = "Enter a valid domain with an extension, e.g. example.com"
        domainNameError.classList.remove("hidden")
        domainValid = false
    } else if (existingDomains.includes(domainNameField)) {
        // Duplicate check against the data from /domains/json
        domainNameError.innerText = "This domain already exists"
        domainNameError.classList.remove("hidden")
        domainValid = false
    } else {
        // Resets everything
        domainNameError.classList.add("hidden")
        domainValid = true
    }

    // Updates the submit button based on both fields
    updateSubmitButton()
}

// Function to validate the registar
function validateRegistarName(){
    // Stores the fields needed
    const registarField = document.getElementById("registarField").value.trim()
    const registarError = document.getElementById("registarError")

    // Performs a length check
    if (registarField.length === 0) {
        registarError.innerText = "Please enter a registar"
        registarError.classList.remove("hidden")
        registarValid = false
    } else {
        // Resets everything
        registarError.classList.add("hidden")
        registarValid = true
    }

    // Updates the submit button based on both fields
    updateSubmitButton()
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("domainNameField").addEventListener("input", validateDomainName)
    document.getElementById("registarField").addEventListener("input", validateRegistarName)

    // Sets the initial state of the registar field so the button is correct on page load
    validateRegistarName()
    loadDomains()
})