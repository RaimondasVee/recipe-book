// Ingredient Modal
const ingredientModal = document.getElementById('ingredientDeleteModal')
ingredientModal.addEventListener('show.bs.modal', event => {
  console.log('ingredientModal')

  const button = event.relatedTarget

  const name = button.getAttribute('data-bs-name')
  const qty = button.getAttribute('data-bs-qty')
  const unit = button.getAttribute('data-bs-unit')
  const path = button.getAttribute('data-bs-path')

  const modalTitle = ingredientModal.querySelector('.modal-title')
  const modalConfirm = ingredientModal.querySelector('#deleteIngredientConfirm')

  modalConfirm.href = path
  modalTitle.textContent = `Deleting Ingredient: ${qty} ${unit} of ${name} `
})

// Step Modal
const stepModal = document.getElementById('stepManagerModal')
stepModal.addEventListener('show.bs.modal', event => {
  console.log('stepModal')
  // Button that triggered the modal
  const button = event.relatedTarget
  // Extract info from data-bs-* attributes
  const id = button.getAttribute('data-bs-id')
  const step = button.getAttribute('data-bs-step')
  const text = button.getAttribute('data-bs-text')
  const deleteStep = button.getAttribute('data-bs-delete-path')
  // If necessary, you could initiate an AJAX request here
  // and then do the updating in a callback.
  //
  // Update the modal's content.
  const modalTitle = stepModal.querySelector('.modal-title')
  const textarea = stepModal.querySelector('.text')

  textarea.textContent = text
  const modalConfirm = stepModal.querySelector('#deleteStepConfirm')
  modalConfirm.href = deleteStep
  // const modalBodyInput = stepModal.querySelector('.modal-body input')

  modalTitle.textContent = `Editing Step: ${step}`
  // modalBodyInput.value = recipient
})