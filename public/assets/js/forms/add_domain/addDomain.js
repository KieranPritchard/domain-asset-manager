// Function to validate the domain Name
function validateDomainName(){
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const domainNameField = document.getElementById("domainNameField").value
    const domainNameError = document.getElementById("domainNameError")

    // Performs a length check
    if (domainNameField.length === 0) {
        domainNameError.innerText = "Please enter a username"
        domainNameError.classList.remove("hidden")

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

// Function to validate the registar
function validateRegistarName(){
    // Stores the fields needed
    const submitButton = document.getElementById("submitBtn")
    const registarField = document.getElementById("registarField").value
    const registarError = document.getElementById("registarField")

    // Performs a length check
    if (registarField.length === 0) {
        registarError.innerText = "Please enter a username"
        registarError.classList.remove("hidden")

        // Adds the disabled attribute to the button
        submitButton.setAttribute("disabled", "")

        // Removes the curser pointer and adds the disabled one
        submitButton.classList.remove("cursor-pointer")
        submitButton.classList.add("cursor-not-allowed")
    } else {
        // Resets everything
        registarError.classList.add("hidden")
        submitButton.removeAttribute("disabled")
        submitButton.classList.add("cursor-pointer")
        submitButton.classList.remove("cursor-not-allowed")
    }
}