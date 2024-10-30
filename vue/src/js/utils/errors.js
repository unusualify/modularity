export function globalError (component = null, error = { message: '', value: null }) {
  let prefix = ''

  if (component && typeof component === 'string') {
    prefix = `${import.meta.env.VUE_APP_NAME} - [${component}]: `
  }

  const errorMessage = prefix + error.message

  const statusCode = error?.value?.response?.status ?? error?.response?.status ?? null

  console.error(errorMessage)

  if (error?.value && error.value?.response && error.value.response?.data) {
    console.error(error.value.response.data)
  }

  // Error 401 = session expired / not authenticated
  // Error 419 = CSRF token mismatched
  if (statusCode === 401 || statusCode === 419) {
    // window.vm.config.globalProperties.$notif({
    //   message: 'Your session has expired, please <a href="' + document.location + '" target="_blank">login in another tab</a>. You can then continue working here.',
    //   variant: 'warning'
    // })
    window.vm.config.globalProperties.$dialog({
      message: 'Your session has expired, please <a class="v-btn v-btn--slim text-primary v-btn--density-default v-btn--size-default mr-12" href="'
        + document.location
        + '" target="_blank">login in another tab</a>',
    })
  } else {
    window.vm.config.globalProperties.$notif({
      message: error.message,
      variant: 'error'
    })
  }
}

export function globalError_ (component = null, error = { message: '', value: null }) {
  let prefix = ''

  if (component && typeof component === 'string') {
    prefix = `${import.meta.env.VUE_APP_NAME} - [${component}]: `
  }

  const errorMessage = prefix + error.message

  const statusCode = error?.value?.response?.status ?? error?.response?.status ?? null

  console.error(errorMessage)

  if (error?.value && error.value?.response) {
    console.error(error.value.response?.data)
  }

  // Error 401 = session expired / not authenticated
  // Error 419 = CSRF token mismatched
  if (statusCode === 401 || statusCode === 419) {
    window[import.meta.env.VUE_APP_NAME].vm.notif({
      message: 'Your session has expired, please <a href="' + document.location + '" target="_blank">login in another tab</a>. You can then continue working here.',
      variant: 'warning'
    })
  }
}
