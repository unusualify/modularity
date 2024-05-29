import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'

describe('example tests', () => {

  test('Math.sqrt()', () => {
    expect(Math.sqrt(4)).toBe(2)
    expect(Math.sqrt(144)).toBe(12)
    expect(Math.sqrt(2)).toBe(Math.SQRT2)
  })

  // test('Squared', () => {
  //   expect(squared(2)).toBe(4)
  //   expect(squared(12)).toBe(144)
  // })

  test('JSON', () => {
    const input = {
      foo: 'hello',
      bar: 'world',
    }

    const output = JSON.stringify(input)

    expect(output).eq('{"foo":"hello","bar":"world"}')
    assert.deepEqual(JSON.parse(output), input, 'matches original')
  })
})

