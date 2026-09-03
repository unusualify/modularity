import { describe, expect, test } from 'vitest'
import {
  isDirectiveDescriptor,
  isEnabledDirectiveValue,
  normalizeDirectiveBinding,
  parseDirectiveKey,
} from '../../src/js/utils/parseDirectiveBindings.js'

describe('parseDirectiveBindings', () => {
  test('parses modifiers from dotted keys', () => {
    expect(parseDirectiveKey('scrollable.height')).toEqual({
      name: 'scrollable',
      modifiers: { height: true },
    })
  })

  test('treats descriptor objects separately from option objects', () => {
    expect(isDirectiveDescriptor({ chrome: '.foo' })).toBe(false)
    expect(isDirectiveDescriptor({ value: '100%', modifiers: { height: true } })).toBe(true)
  })

  test('skips false and null bindings', () => {
    expect(isEnabledDirectiveValue(false)).toBe(false)
    expect(isEnabledDirectiveValue(null)).toBe(false)
    expect(isEnabledDirectiveValue(true)).toBe(true)
  })

  test('normalizes key modifiers and descriptor values', () => {
    expect(normalizeDirectiveBinding('scrollable.height', '50%')).toEqual({
      name: 'scrollable',
      value: '50%',
      arg: null,
      modifiers: { height: true },
    })

    expect(normalizeDirectiveBinding('scrollable', {
      value: '240',
      modifiers: { height: true },
    })).toEqual({
      name: 'scrollable',
      value: '240',
      arg: null,
      modifiers: { height: true },
    })
  })
})
