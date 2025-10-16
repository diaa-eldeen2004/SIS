// Shared modal component utility
export function openModal(id){
  const el = document.getElementById(id);
  if (el) el.classList.add('is-open');
}
export function closeModal(id){
  const el = document.getElementById(id);
  if (el) el.classList.remove('is-open');
}
