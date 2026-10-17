import { openModal } from '../components/modal';

// Helper function to display upload errors in the modal
export function showUploadError(message) {
    const errorMessage = document.getElementById("errorMessage");
    if (errorMessage) {
        errorMessage.innerHTML = message;
    }
    openModal("errorModal");
}