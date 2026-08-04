// SVGs
// Should output : '<span class="icon icon--${id}"><svg><title>id</title><use xlink:href="#icon--${id}"></use></svg></span>';
// <svg class="icon icon--${id}"><title>id</title><use xlink:href="#icon--${id}"></use></svg> if node is already a svg
import { isString } from 'lodash-es'
import { useConfig } from '@/hooks'
import store from '@/store'

export function addSvg (el, binding, vnode) {
  const classNames = ['icon']
  const id = binding.expression || vnode.props.symbol
  let svg = el
  // span or svg ?
  if (vnode.type === 'span') {
    // add SVG element
    svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
    el.appendChild(svg)
  }
  // add classes to wrapper
  classNames.push(`icon--${id}`)
  classNames.forEach(function (className) {
    el.classList.add(className)
  })

  // add title to SVGs
  const title = document.createElementNS('http://www.w3.org/2000/svg', 'title')

  title.textContent = id
  svg.appendChild(title)

  // Add the <use> element to <svg>
  const href = `#icon--${id}`
  const use = document.createElementNS('http://www.w3.org/2000/svg', 'use')

  use.setAttributeNS('http://www.w3.org/1999/xlink', 'href', href)
  svg.appendChild(use)
}

export function removeSvg (el) {
  const svg = el.querySelector('svg')

  // remove svg
  if (svg) svg.parentNode.removeChild(svg)

  // clean up classes
  const classNames = el.className.split(' ').filter(function (c) {
    return c.indexOf('icon') === 0
  })

  classNames.forEach(function (className) {
    el.classList.remove(className)
  })
}

// Helpers to detect where the sprite lives and check for symbol existence
export function isHotSvgMode () {
  const config = useConfig()

  console.log(config.isHot.value)

  // return config.isHot.value
  return !!document.getElementById('vite-plugin-svg-spritemap')
}

export function getSvgSpriteContainer () {
  return document.getElementById('vite-plugin-svg-spritemap') || document.querySelector('.svg-sprite') || document
}

export function normalizeSymbolId (symbol) {
  return symbol && symbol.startsWith('icon--') ? symbol : `icon--${symbol}`
}

export function svgSymbolExists (symbol) {
  const id = normalizeSymbolId(symbol)
  const root = getSvgSpriteContainer()

  // Prefer querying <symbol id="..."> within the sprite root
  const bySymbol = root.querySelector(`symbol[id="${id}"]`)
  if (bySymbol) return true

  // Fallback: an element with the id might be at document level
  return !!document.getElementById(id)
}

export function getSymbol(symbols) {
  const firstSymbol = symbols.findIndex(symbol => svgSymbolExists(symbol))

  if (firstSymbol !== -1) {
    return symbols[firstSymbol]
  }

  return null
}

export function getLocaleSymbol(symbol, fallback) {
  let fallbacks = Array.isArray(fallback) ? fallback : (isString(fallback) ? [fallback] : [])
  // Store-direct: safe outside setup(); do not call useLocale()/inject here.
  const locale = store?.state?.language?.active?.value ?? 'en'

  return getSymbol([`${symbol}-${locale}`, symbol, ...fallbacks])
}
