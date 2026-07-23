import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createStore } from 'vuex'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import BroadcastToasts from '../../src/js/components/shared/BroadcastToasts.vue'
import broadcastToastModule from '../../src/js/store/modules/broadcastToast.js'
import { BROADCAST_TOAST } from '../../src/js/store/mutations/index.js'

const vuetify = createVuetify({ components, directives })

function createToastStore () {
  return createStore({
    modules: {
      broadcastToast: broadcastToastModule,
    },
  })
}

describe('BroadcastToasts', () => {
  let openSpy

  beforeEach(() => {
    openSpy = vi.spyOn(window, 'open').mockImplementation(() => null)
  })

  afterEach(() => {
    openSpy.mockRestore()
  })

  test('renders Look button and opens redirector in a new tab', async () => {
    const store = createToastStore()
    store.commit(BROADCAST_TOAST.PUSH, {
      title: 'Unread chat',
      description: 'You have a new message',
      redirector: 'https://example.test/chat/1',
      hasRedirector: true,
      redirectorText: 'Look',
      timeout: 0,
      variant: 'info',
    })

    const wrapper = mount(BroadcastToasts, {
      global: {
        plugins: [store, vuetify],
      },
    })

    expect(wrapper.text()).toContain('Unread chat')
    expect(wrapper.text()).toContain('Look')

    const lookBtn = wrapper.findComponent({ name: 'VBtn' })
    expect(lookBtn.exists()).toBe(true)
    expect(lookBtn.props('color')).toBe('white')
    expect(lookBtn.props('variant')).toBe('flat')

    await lookBtn.trigger('click')

    expect(openSpy).toHaveBeenCalledWith(
      'https://example.test/chat/1',
      '_blank',
      'noopener',
    )
  })

  test('omits Look button when redirector is absent', () => {
    const store = createToastStore()
    store.commit(BROADCAST_TOAST.PUSH, {
      title: 'Info',
      description: 'No link',
      timeout: 0,
      variant: 'info',
    })

    const wrapper = mount(BroadcastToasts, {
      global: {
        plugins: [store, vuetify],
      },
    })

    expect(wrapper.text()).not.toContain('Look')
  })
})
