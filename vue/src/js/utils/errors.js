export function globalError (component = null, error = { message: '', value: null }) {
  __log(
    error,
    error.value
  )
  let prefix = ''

  if (component && typeof component === 'string') {
    prefix = `${process.env.VUE_APP_NAME} - [${component}]: `
  }

  const errorMessage = prefix + error.message

  const statusCode = error?.value?.response?.status ?? error?.response?.status ?? null

  console.error(errorMessage)

  if (error?.value && error.value?.response) {
    console.error(error.value.response?.data)
  }

  window.vm.config.globalProperties.$notif({
    message: 'dene',
    variant: 'warning'
  })

  // Error 401 = session expired / not authenticated
  // Error 419 = CSRF token mismatched
  if (statusCode === 401 || statusCode === 419) {
    window.vm.config.globalProperties.$notif({
      message: 'Your session has expired, please <a href="' + document.location + '" target="_blank">login in another tab</a>. You can then continue working here.',
      variant: 'warning'
    })
  }
}
