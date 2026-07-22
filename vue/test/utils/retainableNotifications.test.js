import { describe, expect, test, beforeEach, afterEach } from 'vitest'
import { createStore } from 'vuex'
import retainableNotificationModule from '../../src/js/store/modules/retainableNotification.js'
import { RETAINABLE_NOTIFICATION } from '../../src/js/store/mutations/index.js'
import {
  dismissRetainableByGroup,
  filterActiveRetainableItems,
  isRetainableItemActive,
  upsertRetainableItem,
} from '../../src/js/utils/retainableNotifications.js'

describe('retainableNotifications utils', () => {
  test('filters expired and dismissed items', () => {
    const now = Date.parse('2026-07-21T12:00:00.000Z')
    const items = [
      { id: 1, retainUntil: '2026-07-21T18:00:00.000Z', dismissed: false },
      { id: 2, retainUntil: '2026-07-21T10:00:00.000Z', dismissed: false },
      { id: 3, retainUntil: '2026-07-21T18:00:00.000Z', dismissed: true },
    ]

    expect(filterActiveRetainableItems(items, now).map((i) => i.id)).toEqual([1])
    expect(isRetainableItemActive(items[1], now)).toBe(false)
  })

  test('upsert by retainGroup replaces existing slot', () => {
    const first = {
      id: 1,
      token: 'tok-a',
      retainGroup: 'chat:5',
      title: 'First',
      description: 'msg 1',
    }
    const second = {
      id: 2,
      token: 'tok-b',
      retainGroup: 'chat:5',
      title: 'Second',
      description: 'msg 2',
    }

    const result = upsertRetainableItem([first], second)

    expect(result).toHaveLength(1)
    expect(result[0].title).toBe('Second')
    expect(result[0].token).toBe('tok-b')
    expect(result[0].retainGroup).toBe('chat:5')
  })

  test('upsert without retainGroup falls back to token', () => {
    const first = { id: 1, token: 'same', title: 'A', description: '1' }
    const second = { id: 99, token: 'same', title: 'B', description: '2' }

    const result = upsertRetainableItem([first], second)

    expect(result).toHaveLength(1)
    expect(result[0].title).toBe('B')
    expect(result[0].id).toBe(99)
  })

  test('dismissRetainableByGroup removes matching items only', () => {
    const items = [
      { id: 1, retainGroup: 'chat:5', title: 'A' },
      { id: 2, retainGroup: 'chat:9', title: 'B' },
      { id: 3, retainGroup: 'chat:5', title: 'C' },
    ]

    expect(dismissRetainableByGroup(items, 'chat:5').map((i) => i.id)).toEqual([2])
  })
})

describe('retainableNotification vuex module', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  afterEach(() => {
    localStorage.clear()
  })

  function createRetainStore () {
    return createStore({
      modules: {
        retainableNotification: retainableNotificationModule,
      },
    })
  }

  test('PUSH retains item and DISMISS removes it', () => {
    const store = createRetainStore()
    store.commit(RETAINABLE_NOTIFICATION.HYDRATE, { userId: 7 })

    store.commit(RETAINABLE_NOTIFICATION.PUSH, {
      title: 'Unread chat',
      description: 'You have a new message',
      retainUntil: new Date(Date.now() + 60_000).toISOString(),
      token: 't1',
    })

    expect(store.getters.retainableNotificationCount).toBe(1)
    expect(store.getters.retainableNotifications[0].title).toBe('Unread chat')
    expect(store.getters.retainableNotificationsPanelOpen).toBe(true)

    const id = store.getters.retainableNotifications[0].id
    store.commit(RETAINABLE_NOTIFICATION.DISMISS, id)

    expect(store.getters.retainableNotificationCount).toBe(0)
  })

  test('PUSH with same retainGroup upserts to one tray item', () => {
    const store = createRetainStore()
    store.commit(RETAINABLE_NOTIFICATION.HYDRATE, { userId: 7 })

    store.commit(RETAINABLE_NOTIFICATION.PUSH, {
      title: 'Chat',
      description: 'First',
      retainGroup: 'chat:5',
      token: 't1',
      retainUntil: new Date(Date.now() + 60_000).toISOString(),
    })
    store.commit(RETAINABLE_NOTIFICATION.PUSH, {
      title: 'Chat',
      description: 'Second',
      retainGroup: 'chat:5',
      token: 't2',
      retainUntil: new Date(Date.now() + 60_000).toISOString(),
    })

    expect(store.getters.retainableNotificationCount).toBe(1)
    expect(store.getters.retainableNotifications[0].description).toBe('Second')
    expect(store.getters.retainableNotifications[0].token).toBe('t2')
  })

  test('DISMISS_BY_GROUP removes matching retainGroup items', () => {
    const store = createRetainStore()
    store.commit(RETAINABLE_NOTIFICATION.HYDRATE, { userId: 7 })

    store.commit(RETAINABLE_NOTIFICATION.PUSH, {
      title: 'Chat A',
      description: 'msg',
      retainGroup: 'chat:5',
      token: 't1',
      retainUntil: new Date(Date.now() + 60_000).toISOString(),
    })
    store.commit(RETAINABLE_NOTIFICATION.PUSH, {
      title: 'Chat B',
      description: 'msg',
      retainGroup: 'chat:9',
      token: 't2',
      retainUntil: new Date(Date.now() + 60_000).toISOString(),
    })

    store.commit(RETAINABLE_NOTIFICATION.DISMISS_BY_GROUP, 'chat:5')

    expect(store.getters.retainableNotificationCount).toBe(1)
    expect(store.getters.retainableNotifications[0].retainGroup).toBe('chat:9')
  })

  test('PRUNE drops expired items', () => {
    const store = createRetainStore()
    store.commit(RETAINABLE_NOTIFICATION.HYDRATE, { userId: 7 })
    store.commit(RETAINABLE_NOTIFICATION.PUSH, {
      title: 'Old',
      description: 'Gone',
      retainUntil: new Date(Date.now() - 1000).toISOString(),
      token: 'old',
    })

    store.commit(RETAINABLE_NOTIFICATION.PRUNE)

    expect(store.getters.retainableNotificationCount).toBe(0)
  })
})
