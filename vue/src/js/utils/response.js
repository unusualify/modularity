/*
* Gather selected items in a selected object (currently used for medias and browsers)
* if a block is passed as second argument, we retrieve selected items namespaced by the block id
* and strip it out from the key to clean things up and make it easier for the backend
*/
export const redirector = (data, timeout = 1000) => {
  if (Object.prototype.hasOwnProperty.call(data, 'redirector')) {
    setTimeout(function (data) {
      window.open(
        data.redirector,
        Object.prototype.hasOwnProperty.call(data, 'target') ? data.target : '_self'
      )
    }, Object.prototype.hasOwnProperty.call(data, 'timeout') ? data.timeout : timeout, data)
    // window.location.replace(resp.data.redirect)
  }
}
