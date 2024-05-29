import { describe, expect, test, } from 'vitest'
import { mount } from '@vue/test-utils'

import countries from '../../src/js/utils/countries.js'

describe('country tests', () => {

  test('matches first country with the Namibia', () => {
    const country = countries.shift()

    // expect(country).toEqual({ code: 1, name: 'NA' })
    // expect(country).toMatchSnapshot()
    expect(country).toMatchInlineSnapshot(`
      {
        "code": 1,
        "name": "NA",
      }
    `)
  })
})

