// Toast notification utility
export function showToast(message, timeout=2000){
  let container = document.querySelector('.toast-container');
  if (!container){
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => { toast.remove(); }, timeout);
}
