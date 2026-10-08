// Fills the delete modal with the clicked row's data, then opens it
function openDeleteSubdomainModal(button) {
    document.getElementById("deleteSubdomainId").value = button.dataset.id

    // textContent (not innerHTML) so a subdomain name can never inject markup
    document.getElementById("deleteSubdomainName").textContent = button.dataset.name

    openModal("deleteSubdomainModal")
}