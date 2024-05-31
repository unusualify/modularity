// test/utils/common-methods.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import CommonMethods from '../../src/js/utils/commonMethods.js'

// mock example, delete this code phrase while testing
// vi.spyOn(CommonMethods, '$isset')
vi.mock('../../src/js/utils/commonMethods.js', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...actual,
    // your mocked methods
    $isset: (...args) => {
      const a = args
      const l = a.length
      let i = 0
      let undef

      if (l === 0) {
        throw new Error('Empty isset')
      }

      while (i !== l) {
        if (a[i] === undef || a[i] === null) {
          return false
        }
        i++
      }
      return true
    },
  }
})

CommonMethods.overwriteMethod = () => true

describe('commonMethods util tests', () => {

  // mock method test example
  test('test a method of CommonMethods util', async () => {
    // const result = await CommonMethods.$isset(CommonMethods.deneme)
    const result = await CommonMethods.overwriteMethod()

    expect(result).toEqual(true)

  })

})

