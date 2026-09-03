import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import i18n from '../../src/js/config/i18n'
import RecursiveStuff from '../../src/js/components/shared/RecursiveStuff.vue'
import Scrollable from '../../src/js/directives/scrollable.js'

const vuetify = createVuetify({ components, directives })

function mountRecursive (configuration) {
  return mount(RecursiveStuff, {
    global: {
      plugins: [vuetify, i18n],
      components: {
        UeRecursiveStuff: RecursiveStuff,
      },
      directives: {
        scrollable: Scrollable.directive,
      },
    },
    props: { configuration },
  })
}

describe('RecursiveStuff directive render path', () => {
  test('resolves nested children instead of emitting an empty custom element', () => {
    const wrapper = mountRecursive({
      tag: 'v-col',
      attributes: { class: 'h-50' },
      directives: { scrollable: true },
      elements: [
        {
          tag: 'div',
          attributes: { class: 'inner-widget' },
          elements: 'hello',
        },
      ],
    })

    expect(wrapper.find('ue-recursive-stuff').exists()).toBe(false)
    expect(wrapper.find('.inner-widget').exists()).toBe(true)
    expect(wrapper.find('.inner-widget').text()).toBe('hello')
    expect(wrapper.find('.ue-scrollable').exists()).toBe(true)
  })
})
