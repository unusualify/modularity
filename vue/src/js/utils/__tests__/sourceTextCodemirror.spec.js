import { describe, expect, it } from 'vitest'
import { sourceTextLanguageExtension } from '../sourceTextCodemirror.js'

describe('sourceTextLanguageExtension', () => {
  it('uses markdown for md and unknown values', () => {
    const md = sourceTextLanguageExtension('md')
    const fallback = sourceTextLanguageExtension('json')
    expect(md).not.toEqual([])
    expect(fallback).not.toEqual([])
  })

  it('has no highlighter for txt', () => {
    expect(sourceTextLanguageExtension('txt')).toEqual([])
  })

  it('returns a language extension for html, js, and php', () => {
    expect(sourceTextLanguageExtension('html')).not.toEqual([])
    expect(sourceTextLanguageExtension('js')).not.toEqual([])
    expect(sourceTextLanguageExtension('php')).not.toEqual([])
  })
})
