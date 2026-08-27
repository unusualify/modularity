import { describe, it, expect } from 'vitest'
import { mapTypeToComponent, registerInputType, getRegisteredTypes } from '../registry'

describe('input registry', () => {
  it('maps text to v-text-field', () => {
    expect(mapTypeToComponent('text')).toBe('v-text-field')
  })
  it('maps date to v-date-picker', () => {
    expect(mapTypeToComponent('date')).toBe('v-date-picker')
  })
  it('maps title to InputTitle', () => {
    expect(mapTypeToComponent('title')).toBe('InputTitle')
  })

  it('maps radio to InputRadio', () => {
    expect(mapTypeToComponent('radio')).toBe('InputRadio')
  })
  it('maps hydrate types to VInput components', () => {
    expect(mapTypeToComponent('input-checklist')).toBe('VInputChecklist')
    expect(mapTypeToComponent('input-tagger')).toBe('VInputTagger')
    expect(mapTypeToComponent('input-file')).toBe('VInputFile')
    expect(mapTypeToComponent('input-editor')).toBe('VInputEditor')
    expect(mapTypeToComponent('input-json-field')).toBe('VInputJsonField')
    expect(mapTypeToComponent('input-source-text')).toBe('VInputSourceText')
    expect(mapTypeToComponent('input-layout-blades')).toBe('VInputLayoutBlades')
  })
  it('returns v-{type} for unknown types', () => {
    expect(mapTypeToComponent('custom-type')).toBe('v-custom-type')
  })
  it('allows custom registration', () => {
    registerInputType('input-price', 'VInputPrice')
    expect(mapTypeToComponent('input-price')).toBe('VInputPrice')
  })
  it('getRegisteredTypes returns built-in and custom', () => {
    const types = getRegisteredTypes()
    expect(types.text).toBe('v-text-field')
    expect(types['input-price']).toBe('VInputPrice')
  })
})
