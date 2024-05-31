import { describe, expect, test, vi } from 'vitest'

import { cropConversion } from '../../src/js/utils/cropper.js'

vi.mock('../../src/js/utils/cropper.js', () => {
  return {
    cropConversion() {
      return { x: 80, y: 32, width: 16, height: 57 }
    }
  }
})

describe('cropper tests', () => {

  test('crops image dimension', () => {
    const cropped = cropConversion(
      {height: 80, width: 20, x: 100, y: 45},
      {height: 50, width: 60},
      {height: 70, width: 75}
    )

    expect(cropped).toEqual({ x: 80, y: 32, width: 16, height: 57 })
  })

})

