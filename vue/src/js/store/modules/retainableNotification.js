import { RETAINABLE_NOTIFICATION } from '../mutations'
import {
  dismissRetainableByGroup,
  filterActiveRetainableItems,
  loadRetainableFromStorage,
  retainableStorageKey,
  RETAINABLE_DEFAULT_HOURS,
  saveRetainableToStorage,
  upsertRetainableItem,
} from '@/utils/retainableNotifications'

let nextId = 1

const state = () => ({
  items: [],
  panelOpen: false,
  storageKey: null,
})

const getters = {
  retainableNotifications: (moduleState) => filterActiveRetainableItems(moduleState.items),
  retainableNotificationCount: (moduleState, moduleGetters) => moduleGetters.retainableNotifications.length,
  retainableNotificationsPanelOpen: (moduleState) => moduleState.panelOpen,
}

function persist (moduleState) {
  saveRetainableToStorage(
    moduleState.storageKey,
    filterActiveRetainableItems(moduleState.items),
  )
}

function closePanelIfEmpty (moduleState) {
  if (filterActiveRetainableItems(moduleState.items).length === 0) {
    moduleState.panelOpen = false
  }
}

const mutations = {
  [RETAINABLE_NOTIFICATION.HYDRATE] (moduleState, { userId } = {}) {
    const key = retainableStorageKey(userId)
    moduleState.storageKey = key
    moduleState.items = loadRetainableFromStorage(key)
    moduleState.panelOpen = false
  },

  [RETAINABLE_NOTIFICATION.PUSH] (moduleState, payload) {
    if (!payload || typeof payload !== 'object') {
      return
    }

    const title = payload.title != null && String(payload.title).trim() !== ''
      ? String(payload.title)
      : null
    let description = payload.description != null && String(payload.description).trim() !== ''
      ? String(payload.description)
      : null
    if (!description && payload.message != null && String(payload.message).trim() !== '') {
      description = String(payload.message)
    }
    if (!title && !description) {
      return
    }

    const retainUntil = payload.retainUntil
      || new Date(Date.now() + RETAINABLE_DEFAULT_HOURS * 60 * 60 * 1000).toISOString()

    const retainGroup = payload.retainGroup != null && String(payload.retainGroup).trim() !== ''
      ? String(payload.retainGroup)
      : null

    const item = {
      id: payload.id ?? nextId++,
      token: payload.token ?? null,
      retainGroup,
      title,
      description,
      detail: payload.detail ?? null,
      redirector: payload.redirector ?? null,
      hasRedirector: !!(payload.hasRedirector && payload.redirector) || !!payload.redirector,
      redirectorText: payload.redirectorText ?? null,
      notificationType: payload.notificationType ?? payload.notification_type ?? null,
      retainUntil,
      variant: payload.variant ?? 'info',
      dismissed: false,
      createdAt: payload.createdAt ?? new Date().toISOString(),
    }

    moduleState.items = upsertRetainableItem(
      filterActiveRetainableItems(moduleState.items),
      item,
    )
    // Surface new retainable chat/etc. alerts immediately (bottom-right tray).
    moduleState.panelOpen = true
    persist(moduleState)
  },

  [RETAINABLE_NOTIFICATION.DISMISS] (moduleState, id) {
    moduleState.items = moduleState.items
      .map((item) => (item.id === id ? { ...item, dismissed: true } : item))
      .filter((item) => !item.dismissed)
    persist(moduleState)
    closePanelIfEmpty(moduleState)
  },

  [RETAINABLE_NOTIFICATION.DISMISS_BY_GROUP] (moduleState, retainGroup) {
    if (retainGroup == null || String(retainGroup).trim() === '') {
      return
    }

    moduleState.items = dismissRetainableByGroup(moduleState.items, String(retainGroup))
    persist(moduleState)
    closePanelIfEmpty(moduleState)
  },

  [RETAINABLE_NOTIFICATION.PRUNE] (moduleState) {
    moduleState.items = filterActiveRetainableItems(moduleState.items)
    persist(moduleState)
    if (moduleState.items.length === 0) {
      moduleState.panelOpen = false
    }
  },

  [RETAINABLE_NOTIFICATION.TOGGLE] (moduleState, open) {
    if (typeof open === 'boolean') {
      moduleState.panelOpen = open
    } else {
      moduleState.panelOpen = !moduleState.panelOpen
    }
  },

  [RETAINABLE_NOTIFICATION.CLEAR] (moduleState) {
    moduleState.items = []
    moduleState.panelOpen = false
    persist(moduleState)
  },
}

export default {
  state,
  getters,
  mutations,
}
