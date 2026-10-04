// Fills the delete modal with the clicked row's data, then opens it
function openDeleteDomainModal(button) {
    document.getElementById("deleteDomainId").value = button.dataset.id

    // textContent (not innerHTML) so a domain name can never inject markup
    document.getElementById("deleteDomainName").textContent = button.dataset.name

    openModal("deleteDomainModal")
}