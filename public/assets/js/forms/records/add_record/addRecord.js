// Stores a cached list of existing records as { id, key }
let addExistingRecords = []

// Tracks whether each field is currently valid
let addSubdomainValid = false
let addValueValid = false
let addTtlValid = true
let addPriorityValid = true

// Enables or disables the submit button based on every field being valid
function updateAddSubmitButton() {
    setSubmitState(
        "addSubmitBtn",
        addSubdomainValid && addValueValid && addTtlValid && addPriorityValid
    )
}

// Function to validate the subdomain select
function validateAddSubdomain() {
    // Stores the fields needed
    const subdomainField = document.getElementById("addRecordSubdomainId")

    // Checks a real subdomain has been picked (the placeholder option has an empty value)
    addSubdomainValid = showFieldError(
        "addRecordSubdomainError",
        subdomainField.value === "" ? "Please select a subdomain" : ""
    )

    updateAddSubmitButton()
}

// Function to validate the value, silent keeps the error hidden while the field is empty
function validateAddValue(silent = false) {
    // Stores the fields needed
    const type = document.getElementById("addRecordType").value
    const subdomainId = document.getElementById("addRecordSubdomainId").value
    const value = document.getElementById("addRecordValue").value.trim()

    // Doesn't nag about an empty field the user hasn't touched yet
    if (value === "" && silent) {
        showFieldError("addRecordValueError", "")
        addValueValid = false
        updateAddSubmitButton()
        return
    }

    // Format check, matches the server-side rule
    let message = getValueError(type, value)

    // Duplicate check against the data from /records/json
    if (!message && subdomainId !== ""
        && addExistingRecords.some(record => record.key === recordKey(subdomainId, type, value))) {
        message = "This record already exists"
    }

    addValueValid = showFieldError("addRecordValueError", message)

    updateAddSubmitButton()
}

// Function to validate the TTL
function validateAddTtl() {
    const ttl = document.getElementById("addRecordTtl").value

    addTtlValid = showFieldError("addRecordTtlError", getTtlError(ttl))

    updateAddSubmitButton()
}

// Function to validate the priority, silent keeps the error hidden while the field is empty
function validateAddPriority(silent = false) {
    // Stores the fields needed
    const type = document.getElementById("addRecordType").value
    const priority = document.getElementById("addRecordPriority").value

    // Not needed for this type, so nothing to check
    if (!PRIORITY_TYPES.includes(type)) {
        showFieldError("addRecordPriorityError", "")
        addPriorityValid = true
        updateAddSubmitButton()
        return
    }

    // Doesn't nag about an empty field the user hasn't touched yet
    if (priority.trim() === "" && silent) {
        showFieldError("addRecordPriorityError", "")
        addPriorityValid = false
        updateAddSubmitButton()
        return
    }

    addPriorityValid = showFieldError("addRecordPriorityError", getPriorityError(type, priority))

    updateAddSubmitButton()
}

// Runs when the record type changes
function validateAddType() {
    toggleRecordFields("add")

    // The value rule and the priority requirement both depend on the type
    validateAddValue(true)
    validateAddPriority(true)
}

// Loads the existing records so the duplicate check has data
async function loadAddRecords() {
    addExistingRecords = await fetchExistingRecords()

    // Re-runs the duplicate check if the user started typing before the data arrived
    if (document.getElementById("addRecordValue").value.trim().length > 0) {
        validateAddValue()
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("addRecordSubdomainId").addEventListener("change", () => {
        validateAddSubdomain()

        // Changing the subdomain changes the duplicate key, so the value check needs to run again
        if (document.getElementById("addRecordValue").value.trim().length > 0) {
            validateAddValue()
        }
    })

    document.getElementById("addRecordType").addEventListener("change", validateAddType)
    document.getElementById("addRecordValue").addEventListener("input", () => validateAddValue())
    document.getElementById("addRecordTtl").addEventListener("input", validateAddTtl)
    document.getElementById("addRecordPriority").addEventListener("input", () => validateAddPriority())

    // Sets the initial state so the button starts disabled without showing errors up front
    toggleRecordFields("add")
    updateAddSubmitButton()
    loadAddRecords()
})