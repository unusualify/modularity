// POST logout action

function disconnectBroadcasting () {
  if (typeof window.disconnectEcho === 'function') {
    window.disconnectEcho()
    return
  }

  try {
    window.Echo?.disconnect?.()
  } catch (_) {
    // ignore
  }
}

const logoutButton = function () {
  const logoutForm = document.querySelector('[data-logout-form]')

  if (!logoutForm) return

  document.body.addEventListener('click', e => {
    if (e.target.hasAttribute('data-logout-btn')) {
      e.preventDefault()
      disconnectBroadcasting()
      logoutForm.submit()
    }
  })
}

export default logoutButton
