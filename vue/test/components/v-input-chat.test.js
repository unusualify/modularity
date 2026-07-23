import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import UEConfig from '../../src/js/plugins/UEConfig'
import VInputChat from '../../src/js/components/inputs/Chat.vue'

vi.mock('axios', () => ({
  default: {
    get: vi.fn(() => Promise.resolve({ status: 200, data: [] })),
    post: vi.fn(() => Promise.resolve({ status: 200, data: {} })),
    put: vi.fn(() => Promise.resolve({ status: 200, data: {} })),
    delete: vi.fn(() => Promise.resolve({ status: 200, data: {} })),
    defaults: { headers: { common: {} } },
    interceptors: {
      request: { use: vi.fn() },
      response: { use: vi.fn() },
    },
  },
}))

const endpoints = {
  index: '/api/chats/:id/messages',
  store: '/api/chats/:id/messages',
  update: '/api/chat-messages/:id',
  attachments: '/api/chats/:id/attachments',
  pinnedMessage: '/api/chats/:id/pinned-message',
}

function appNamespace () {
  return import.meta.env.VUE_APP_NAME || 'MODULAROUS'
}

function mountChat (props = {}) {
  return mount(VInputChat, {
    global: {
      plugins: [UEConfig],
      stubs: {
        ChatMessage: true,
        InfiniteLoading: true,
        'ue-title': true,
        'ue-button': true,
        'ue-modal': true,
        'file-uploader': true,
        'emoji-picker': true,
      },
    },
    props: {
      modelValue: 5,
      endpoints,
      refreshTime: 10000,
      ...props,
    },
  })
}

describe('VInputChat broadcast sync', () => {
  beforeEach(() => {
    const ns = appNamespace()
    window[ns] = window[ns] || { STORE: {} }
    window[ns].STORE = window[ns].STORE || {}
    window[ns].STORE.broadcast = { enabled: true }
    window[ns].ENDPOINTS = {
      ...(window[ns].ENDPOINTS || {}),
      languages: window[ns].ENDPOINTS?.languages ?? '',
    }

    vi.spyOn(VInputChat.methods, 'loadMessages').mockImplementation(() => {})
    vi.spyOn(VInputChat.methods, 'getAttachments').mockImplementation(() => {})
  })

  afterEach(() => {
    delete window.Echo
    const ns = appNamespace()
    if (window[ns]?.STORE) {
      delete window[ns].STORE.broadcast
    }
    vi.restoreAllMocks()
  })

  test('subscribes to Echo and skips polling when broadcast is available', async () => {
    const listen = vi.fn().mockReturnThis()
    const leave = vi.fn()
    window.Echo = {
      private: vi.fn(() => ({ listen })),
      leave,
    }

    const wrapper = mountChat()

    expect(window.Echo.private).toHaveBeenCalledWith('chats.5')
    expect(listen).toHaveBeenCalledWith(
      '.modularous.chatable.message.synced',
      expect.any(Function),
    )
    expect(wrapper.vm.refreshInterval).toBeNull()

    wrapper.unmount()
    expect(leave).toHaveBeenCalledWith('chats.5')
  })

  test('dismisses retainable tray for chat group on open', async () => {
    const listen = vi.fn().mockReturnThis()
    window.Echo = {
      private: vi.fn(() => ({ listen })),
      leave: vi.fn(),
    }

    const wrapper = mountChat()
    const commit = vi.spyOn(wrapper.vm.$store, 'commit')

    wrapper.vm.dismissRetainableForChat()

    expect(commit).toHaveBeenCalledWith(
      expect.stringContaining('dismissRetainableNotificationByGroup'),
      'chat:5',
    )

    wrapper.unmount()
  })

  test('starts polling when Echo is unavailable', async () => {
    delete window.Echo

    const wrapper = mountChat()

    expect(wrapper.vm.refreshInterval).not.toBeNull()
    wrapper.unmount()
    expect(wrapper.vm.refreshInterval).toBeNull()
  })
})
