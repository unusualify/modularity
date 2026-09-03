/**
 * v-scrollable
 *
 * Marks a region as a scroll container (`ue-scrollable`).
 *
 * Without `.height`, the host fills its parent and clips overflow so nested
 * Vuetify cards can scroll their body (see `.ue-scrollable` CSS).
 *
 * With `.height`, sets an explicit height and `overflow-y: auto`.
 *
 * Usage:
 *   <div v-scrollable>…</div>
 *   <div v-scrollable.height="240">…</div>
 *   <div v-scrollable.height="'50vh'">…</div>
 *   <div v-scrollable="'100%'">…</div>
 */

const HEIGHT_PATTERN = /^\d+(\.\d+)?(px|em|rem|vh|%)$/

function isValidHeight (height) {
  return typeof height === 'number'
    || (typeof height === 'string' && HEIGHT_PATTERN.test(height))
}

function toCssHeight (height) {
  return typeof height === 'number' ? `${height}px` : height
}

function apply (el, binding) {
  el.classList.add('ue-scrollable')
  el.style.minHeight = '0'

  const height = binding.modifiers.height ? binding.value : (
    isValidHeight(binding.value) ? binding.value : null
  )

  if (height == null) {
    return
  }

  if (! isValidHeight(height)) {
    console.warn('v-scrollable: Invalid height value. Please provide a number (for px) or a valid CSS height value.')
    return
  }

  el.style.height = toCssHeight(height)
  el.style.overflowY = 'auto'
}

const dir = {
  beforeMount (el, binding) {
    apply(el, binding)
  },
  updated (el, binding) {
    if (binding.value === binding.oldValue) {
      return
    }
    apply(el, binding)
  },
}

export default {
  install (app) {
    app.directive('scrollable', dir)
  },
  directive: dir,
}
