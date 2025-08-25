import { isObject, omit } from 'lodash-es'
/*
* Gather selected items in a selected object (currently used for medias and browsers)
* if a block is passed as second argument, we retrieve selected items namespaced by the block id
* and strip it out from the key to clean things up and make it easier for the backend
*/
export const redirector = (data, timeout = 1000) => {
  if (Object.prototype.hasOwnProperty.call(data, 'redirector')) {
    timeout = Object.prototype.hasOwnProperty.call(data, 'timeout') ? data.timeout : timeout
    setTimeout(function (data) {
      window.open(
        data.redirector,
        Object.prototype.hasOwnProperty.call(data, 'target') ? data.target : '_self'
      )
    }, timeout, data)
    // window.location.replace(resp.data.redirect)
  }
}

export const handleSuccessResponse = (response) => {
  if (response.status === 200) {
    // open temporary modal
    if (response.data.modalService && isObject(response.data.modalService)) {
      window.$modalService.open(response.data.modalService.component ?? null, omit(response.data.modalService, ['component']))
    }
  }
}

export const handleErrorResponse = (error) => {
  if (error.response?.status === 403) {
    window.$modalService.handleObject(error.response.data)
  }
  redirector(error.response.data, 1000)
}
