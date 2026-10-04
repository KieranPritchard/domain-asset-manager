// Tracks whether each field is currently valid
let usernameValid = false
let passwordValid = false

// Enables or disables the submit button based on both fields being valid
function updateSubmitButton() {
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const enabled = usernameValid && passwordValid

    // Adds or removes the disabled attribute on the button
    submitButton.toggleAttribute("disabled", !enabled)

    // Swaps between the curser pointer and the disabled one
    submitButton.classList.toggle("cursor-pointer", enabled)
    submitButton.classList.toggle("cursor-not-allowed", !enabled)
}

// Function to handle the button submit
function handleLoginSubmit() {
    // Stores the submit button
    const submitButton = document.getElementById("submitBtn")

    // Adds the disabled attribute to the button
    submitButton.setAttribute("disabled", "")

    // Removes the curser pointer and adds the disabled one
    submitButton.classList.remove("cursor-pointer", "cursor-not-allowed")
    submitButton.classList.add("cursor-progress")

    // Sets the text to submitting
    submitButton.innerHTML = "Submitting..."
}

// Function to validate the username
function validateUsernameField(){
    // Stores the fields needed
    const usernameField = document.getElementById("usernameField").value.trim()
    const usernameError = document.getElementById("usernameError")

    // Performs a length check
    if (usernameField.length === 0) {
        usernameError.innerText = "Please enter a username"
        usernameError.classList.remove("hidden")
        usernameValid = false
    } else {
        // Resets everything
        usernameError.classList.add("hidden")
        usernameValid = true
    }

    // Updates the submit button based on both fields
    updateSubmitButton()
}

// Function to validate the password
function validatePasswordField(){
    // Stores the fields needed
    const passwordField = document.getElementById("passwordField").value
    const passwordError = document.getElementById("passwordError")

    // Performs a length check
    if (passwordField.length === 0) {
        passwordError.innerText = "Passwords must not be empty"
        passwordError.classList.remove("hidden")
        passwordValid = false
    } else {
        // Resets everything
        passwordError.classList.add("hidden")
        passwordValid = true
    }

    // Updates the submit button based on both fields
    updateSubmitButton()
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("usernameField").addEventListener("input", validateUsernameField)
    document.getElementById("passwordField").addEventListener("input", validatePasswordField)

    // Sets the initial state from whatever is already in the fields (e.g. browser autofill)
    // without showing error messages before the user has typed anything
    usernameValid = document.getElementById("usernameField").value.trim().length > 0
    passwordValid = document.getElementById("passwordField").value.length > 0
    updateSubmitButton()
})