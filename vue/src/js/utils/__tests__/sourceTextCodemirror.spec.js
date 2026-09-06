import { describe, expect, it } from 'vitest'
import { sourceTextLanguageExtension } from '../sourceTextCodemirror.js'

describe('sourceTextLanguageExtension', () => {
  it('uses markdown for md and unknown values', () => {
    const md = sourceTextLanguageExtension('md')
    const fallback = sourceTextLanguageExtension('yaml')
    expect(md).not.toEqual([])
    expect(fallback).not.toEqual([])
  })

  it('has no highlighter for txt', () => {
    expect(sourceTextLanguageExtension('txt')).toEqual([])
  })

  it('returns a language extension for html, js, php, and json', () => {
    expect(sourceTextLanguageExtension('html')).not.toEqual([])
    expect(sourceTextLanguageExtension('js')).not.toEqual([])
    expect(sourceTextLanguageExtension('php')).not.toEqual([])
    expect(sourceTextLanguageExtension('json')).not.toEqual([])
  })
})
