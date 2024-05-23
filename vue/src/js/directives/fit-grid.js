
export default {
  install (app, opts = {}) {
    const dir = {
      // called before bound element's attributes
      // or event listeners are applied
      created (el, binding, vnode, prevVnode) {

      },
      // called right before the element is inserted into the DOM.
      beforeMount (el, binding, vnode, prevVnode) {
        // __log(
        //   el
        // )
        el.classList.add('d-flex')

        el.firstElementChild.classList.add('h-100', 'w-100')
      },
      // called when the bound element's parent component
      // and all its children are mounted.
      mounted (el, binding, vnode, prevVnode) {

      },
      // called before the parent component is updated
      beforeUpdate (el, binding, vnode, prevVnode) {},
      // called after the parent component and
      // all of its children have updated
      updated (el, binding, vnode, prevVnode) {},
      // called before the parent component is unmounted
      beforeUnmount (el, binding, vnode, prevVnode) {},
      // called when the parent component is unmounted
      unmounted (el, binding, vnode, prevVnode) {}
    }
    app.directive('fit-grid', dir)
  }
}
