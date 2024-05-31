import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'

import { VAlert } from 'vuetify/components/VAlert'

import vuetify from '../../src/js/plugins/vuetify'


describe('VAlert tests', () => {

  test('renders vuetify v-alert', () => {
    const wrapper = mount(VAlert, {
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

    expect(wrapper.text()).toBe('Example Alert')
  })

})

