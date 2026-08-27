import { describe, it, expect, vi, beforeEach } from 'vitest'

vi.mock('@/utils/locale', () => ({
  getActiveContentLocale: vi.fn(() => 'tr'),
  getFallbackContentLocale: vi.fn(() => 'en'),
  getTranslationLanguages: vi.fn(() => ['en', 'tr', 'nl']),
}))

import { getActiveContentLocale, getFallbackContentLocale, getTranslationLanguages } from '@/utils/locale'
import { coerceFormEventValue } from '../formEventFormatters/helpers'

describe('coerceFormEventValue', () => {
  beforeEach(() => {
    getActiveContentLocale.mockReturnValue('tr')
    getFallbackContentLocale.mockReturnValue('en')
    getTranslationLanguages.mockReturnValue(['en', 'tr', 'nl'])
  })

  it('picks the fallback locale, not the active tab, when copying onto a scalar', () => {
    expect(coerceFormEventValue(
      { en: 'Hello', tr: 'Merhaba', nl: 'Hallo' },
      { sourceTranslated: true, targetTranslated: false },
    )).toBe('Hello')
  })

  it('does not steal another locale when the fallback value is empty', () => {
    expect(coerceFormEventValue(
      { en: '', tr: 'Merhaba', nl: 'Hallo' },
      { sourceTranslated: true, targetTranslated: false },
    )).toBe('')
  })

  it('picks an explicit locale token', () => {
    expect(coerceFormEventValue(
      { en: 'Hello', tr: 'Merhaba', nl: 'Hallo' },
      { sourceTranslated: true, targetTranslated: false, sourceLocale: 'tr' },
    )).toBe('Merhaba')
  })

  it('writes the scalar onto the active locale and keeps other locales', () => {
    expect(coerceFormEventValue('Admin', {
      sourceTranslated: false,
      targetTranslated: true,
      currentTargetValue: { en: 'Old', tr: 'Eski', nl: 'Oud' },
    })).toEqual({ en: 'Old', tr: 'Admin', nl: 'Oud' })
  })

  it('passes through when source and target shapes match', () => {
    const map = { en: 'Hello', tr: 'Merhaba' }

    expect(coerceFormEventValue(map, {
      sourceTranslated: true,
      targetTranslated: true,
    })).toBe(map)

    expect(coerceFormEventValue('plain', {
      sourceTranslated: false,
      targetTranslated: false,
    })).toBe('plain')
  })
})
