
export default {
  install (app, opts = {}) {
    const defaultColumns = {
      cols: 12
      // xxl: 4,
      // xl: 4,
      // lg: 6,
      // md: 8,
      // sm: 12,
      // xs: 12
    }
    const dir = {
      // called before bound element's attributes
      // or event listeners are applied
      created (el, binding, vnode, prevVnode) {
        const Columns = binding.value
        const vm = binding.instance
        __log(vnode, el)
        // vnode.props[key] = value
      },
      // called right before the element is inserted into the DOM.
      beforeMount (el, binding, vnode, prevVnode) {
        const Columns = binding.value
        __log(binding.arg)
        if (!Columns) {
          Object.entries(defaultColumns).forEach(([key, value]) => {
            el.setAttribute(key, value)
          })
          // el.setAttribute('md', '6')
        } else if (__isObject(Columns)) {
          // Set the column information as attributes
          Object.entries(Columns).forEach(([key, value]) => {
            el.setAttribute(key, value)
          })
        }
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
    app.directive('column', dir)
  }
}
