export default {
  install(app, opts = {}) {
    const dir = {
      created(el, binding, vnode, prevVnode) {
        // Initialize any necessary data
      },
      beforeMount(el, binding, vnode, prevVnode) {
        el.classList.add('ue-scrollable');

        // Check if a height modifier is provided
        console.log('here')
        if (binding.modifiers.height) {
          const height = binding.value;
          if (typeof height === 'number' || (typeof height === 'string' && height.match(/^\d+(\.\d+)?(px|em|rem|vh|%)$/))) {
            el.style.height = typeof height === 'number' ? `${height}px` : height;
            el.style.overflowY = 'auto';
          } else {
            console.warn('v-scrollable: Invalid height value. Please provide a number (for px) or a valid CSS height value.');
          }
        }
      },
      mounted(el, binding, vnode, prevVnode) {
        // Additional mounted logic if needed
      },
      updated(el, binding, vnode, prevVnode) {
        // Update height if the binding value changes
        if (binding.modifiers.height && binding.value !== binding.oldValue) {
          const height = binding.value;
          if (typeof height === 'number' || (typeof height === 'string' && height.match(/^\d+(\.\d+)?(px|em|rem|vh|%)$/))) {
            el.style.height = typeof height === 'number' ? `${height}px` : height;
          }
        }
      },
      beforeUpdate(el, binding, vnode, prevVnode) {},
      beforeUnmount(el, binding, vnode, prevVnode) {},
      unmounted(el, binding, vnode, prevVnode) {}
    };
    app.directive('scrollable', dir);
  }
};
