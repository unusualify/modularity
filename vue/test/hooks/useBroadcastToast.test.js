import { describe, expect, test, vi } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, h } from 'vue'
import { mount } from '@vue/test-utils'
import useBroadcastToast from '../../src/js/hooks/useBroadcastToast.js'
import broadcastToastModule from '../../src/js/store/modules/broadcastToast.js'
import { BROADCAST_TOAST } from '../../src/js/store/mutations/index.js'

function createStoreWithBroadcastToast () {
  return createStore({
    modules: {
      broadcastToast: broadcastToastModule,
    },
  })
}

const TestComponent = defineComponent({
  setup () {
    return useBroadcastToast()
  },
  render: () => h('div'),
})

describe('useBroadcastToast', () => {
  test('push commits BROADCAST_TOAST.PUSH', async () => {
    const store = createStoreWithBroadcastToast()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = mount(TestComponent, {
      global: { plugins: [store] },
    })

    const payload = { message: 'Hello', variant: 'info' }
    wrapper.vm.push(payload)

    expect(spy).toHaveBeenCalledWith(BROADCAST_TOAST.PUSH, payload)
  })

  test('push forwards title description detail payload', async () => {
    const store = createStoreWithBroadcastToast()
    const spy = vi.spyOn(store, 'commit')
    const wrapper = mount(TestComponent, {
      global: { plugins: [store] },
    })

    const payload = {
      title: 'Cache warm',
      description: 'Done',
      detail: 'Blog:Post:1',
      variant: 'success',
    }
    wrapper.vm.push(payload)

    expect(spy).toHaveBeenCalledWith(BROADCAST_TOAST.PUSH, payload)
  })

  test('dismiss commits BROADCAST_TOAST.DISMISS', async () => {
    const store = createStoreWithBroadcastToast()
    store.commit(BROADCAST_TOAST.PUSH, { message: 'Hello' })
    const id = store.state.broadcastToast.visible[0].id
    const spy = vi.spyOn(store, 'commit')

    const wrapper = mount(TestComponent, {
      global: { plugins: [store] },
    })

    wrapper.vm.dismiss(id)

    expect(spy).toHaveBeenCalledWith(BROADCAST_TOAST.DISMISS, id)
  })
})
