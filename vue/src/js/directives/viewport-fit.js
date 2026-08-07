/**
 * v-viewport-fit
 *
 * Fits the bound element to leftover viewport space below whatever sits above it
 * (breadcrumbs, alerts, filters, margins, …).
 *
 * Measurement uses the element's own getBoundingClientRect().top so collapsed
 * margins between chrome and the fill target are included. Chrome is only used
 * as a ResizeObserver target for reflow.
 *
 * Usage:
 *   <div class="chrome">…</div>
 *   <div
 *     v-viewport-fit="{ chrome: '.chrome', heightRef: tableHeight, offset: 16, min: 240 }"
 *   >
 *     <v-data-table :height="tableHeight" fixed-header />
 *   </div>
 *
 * Binding:
 *   chrome     selector | Element | Ref — region to observe for size changes
 *              (default: previousElementSibling)
 *   offset     bottom gap px (default 16)
 *   min        minimum height px (default 240)
 *   heightRef  Ref<number> for Vuetify `:height`
 *   onUpdate   (height: number) => void
 *   applyStyle set el.style height (default true)
 *
 * Modifiers: .style (force style), .css-var (--viewport-fit-height)
 */

const STATE_KEY = '__viewportFit'

function isElement (node) {
  return typeof Element !== 'undefined' && node instanceof Element
}

function resolveElement (raw, el) {
  const value = typeof raw === 'function' ? raw() : raw
  if (!value) {
    return null
  }
  if (isElement(value)) {
    return value
  }
  if (typeof value === 'object' && 'value' in value) {
    return resolveElement(value.value, el)
  }
  if (typeof value === 'string') {
    const scope = el?.closest?.('[data-viewport-fit-root]')
      || el?.parentElement
      || document
    try {
      return scope.querySelector(value) || document.querySelector(value)
    } catch {
      return null
    }
  }
  if (value?.$el && isElement(value.$el)) {
    return value.$el
  }
  return null
}

function resolveChromeElement (el, opts) {
  const fromOpt = resolveElement(opts.chrome, el)
  if (fromOpt) {
    return fromOpt
  }

  let node = el
  while (node) {
    if (node.previousElementSibling) {
      return node.previousElementSibling
    }
    node = node.parentElement
    if (!node || node === document.body || node.id === 'ue-main-body') {
      break
    }
  }
  return null
}

function getViewportBottom (el) {
  let node = el.parentElement
  while (node && node !== document.documentElement) {
    const style = window.getComputedStyle(node)
    const oy = style.overflowY
    const scrollable = oy === 'auto' || oy === 'scroll' || oy === 'overlay'
    if (scrollable && node.clientHeight > 0) {
      return node.getBoundingClientRect().bottom
    }
    node = node.parentElement
  }

  return window.visualViewport?.height ?? window.innerHeight
}

function normalizeOptions (bindingValue) {
  if (bindingValue == null || bindingValue === false) {
    return null
  }
  if (typeof bindingValue === 'number') {
    return { offset: bindingValue }
  }
  if (typeof bindingValue === 'string') {
    return { chrome: bindingValue }
  }
  if (typeof bindingValue === 'object') {
    return { ...bindingValue }
  }
  return {}
}

function writeHeightRef (heightRef, height) {
  if (!heightRef) {
    return
  }
  if (typeof heightRef === 'object' && 'value' in heightRef) {
    if (heightRef.value !== height) {
      heightRef.value = height
    }
  }
}

function apply (el, binding) {
  const opts = normalizeOptions(binding.value)
  if (opts === null) {
    return
  }

  const offset = Number(opts.offset ?? 16)
  const min = Number(opts.min ?? 240)

  // Use the fill element's top — includes margins below chrome automatically
  const top = el.getBoundingClientRect().top
  const bottom = getViewportBottom(el)
  const height = Math.max(min, Math.floor(bottom - top - offset))

  const skipStyle = opts.applyStyle === false && !binding.modifiers.style
  if (!skipStyle) {
    el.style.setProperty('height', `${height}px`, 'important')
    el.style.setProperty('max-height', `${height}px`, 'important')
    el.style.overflow = 'hidden'
    el.style.boxSizing = 'border-box'
  }

  if (binding.modifiers['css-var'] || opts.cssVar) {
    const varName = typeof opts.cssVar === 'string' ? opts.cssVar : '--viewport-fit-height'
    el.style.setProperty(varName, `${height}px`)
  }

  writeHeightRef(opts.heightRef, height)

  if (typeof opts.onUpdate === 'function') {
    opts.onUpdate(height)
  }
}

function scheduleApply (el, binding) {
  requestAnimationFrame(() => {
    requestAnimationFrame(() => apply(el, binding))
  })
}

function bind (el, binding) {
  unbind(el)

  const state = {
    binding,
    onResize: () => scheduleApply(el, el[STATE_KEY]?.binding ?? binding),
    chromeObserver: null,
    parentObserver: null,
  }

  window.addEventListener('resize', state.onResize)
  window.visualViewport?.addEventListener('resize', state.onResize)

  const opts = normalizeOptions(binding.value) || {}
  const chromeEl = resolveChromeElement(el, opts)

  if (typeof ResizeObserver !== 'undefined') {
    state.chromeObserver = new ResizeObserver(() => scheduleApply(el, el[STATE_KEY]?.binding ?? binding))
    if (chromeEl) {
      state.chromeObserver.observe(chromeEl)
    }
    // Observe root so alert/filter toggles reflow even if chrome selector misses
    const root = el.closest?.('[data-viewport-fit-root]') || el.parentElement
    if (root) {
      state.chromeObserver.observe(root)
    }
  }

  el[STATE_KEY] = state
  scheduleApply(el, binding)
}

function unbind (el) {
  const state = el[STATE_KEY]
  if (!state) {
    return
  }
  window.removeEventListener('resize', state.onResize)
  window.visualViewport?.removeEventListener('resize', state.onResize)
  state.chromeObserver?.disconnect()
  state.parentObserver?.disconnect()
  delete el[STATE_KEY]
}

const dir = {
  mounted (el, binding) {
    bind(el, binding)
  },
  updated (el, binding) {
    const state = el[STATE_KEY]
    if (!state) {
      bind(el, binding)
      return
    }
    state.binding = binding
    scheduleApply(el, binding)
  },
  unmounted (el) {
    unbind(el)
  },
}

export default {
  install (app) {
    app.directive('viewport-fit', dir)
  },
  directive: dir,
}
