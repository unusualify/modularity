export default {
  install(app, opts = {}) {
    const dir = {
      beforeMount(el, binding) {
        // Default transition type if none specified
        const type = binding.arg || 'scale'
        // Get transition duration from modifier or use default
        const duration = binding.modifiers?.slow ? '0.3s' : '0.2s'

        // Add base transition class
        el.classList.add(`${type}-transition`)
      },

      mounted(el, binding) {
        const type = binding.arg || 'scale'

        // Set initial state
        if (!el.classList.contains('d-none')) {
          el.classList.add(`${type}-1`)
        }

        // Create mutation observer to watch for d-none class changes
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
              const hasDNone = el.classList.contains('d-none')
              // Handle transition when d-none is added/removed
              if (hasDNone) {
                __log('observer', hasDNone, el)
                el.classList.remove(`${type}-1`)
              } else {
                // Small delay to ensure d-none is fully removed
                setTimeout(() => {
                  el.classList.add(`${type}-1`)
                }, 50)
              }
            }
          })
        })

        // Store observer in element to cleanup later
        el._transitionObserver = observer

        // Start observing the element
        observer.observe(el, {
          attributes: true,
          attributeFilter: ['class']
        })
      },

      updated(el, binding) {
        // Handle any dynamic updates to the binding if needed
      },

      unmounted(el) {
        // Cleanup the observer when component is unmounted
        if (el._transitionObserver) {
          el._transitionObserver.disconnect()
          delete el._transitionObserver
        }
      }
    }

    // Register the directive as 'transition'
    app.directive('transition', dir)
  }
}
