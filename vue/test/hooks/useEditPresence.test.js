import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import { defineComponent, h, nextTick, ref } from 'vue'
import { createStore } from 'vuex'
import { mount } from '@vue/test-utils'
import useEditPresence, {
  normalizePresenceMember,
  samePresenceUserId,
} from '../../src/js/hooks/useEditPresence.js'

vi.mock('@inertiajs/vue3', () => {
  const listeners = []
  return {
    router: {
      on: vi.fn((event, cb) => {
        listeners.push({ event, cb })
        return () => {
          const idx = listeners.findIndex((l) => l.cb === cb)
          if (idx >= 0) listeners.splice(idx, 1)
        }
      }),
      __listeners: listeners,
      __emit (event) {
        listeners.filter((l) => l.event === event).forEach((l) => l.cb())
      },
    },
  }
})

describe('normalizePresenceMember / samePresenceUserId', () => {
  test('normalizes user_info and { id, info } shapes', () => {
    expect(normalizePresenceMember({ id: 2, name: 'Alice' })).toEqual({ id: 2, name: 'Alice' })
    expect(normalizePresenceMember({ id: '2', info: { id: 2, name: 'Alice' } })).toEqual({
      id: 2,
      name: 'Alice',
    })
    expect(normalizePresenceMember(null)).toBeNull()
  })

  test('compares ids as strings so number/string match', () => {
    expect(samePresenceUserId(1, '1')).toBe(true)
    expect(samePresenceUserId(1, 2)).toBe(false)
    expect(samePresenceUserId(null, 1)).toBe(false)
  })
})

describe('useEditPresence', () => {
  let echo
  const wrappers = []

  beforeEach(() => {
    echo = createEchoMock()
    window.Echo = echo
  })

  afterEach(() => {
    while (wrappers.length) {
      wrappers.pop().unmount()
    }
    delete window.Echo
  })

  function createEchoMock () {
    const channel = {
      here: vi.fn(function (cb) {
        this._here = cb
        return this
      }),
      joining: vi.fn(function (cb) {
        this._joining = cb
        return this
      }),
      leaving: vi.fn(function (cb) {
        this._leaving = cb
        return this
      }),
      error: vi.fn(function () {
        return this
      }),
    }

    return {
      join: vi.fn(() => channel),
      leave: vi.fn(),
      disconnect: vi.fn(),
      leaveAllChannels: vi.fn(),
      _channel: channel,
    }
  }

  function createStoreWithUser (userId = 1) {
    return createStore({
      getters: {
        userProfile: () => ({ id: userId, name: 'Me' }),
      },
    })
  }

  function mountPresence ({ enabled = true, modelType = 'App-Models-Post', modelId = 42, store } = {}) {
    const enabledRef = ref(enabled)
    const modelTypeRef = ref(modelType)
    const modelIdRef = ref(modelId)
    let api = null

    const Comp = defineComponent({
      setup () {
        api = useEditPresence({
          enabled: enabledRef,
          modelType: modelTypeRef,
          modelId: modelIdRef,
        })
        return api
      },
      render: () => h('div'),
    })

    const wrapper = mount(Comp, {
      global: { plugins: [store || createStoreWithUser()] },
    })
    wrappers.push(wrapper)

    return { wrapper, api, enabledRef, modelTypeRef, modelIdRef }
  }

  test('joins presence channel when enabled with model type and id', async () => {
    mountPresence()
    await nextTick()

    expect(echo.join).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('leaves with the joined name when channelName becomes null', async () => {
    const { modelIdRef } = mountPresence()
    await nextTick()

    modelIdRef.value = null
    await nextTick()

    expect(echo.leave).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('leaves when enabled becomes false', async () => {
    const { enabledRef } = mountPresence()
    await nextTick()
    echo.leave.mockClear()

    enabledRef.value = false
    await nextTick()

    expect(echo.leave).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('leaves on pagehide without relying on Vue unmount', async () => {
    mountPresence()
    await nextTick()
    echo.leave.mockClear()

    window.dispatchEvent(new Event('pagehide'))

    expect(echo.leave).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('leaves on beforeunload', async () => {
    mountPresence()
    await nextTick()
    echo.leave.mockClear()

    window.dispatchEvent(new Event('beforeunload'))

    expect(echo.leave).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('leaves early on same-origin navigational click', async () => {
    mountPresence()
    await nextTick()
    echo.leave.mockClear()

    const stopNav = (e) => e.preventDefault()
    document.addEventListener('click', stopNav, true)

    const anchor = document.createElement('a')
    anchor.setAttribute('href', '/admin/other-page')
    document.body.appendChild(anchor)
    anchor.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, button: 0 }))
    document.body.removeChild(anchor)
    document.removeEventListener('click', stopNav, true)

    expect(echo.leave).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('leaves on Inertia before navigation', async () => {
    const { router } = await import('@inertiajs/vue3')
    mountPresence()
    await nextTick()
    echo.leave.mockClear()

    router.__emit('before')

    expect(echo.leave).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('does not leave on same-path hash/download clicks', async () => {
    mountPresence()
    await nextTick()
    echo.leave.mockClear()

    const hash = document.createElement('a')
    hash.setAttribute('href', '#section')
    document.body.appendChild(hash)
    hash.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, button: 0 }))
    document.body.removeChild(hash)

    expect(echo.leave).not.toHaveBeenCalled()
  })

  test('leaves on unmount', async () => {
    const { wrapper } = mountPresence()
    await nextTick()
    echo.leave.mockClear()

    wrapper.unmount()

    expect(echo.leave).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })

  test('tracks other editors via here/joining/leaving and excludes self', async () => {
    const { api } = mountPresence({ store: createStoreWithUser(1) })
    await nextTick()

    echo._channel._here([
      { id: 1, name: 'Me' },
      { id: 2, name: 'Alice' },
    ])
    expect(api.hasOtherEditors.value).toBe(true)
    expect(api.lockMessage.value).toContain('Alice')
    expect(api.lockMessage.value).not.toContain('Me')
    expect(api.otherEditors.value.map((u) => u.name)).toEqual(['Alice'])

    echo._channel._joining({ id: 3, name: 'Bob' })
    expect(api.otherEditors.value.map((u) => u.name)).toEqual(['Alice', 'Bob'])

    echo._channel._leaving({ id: 2, name: 'Alice' })
    expect(api.otherEditors.value.map((u) => u.name)).toEqual(['Bob'])
  })

  test('second joiner sees first via here; first sees second via joining', async () => {
    const echoA = createEchoMock()
    const echoB = createEchoMock()

    window.Echo = echoA
    const userA = mountPresence({ store: createStoreWithUser(1) })
    await nextTick()
    echoA._channel._here([{ id: 1, name: 'First' }])
    expect(userA.api.hasOtherEditors.value).toBe(false)

    window.Echo = echoB
    const userB = mountPresence({ store: createStoreWithUser(2) })
    await nextTick()
    echoB._channel._here([
      { id: 1, name: 'First' },
      { id: 2, name: 'Second' },
    ])
    expect(userB.api.hasOtherEditors.value).toBe(true)
    expect(userB.api.lockMessage.value).toContain('First')
    expect(userB.api.lockMessage.value).not.toContain('Second')

    // First user receives joining for second
    echoA._channel._joining({ id: 2, name: 'Second' })
    expect(userA.api.hasOtherEditors.value).toBe(true)
    expect(userA.api.lockMessage.value).toContain('Second')
    expect(userA.api.lockMessage.value).not.toContain('First')
  })

  test('leaving clears other editors for remaining user', async () => {
    const { api } = mountPresence({ store: createStoreWithUser(1) })
    await nextTick()

    echo._channel._here([
      { id: 1, name: 'Me' },
      { id: 2, name: 'Alice' },
    ])
    expect(api.hasOtherEditors.value).toBe(true)

    echo._channel._leaving({ id: '2', info: { id: 2, name: 'Alice' } })
    expect(api.hasOtherEditors.value).toBe(false)
    expect(api.lockMessage.value).toBeNull()
  })

  test('rejoins after bfcache pageshow when persisted', async () => {
    mountPresence()
    await nextTick()
    window.dispatchEvent(new Event('pagehide'))
    echo.join.mockClear()

    const event = new Event('pageshow')
    Object.defineProperty(event, 'persisted', { value: true })
    window.dispatchEvent(event)
    await nextTick()

    expect(echo.join).toHaveBeenCalledWith('editing.App-Models-Post.42')
  })
})
