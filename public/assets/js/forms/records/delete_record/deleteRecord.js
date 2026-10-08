// Fills the delete modal with the clicked row's data, then opens it
function openDeleteRecordModal(button) {
    const { id, type, value } = button.dataset

    document.getElementById("deleteRecordId").value = id

    // textContent (not innerHTML) so a record value can never inject markup
    document.getElementById("deleteRecordName").textContent = `${type} ${value}`

    openModal("deleteRecordModal")
}