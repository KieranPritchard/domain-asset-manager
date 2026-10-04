// Tracks whether each field is currently valid
let usernameValid = false
let passwordValid = false
let confirmPasswordValid = false

// Enables or disables the submit button based on all fields being valid
function updateSubmitButton() {
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const enabled = usernameValid && passwordValid && confirmPasswordValid

    // Adds or removes the disabled attribute on the button
    submitButton.toggleAttribute("disabled", !enabled)

    // Swaps between the curser pointer and the disabled one
    submitButton.classList.toggle("cursor-pointer", enabled)
    submitButton.classList.toggle("cursor-not-allowed", !enabled)
}

// Returns the first password problem as a message, or an empty string if it's fine
function getPasswordError(password) {
    // Password checks
    const hasNumber = /\d/.test(password)
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password)

    // Performs a length check
    if (password.length < 8) return "Passwords must be at least 8 characters"
    if (!hasNumber) return "Password must include at least one number"
    if (!hasSpecialChar) return "Password must include at least one special character"
    return ""
}

// Function to handle the button submit
function handleRegisterSubmit() {
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

    // Updates the submit button based on all fields
    updateSubmitButton()
}

// Function to validate the password
function validatePasswordField(){
    // Stores the fields needed
    const passwordField = document.getElementById("passwordField").value
    const passwordError = document.getElementById("passwordError")

    // Runs the password checks
    const message = getPasswordError(passwordField)

    if (message) {
        passwordError.innerText = message
        passwordError.classList.remove("hidden")
        passwordValid = false
    } else {
        // Resets everything
        passwordError.classList.add("hidden")
        passwordValid = true
    }

    // Re-checks the match if the confirm field already has something in it,
    // so changing the password can't leave a stale "match" result behind
    if (document.getElementById("confirmPasswordField").value.length > 0) {
        validateConfirmPasswordField()
    } else {
        updateSubmitButton()
    }
}

// Function to validate the confirm password field
function validateConfirmPasswordField(){
    // Stores the fields needed
    const passwordField = document.getElementById("passwordField").value
    const confirmPasswordField = document.getElementById("confirmPasswordField").value
    const confirmPasswordError = document.getElementById("confirmPasswordError")

    // Performs the match check
    if (passwordField !== confirmPasswordField) {
        confirmPasswordError.innerText = "Passwords do not match"
        confirmPasswordError.classList.remove("hidden")
        confirmPasswordValid = false
    } else {
        // Resets everything
        confirmPasswordError.classList.add("hidden")

        // An empty confirm field only counts as valid once something is typed
        confirmPasswordValid = confirmPasswordField.length > 0
    }

    // Updates the submit button based on all fields
    updateSubmitButton()
}

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("usernameField").addEventListener("input", validateUsernameField)
    document.getElementById("passwordField").addEventListener("input", validatePasswordField)
    document.getElementById("confirmPasswordField").addEventListener("input", validateConfirmPasswordField)

    // Sets the initial state from whatever is already in the fields (e.g. browser autofill)
    // without showing error messages before the user has typed anything
    const password = document.getElementById("passwordField").value
    const confirmPassword = document.getElementById("confirmPasswordField").value
    usernameValid = document.getElementById("usernameField").value.trim().length > 0
    passwordValid = getPasswordError(password) === ""
    confirmPasswordValid = confirmPassword.length > 0 && password === confirmPassword
    updateSubmitButton()
})