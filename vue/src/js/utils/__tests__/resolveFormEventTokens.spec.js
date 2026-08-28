import { describe, it, expect } from 'vitest'
import { resolveFormEventTokens } from '../resolveFormEventTokens'

describe('resolveFormEventTokens', () => {
  it('prefers formEvents array over event pipe', () => {
    expect(resolveFormEventTokens({
      formEvents: ['formatUpdate:name:modelValue:modelValue'],
      event: 'formatLock:url:url',
    })).toEqual(['formatUpdate:name:modelValue:modelValue'])
  })

  it('splits formEvents string and event fallback', () => {
    expect(resolveFormEventTokens({
      formEvents: 'formatSet:a:b:c|formatUpdate:d:e:f',
    })).toEqual(['formatSet:a:b:c', 'formatUpdate:d:e:f'])

    expect(resolveFormEventTokens({
      event: 'formatLock:url:url|formatClearModel:status',
    })).toEqual(['formatLock:url:url', 'formatClearModel:status'])
  })

  it('returns empty when neither key is set', () => {
    expect(resolveFormEventTokens({})).toEqual([])
    expect(resolveFormEventTokens(null)).toEqual([])
  })
})
