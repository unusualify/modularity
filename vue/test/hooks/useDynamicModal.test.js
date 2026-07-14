import { describe, expect, test } from 'vitest'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useDynamicModal from '../../src/js/hooks/useDynamicModal.js'

const mockModalService = {
  open: () => {},
  close: () => {}
}

const TestComponent = defineComponent({
  setup() {
    const modal = useDynamicModal()

    return { modal }
  },
  render: () => h('div')
})

describe('useDynamicModal', () => {
  test('returns modal service when provided', () => {
    const wrapper = mount(TestComponent, {
      global: {
        provide: {
          modalService: mockModalService
        }
      }
    })
    expect(wrapper.vm.modal).toBe(mockModalService)
  })

  test('throws when modalService not provided', () => {
    expect(() => {
      mount(TestComponent)
    }).toThrow('[ModalService] not installed')
  })
})
