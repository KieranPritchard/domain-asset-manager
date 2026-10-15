// Creates an async function to get the user data
async function checkUploadErrors() {
    try {
        // Stores the fetched response
        const response = await fetch("/upload");
        
        // Handle HTTP response errors (e.g., 404, 500)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Stores the data
        const data = await response.json();

        // Checks if no files have been provided yet on initial load
        if (data.status === "idle") {
            return;
        }

        // Check the data structure returned by the PHP script
        if (data.status === "error" || data.error) {
            console.error("Upload error detected:", data.message || data.error);
            
            // Displays the modal
            const errorMessage = document.getElementById("errorMessage");

            // Sets the message
            errorMessage.innerHTML = "Upload error detected: " + (data.message || data.error);

            openModal("errorModal");
            return;
        }

        // Output the loaded data cleanly as an object
        console.log("Loaded data:", data);

        // Redirects to another page
        window.location.replace("overview.php");
    } catch (error) {
        console.error("Failed to load data:", error);

        // Displays the modal
        const errorMessage = document.getElementById("errorMessage");

        // Sets the message
        errorMessage.innerHTML = "Failed to load data: " + error.message;

        openModal("errorModal");
    }
}

// Executes the error check function
checkUploadErrors();