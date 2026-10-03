function openModal(modalId) {
    // Gets the modal by its id
    const modal = document.getElementById(modalId)

    // Checks if the modal does not exist
    if (!modal) return

    // Reveals the modal
    modal.classList.remove("hidden")
    modal.classList.add("flex")

    // Stops scrolling
    document.body.style.overflow = 'hidden'
}

function closeModal(modalId) {
    // Gets the modal by id
    const modal = document.getElementById(modalId);
    
    // Checks if the modal exists
    if (!modal) return;

    // Hides the modal
    modal.classList.remove('flex');
    modal.classList.add('hidden');

    // Allows the scrolling again
    document.body.style.overflow = 'unset';
}

// Global Escape Key Listener
document.addEventListener('keydown', (e) => {
    // Checks if the key is escape
    if (e.key === 'Escape') {
        // Closes the visible modals
        const visibleModals = document.querySelectorAll('.modal-backdrop:not(.hidden)');
        visibleModals.forEach(modal => closeModal(modal.id));
    }
});