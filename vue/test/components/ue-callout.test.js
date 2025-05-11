import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'

import Callout from '../../src/js/components/labs/Callout.vue'

import vuetify from '../../src/js/plugins/vuetify'

describe('callout tests', () => {

  test('renders Callout component', () => {
    const wrapper = mount(Callout, {
      global: {
        plugins: [vuetify],
      },
      props: {
        density: 'compact',
        type: 'warning',
        text: 'Example Alert'
      }
    })

    // const todo = wrapper.get('[data-test="todo"]')

    expect(wrapper.text()).toBe('')
  })

})

