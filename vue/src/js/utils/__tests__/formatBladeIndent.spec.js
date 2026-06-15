import { describe, it, expect } from 'vitest'
import { formatBladeIndent } from '../formatBladeIndent'

describe('formatBladeIndent', () => {
  it('indents @php block, blade/html nesting, and flattens messy leading spaces', () => {
    const messy = [
      '   @php',
      "        $locale = app()->getLocale();",
      '   @endphp',
      '',
      '    <div class="container">',
      ' <header>',
      '       <span>Hi</span>',
      '          </header>',
      '            </div>',
    ].join('\n')

    const got = formatBladeIndent(messy, 4)
    expect(got).toBe(
      [
        '@php',
        '    $locale = app()->getLocale();',
        '@endphp',
        '',
        '<div class="container">',
        '    <header>',
        '        <span>Hi</span>',
        '    </header>',
        '</div>',
      ].join('\n'),
    )
  })

  it('handles @if / @else / @endif alignment', () => {
    const src = ['@if ($x)', 'foo', '@else', 'bar', '@endif'].join('\n')
    expect(formatBladeIndent(src, 2)).toBe(['@if ($x)', '  foo', '@else', '  bar', '@endif'].join('\n'))
  })
})
