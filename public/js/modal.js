// Ingredient Modal
const ingredientModal = document.getElementById('ingredientDeleteModal')
ingredientModal.addEventListener('show.bs.modal', event => {
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
  // Button that triggered the modal
  const button = event.relatedTarget

  const id = button.getAttribute('data-bs-id')
  const step = button.getAttribute('data-bs-step')
  const text = button.getAttribute('data-bs-text')
  const recipeId = button.getAttribute('data-bs-recipe')
  const last = button.getAttribute('data-bs-length')

  const modalTitle = stepModal.querySelector('.modal-title')
  const textarea = stepModal.querySelector('.text')
  const formSave = stepModal.querySelector('#stepEdit')
  const buttonDelete = stepModal.querySelector('#deleteStepConfirm')
  const buttonUp = stepModal.querySelector('#moveUp')
  const buttonDown = stepModal.querySelector('#moveDown')

  modalTitle.textContent = `Editing Step: ${step}`
  textarea.textContent = text
  formSave.setAttribute('action', `/recipe/show/${recipeId}/steps/update/${id}`)
  buttonDelete.href = `/recipe/show/${recipeId}/steps/delete/${id}`
  buttonUp.setAttribute('onclick',`window.location.href='/recipe/show/${recipeId}/steps/order/${id}/up'`)
  buttonDown.setAttribute('onclick',`window.location.href='/recipe/show/${recipeId}/steps/order/${id}/down'`)

  if (step == 1) {
    buttonUp.classList.add("disabled")
  } else {
    buttonUp.classList.remove("disabled")
  }

  if (step == last) {
    buttonDown.classList.add("disabled")
  } else {
    buttonDown.classList.remove("disabled")
  }
})