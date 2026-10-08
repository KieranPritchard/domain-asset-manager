// Stores a cached list of existing records as { id, key }
let editExistingRecords = []

// Stores the id of the record being edited so the duplicate check can ignore it
let editingRecordId = 0

// Tracks whether each field is currently valid
let editValueValid = false
let editTtlValid = true
let editPriorityValid = true

// Fills the edit modal with the clicked row's data, then opens it
function openEditRecordModal(button) {
    const { id, subdomainId, type, value, ttl, priority } = button.dataset

    editingRecordId = Number(id)

    document.getElementById("editRecordId").value = id
    document.getElementById("editRecordSubdomainId").value = subdomainId
    document.getElementById("editRecordSubdomainDisplay").value = subdomainId
    document.getElementById("editRecordType").value = type
    document.getElementById("editRecordValue").value = value
    document.getElementById("editRecordTtl").value = ttl
    document.getElementById("editRecordPriority").value = priority

    // Shows or hides the priority field to suit the type
    toggleRecordFields("edit")

    // Runs the validators so the button state is correct as soon as the modal opens
    validateEditValue()
    validateEditTtl()
    validateEditPriority()

    openModal("editRecordModal")
}

// Enables or disables the submit button based on every field being valid
function updateEditSubmitButton() {
    setSubmitState("editSubmitBtn", editValueValid && editTtlValid && editPriorityValid)
}

// Function to validate the value
function validateEditValue() {
    // Stores the fields needed
    const type = document.getElementById("editRecordType").value
    const subdomainId = document.getElementById("editRecordSubdomainId").value
    const value = document.getElementById("editRecordValue").value.trim()

    // Format check, matches the server-side rule
    let message = getValueError(type, value)

    // Duplicate check against the data from /records/json, ignoring the record being edited
    if (!message && editExistingRecords.some(record =>
        record.id !== editingRecordId && record.key === recordKey(subdomainId, type, value))) {
        message = "This record already exists"
    }

    editValueValid = showFieldError("editRecordValueError", message)

    updateEditSubmitButton()
}

// Function to validate the TTL
function validateEditTtl() {
    const ttl = document.getElementById("editRecordTtl").value

    editTtlValid = showFieldError("editRecordTtlError", getTtlError(ttl))

    updateEditSubmitButton()
}

// Function to validate the priority
function validateEditPriority() {
    // Stores the fields needed
    const type = document.getElementById("editRecordType").value
    const priority = document.getElementById("editRecordPriority").value

    editPriorityValid = showFieldError("editRecordPriorityError", getPriorityError(type, priority))

    updateEditSubmitButton()
}

// Runs when the record type changes
function validateEditType() {
    toggleRecordFields("edit")

    // The value rule and the priority requirement both depend on the type
    validateEditValue()
    validateEditPriority()
}

// Loads the existing records so the duplicate check has data
async function loadEditRecords() {
    editExistingRecords = await fetchExistingRecords()
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("editRecordType").addEventListener("change", validateEditType)
    document.getElementById("editRecordValue").addEventListener("input", validateEditValue)
    document.getElementById("editRecordTtl").addEventListener("input", validateEditTtl)
    document.getElementById("editRecordPriority").addEventListener("input", validateEditPriority)

    loadEditRecords()
})