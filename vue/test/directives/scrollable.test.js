import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent } from 'vue'
import Scrollable from '../../src/js/directives/scrollable.js'

describe('v-scrollable', () => {
  test('marks the host as a fill-parent scroll region', () => {
    const wrapper = mount(
      defineComponent({
        directives: { scrollable: Scrollable.directive },
        template: '<div v-scrollable class="h-50">overflow</div>',
      }),
    )

    expect(wrapper.classes()).toContain('ue-scrollable')
    expect(wrapper.element.style.minHeight).toBe('0')
    expect(wrapper.element.style.height).toBe('')
  })

  test('sets explicit height with the height modifier', () => {
    const wrapper = mount(
      defineComponent({
        directives: { scrollable: Scrollable.directive },
        template: '<div v-scrollable.height="240">overflow</div>',
      }),
    )

    expect(wrapper.element.style.height).toBe('240px')
    expect(wrapper.element.style.overflowY).toBe('auto')
  })

  test('accepts a CSS length as the binding value', () => {
    const wrapper = mount(
      defineComponent({
        directives: { scrollable: Scrollable.directive },
        template: '<div v-scrollable="\'50%\'">overflow</div>',
      }),
    )

    expect(wrapper.element.style.height).toBe('50%')
    expect(wrapper.element.style.overflowY).toBe('auto')
  })
})
