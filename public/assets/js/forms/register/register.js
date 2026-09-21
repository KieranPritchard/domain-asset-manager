// Function to handle the button submit
function handleRegisterSubmit() {
    // Stores the submit button
    const submitButton = document.getElementById("submitBtn")

    // Adds the disabled attribute to the button
    submitButton.setAttribute("disabled", "")

    // Removes the curser pointer and adds the disabled one
    submitButton.classList.remove("cursor-pointer")
    submitButton.classList.add("cursor-progress")

    // Sets the text to submitting
    submitButton.innerHTML = "Submitting..."
}

// Function to validate the username
function validateUsernameField(){
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const usernameField = document.getElementById("usernameField").value
    const usernameError = document.getElementById("usernameError")

    // Performs a length check
    if (usernameField.length === 0) {
        usernameError.innerText = "Please enter a username"
        usernameError.classList.remove("hidden")

        // Adds the disabled attribute to the button
        submitButton.setAttribute("disabled", "")

        // Removes the curser pointer and adds the disabled one
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")
    } else {
        // Resets everything
        usernameError.classList.add("hidden")
        submitButton.removeAttribute("disabled")
        submitButton.classList.add("cursor-pointer")
        submitButton.classList.remove("cursor-not-allowed")
    }
}

// Function to validate the username
function validatePasswordField(){
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const passwordField = document.getElementById("passwordField").value
    const passwordError = document.getElementById("passwordError")

    // Password checks
    const hasNumber = /\d/.test(passwordField)
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(passwordField)

    // Performs a length check
    if (passwordField.length < 8) {
        passwordError.innerText = "Passwords must be at least 8 characters"
        passwordError.classList.remove("hidden")

        // Adds the disabled attribute to the button
        submitButton.setAttribute("disabled", "")

        // Removes the curser pointer and adds the disabled one
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")
    } else if (!hasNumber) {
        passwordError.innerText = "Password must include at least one number"
        passwordError.classList.remove("hidden")

        // Adds the disabled attribute to the button
        submitButton.setAttribute("disabled", "")

        // Removes the curser pointer and adds the disabled one
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")
    } else if (!hasSpecialChar) {
        passwordError.innerText = "Password must include at least one special character"
        passwordError.classList.remove("hidden")

        // Adds the disabled attribute to the button
        submitButton.setAttribute("disabled", "")

        // Removes the curser pointer and adds the disabled one
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")
    } else {
        // Resets everything
        passwordError.classList.add("hidden")
        submitButton.removeAttribute("disabled")
        submitButton.classList.add("cursor-pointer")
        submitButton.classList.remove("cursor-not-allowed")
    }
}

// Function to validate the password
function validatePasswordField(){
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const passwordField = document.getElementById("passwordField").value
    const passwordError = document.getElementById("passwordError")

    // Password checks
    const hasNumber = /\d/.test(passwordField)
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(passwordField)

    // Performs a length check
    if (passwordField.length < 8) {
        passwordError.innerText = "Passwords must be at least 8 characters"
        passwordError.classList.remove("hidden")

        // Adds the disabled attribute to the button
        submitButton.setAttribute("disabled", "")

        // Removes the curser pointer and adds the disabled one
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")

        return false
    } else if (!hasNumber) {
        passwordError.innerText = "Password must include at least one number"
        passwordError.classList.remove("hidden")

        submitButton.setAttribute("disabled", "")
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")

        return false
    } else if (!hasSpecialChar) {
        passwordError.innerText = "Password must include at least one special character"
        passwordError.classList.remove("hidden")

        submitButton.setAttribute("disabled", "")
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")

        return false
    } else {
        // Resets everything
        passwordError.classList.add("hidden")
        submitButton.removeAttribute("disabled")
        submitButton.classList.add("cursor-pointer")
        submitButton.classList.remove("cursor-not-allowed")

        return true
    }
}

// Function to validate the confirm password field
function validateConfirmPasswordField(){
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const passwordField = document.getElementById("passwordField").value
    const confirmPasswordField = document.getElementById("confirmPasswordField").value
    const confirmPasswordError = document.getElementById("confirmPasswordError")

    // Reuses the password field's own checks first
    const passwordIsValid = validatePasswordField()

    // If the password itself already failed, don't bother checking the match —
    // validatePasswordField() has already set the error message and disabled the button
    if (!passwordIsValid) {
        return
    }

    // Performs the match check
    if (passwordField !== confirmPasswordField) {
        confirmPasswordError.innerText = "Passwords do not match"
        confirmPasswordError.classList.remove("hidden")

        submitButton.setAttribute("disabled", "")
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")
    } else {
        // Resets everything
        confirmPasswordError.classList.add("hidden")
        submitButton.removeAttribute("disabled")
        submitButton.classList.add("cursor-pointer")
        submitButton.classList.remove("cursor-not-allowed")
    }
}