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