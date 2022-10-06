// Ingredient Modal
const ingredientModal = document.getElementById('ingredientDeleteModal')
ingredientModal.addEventListener('show.bs.modal', event => {
  console.log('ingredient modal')
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
  if (event.relatedTarget.id == 'returnButton') {
    return
  }
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
  const recButton = stepModal.querySelector('#recDataButton')

  //Build recommendations
  recButton.setAttribute('data-rec-id', recipeId)
  recButton.setAttribute('data-step-id', id)
  recButton.setAttribute('data-rec-0-id', button.getAttribute('data-bs-rec-0-id'))
  recButton.setAttribute('data-rec-0-text', button.getAttribute('data-bs-rec-0-text'))
  recButton.setAttribute('data-rec-1-id', button.getAttribute('data-bs-rec-1-id'))
  recButton.setAttribute('data-rec-1-text', button.getAttribute('data-bs-rec-1-text'))
  recButton.setAttribute('data-rec-2-id', button.getAttribute('data-bs-rec-2-id'))
  recButton.setAttribute('data-rec-2-text', button.getAttribute('data-bs-rec-2-text'))

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

// Step Modal
const recModal = document.getElementById('recManagerModal')
recModal.addEventListener('show.bs.modal', event => {
  // Button that triggered the modal
  const button = event.relatedTarget

  const recipeId = button.getAttribute('data-rec-id')
  const stepId = button.getAttribute('data-step-id')
  const recId = button.getAttribute('data-rec-0-id')

  const textarea = recModal.querySelector('#stepRecEditText')
  const form = recModal.querySelector('#stepRecEdit')

  form.setAttribute('action', `/recipe/show/${recipeId}/steps/update/${stepId}/rec/${recId}`)
  textarea.textContent = button.getAttribute('data-rec-0-text')

})