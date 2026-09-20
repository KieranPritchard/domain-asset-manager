// Function to handle the button submit
function handleLoginSubmit() {
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
    }

    // Resets everything
    usernameError.classList.add("hidden")
    submitButton.removeAttribute("disabled")
    submitButton.classList.add("cursor-pointer")
    submitButton.classList.remove("cursor-not-allowed")
}