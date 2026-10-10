// Icon and colour for each toast type
const toastTypes = {
    success: { icon: 'circle-check',   color: 'text-green-600' },
    error:   { icon: 'circle-x',       color: 'text-red-600' },
    warning: { icon: 'triangle-alert', color: 'text-amber-500' },
    info:    { icon: 'info',           color: 'text-sky-600' },
    added:   { icon: 'circle-plus',    color: 'text-green-600' },
    updated: { icon: 'pencil',         color: 'text-sky-600' },
    deleted: { icon: 'trash-2',        color: 'text-red-600' },
}

function showToast(type, title, message = '', duration = 5000) {
    // Gets the container and the style for this type
    const container = document.getElementById('toast-container')
    if (!container) return
    const style = toastTypes[type] || toastTypes.info

    // Builds the toast
    const toast = document.createElement('div')
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status')
    toast.className = 'pointer-events-auto flex items-start gap-3 rounded-xl border border-slate-200/80 bg-white p-4 shadow-xl transition duration-200 opacity-0 translate-x-4'
    toast.innerHTML = `
        <i width="20" height="20" class="shrink-0 ${style.color}" data-lucide="${style.icon}"></i>
        <div class="min-w-0 flex-1">
            <p class="toast-title text-sm font-semibold text-gray-900"></p>
            <p class="toast-message text-sm text-gray-600"></p>
        </div>
        <button type="button" class="toast-close text-gray-400 hover:text-gray-600">
            <i width="20" height="20" class="shrink-0" data-lucide="x"></i>
        </button>
    `

    // Sets the text safely (no HTML injection)
    toast.querySelector('.toast-title').textContent = title
    const msg = toast.querySelector('.toast-message')
    msg.textContent = message
    if (!message) msg.remove()

    // Closes on click of the X
    toast.querySelector('.toast-close').onclick = () => closeToast(toast)

    // Adds it to the screen, then fades it in
    container.appendChild(toast)
    lucide.createIcons()
    requestAnimationFrame(() => toast.classList.remove('opacity-0', 'translate-x-4'))

    // Removes it after the duration
    if (duration > 0) setTimeout(() => closeToast(toast), duration)
}

function closeToast(toast) {
    // Fades out then removes it
    toast.classList.add('opacity-0', 'translate-x-4')
    setTimeout(() => toast.remove(), 200)
}