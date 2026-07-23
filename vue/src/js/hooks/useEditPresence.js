import { ref, computed, watch, onBeforeUnmount } from 'vue'
import { useStore } from 'vuex'
import { router as inertiaRouter } from '@inertiajs/vue3'

function unwrap (maybeRef) {
  return typeof maybeRef === 'object' && maybeRef !== null && 'value' in maybeRef
    ? maybeRef.value
    : maybeRef
}

/**
 * Normalize presence member payloads from Echo/Pusher.
 * Echo `.here()` passes user_info objects; `.joining`/`.leaving` pass `member.info`.
 * Some paths may leak `{ id, info }` — accept both.
 *
 * @param {unknown} user
 * @returns {{ id: string|number, name: string }|null}
 */
export function normalizePresenceMember (user) {
  if (user === null || user === undefined) {
    return null
  }

  if (typeof user !== 'object') {
    if (user === '') {
      return null
    }
    return { id: user, name: `#${user}` }
  }

  const info = user.info && typeof user.info === 'object' ? user.info : user
  const id = info.id ?? user.id
  if (id === null || id === undefined || id === '') {
    return null
  }

  const name = info.name ?? user.name
  return {
    id,
    name: name ? String(name) : `#${id}`,
  }
}

/**
 * @param {unknown} a
 * @param {unknown} b
 */
export function samePresenceUserId (a, b) {
  if (a === null || a === undefined || a === '' || b === null || b === undefined || b === '') {
    return false
  }
  return String(a) === String(b)
}

function resolveBootstrapUserId () {
  if (typeof window === 'undefined') {
    return null
  }
  const ns = import.meta.env.VUE_APP_NAME
  const bootstrap = ns ? window[ns]?.STORE : null
  const raw = bootstrap?.broadcast?.userId ?? bootstrap?.user?.profile?.id ?? null
  if (raw === null || raw === undefined || raw === '') {
    return null
  }
  return raw
}

/**
 * Presence-based concurrent edit awareness (informational only — no hard lock).
 *
 * Leave is durable across:
 * - Vue unmount (SPA / Inertia navigation)
 * - enabled → false / model id cleared
 * - same-origin link navigation (early leave so WS unsubscribe can flush)
 * - Inertia `before` navigation
 * - pagehide / beforeunload (tab close, full page navigation)
 *
 * Logout should also call `disconnectEcho()` so the WebSocket closes before redirect
 * (session may already be invalid for channel auth).
 *
 * @param {object} options
 * @param {import('vue').Ref|import('vue').ComputedRef|boolean} options.enabled
 * @param {import('vue').Ref|import('vue').ComputedRef|string} options.modelType Encoded FQCN (backslashes → dashes)
 * @param {import('vue').Ref|import('vue').ComputedRef|string|number} options.modelId
 */
export default function useEditPresence ({ enabled, modelType, modelId } = {}) {
  const store = useStore()
  const editors = ref([])
  let channel = null
  /** @type {string|null} Name used for join — must be used for leave even if channelName clears. */
  let joinedName = null
  let inertiaOff = null

  const currentUserId = computed(() => {
    const fromStore = store?.getters?.userProfile?.id
    if (fromStore !== null && fromStore !== undefined && fromStore !== '') {
      return fromStore
    }
    return resolveBootstrapUserId()
  })

  const otherEditors = computed(() => {
    const me = currentUserId.value
    // Without a resolvable self id, never treat members as "others" (avoids self-banner).
    if (me === null || me === undefined || me === '') {
      return []
    }
    return (editors.value || []).filter((user) => !samePresenceUserId(user.id, me))
  })

  const hasOtherEditors = computed(() => otherEditors.value.length > 0)

  const lockMessage = computed(() => {
    if (!hasOtherEditors.value) {
      return null
    }

    const names = otherEditors.value
      .map((user) => user.name || `#${user.id}`)
      .filter(Boolean)
      .join(', ')

    return names
      ? `${names} is currently editing this record`
      : 'Another user is currently editing this record'
  })

  const channelName = computed(() => {
    const type = unwrap(modelType)
    const id = unwrap(modelId)

    if (!type || id === null || id === undefined || id === '') {
      return null
    }

    // Pusher rejects backslashes; encoded FQCN uses dashes (Modules-Blog-Entities-Post).
    const safeType = String(type).includes('\\')
      ? String(type).replaceAll('\\', '-')
      : String(type)

    return `editing.${safeType}.${id}`
  })

  const setEditorsFromUsers = (users) => {
    if (users && typeof users.each === 'function') {
      const list = []
      users.each((member) => {
        const normalized = normalizePresenceMember(member?.info ?? member)
        if (normalized) {
          list.push(normalized)
        }
      })
      editors.value = list
      return
    }

    const list = (Array.isArray(users) ? users : [])
      .map(normalizePresenceMember)
      .filter(Boolean)

    // Dedupe by id (presence can briefly double-report).
    const seen = new Set()
    editors.value = list.filter((user) => {
      const key = String(user.id)
      if (seen.has(key)) {
        return false
      }
      seen.add(key)
      return true
    })
  }

  const syncMembersFromPusher = () => {
    if (!joinedName || !window.Echo?.connector?.pusher) {
      return
    }
    try {
      const presence = window.Echo.connector.pusher.channel(`presence-${joinedName}`)
      if (presence?.members) {
        setEditorsFromUsers(presence.members)
      }
    } catch (_) {
      // Best-effort sync for late .here() registration.
    }
  }

  const leave = () => {
    const name = joinedName
    if (name && window.Echo) {
      try {
        window.Echo.leave(name)
      } catch (_) {
        // Best-effort; unload may race the socket.
      }
    }
    channel = null
    joinedName = null
    editors.value = []
  }

  const join = () => {
    leave()

    const name = channelName.value
    const isEnabled = unwrap(enabled)

    if (!isEnabled || !name || !window.Echo) {
      return
    }

    joinedName = name
    channel = window.Echo.join(name)
      .here((users) => {
        setEditorsFromUsers(users)
      })
      .joining((user) => {
        const normalized = normalizePresenceMember(user)
        if (!normalized) {
          return
        }
        if (!editors.value.find((u) => samePresenceUserId(u.id, normalized.id))) {
          editors.value = [...editors.value, normalized]
        }
      })
      .leaving((user) => {
        const normalized = normalizePresenceMember(user)
        const leaveId = normalized?.id ?? (typeof user === 'object' ? user?.id : user)
        if (leaveId === null || leaveId === undefined || leaveId === '') {
          return
        }
        editors.value = editors.value.filter((u) => !samePresenceUserId(u.id, leaveId))
      })
      .error(() => {
        // Presence auth failures should not break the form.
      })

    // If the presence channel was already subscribed (Echo reuses the instance),
    // `pusher:subscription_succeeded` will not fire again — sync members now.
    syncMembersFromPusher()
  }

  watch(
    [() => unwrap(enabled), channelName],
    ([isEnabled]) => {
      if (isEnabled && channelName.value) {
        join()
      } else {
        leave()
      }
    },
    { immediate: true },
  )

  /**
   * Leave early on same-origin navigations so the WS unsubscribe can flush
   * before the document is torn down (pagehide alone is often too late).
   */
  const onDocumentClick = (event) => {
    if (!joinedName) {
      return
    }
    if (event.defaultPrevented || event.button !== 0) {
      return
    }
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return
    }

    const anchor = event.target?.closest?.('a[href]')
    if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) {
      return
    }

    const href = anchor.getAttribute('href')
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
      return
    }

    try {
      const url = new URL(href, window.location.href)
      if (url.origin !== window.location.origin) {
        return
      }
      if (url.pathname === window.location.pathname && url.search === window.location.search) {
        return
      }
      leave()
    } catch (_) {
      // ignore invalid href
    }
  }

  // Browsers often skip Vue unmount on tab/page close; presence then relies on WS
  // disconnect (Reverb member_removed). Explicit leave is faster and more reliable.
  const onPageHide = () => {
    leave()
  }

  const onPageShow = (event) => {
    if (event?.persisted && unwrap(enabled) && channelName.value) {
      join()
    }
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('pagehide', onPageHide)
    window.addEventListener('beforeunload', onPageHide)
    window.addEventListener('pageshow', onPageShow)
    document.addEventListener('click', onDocumentClick, true)
  }

  // Inertia soft navigations do not fire pagehide; leave before the visit.
  if (typeof inertiaRouter?.on === 'function') {
    inertiaOff = inertiaRouter.on('before', () => {
      leave()
    })
  }

  onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
      window.removeEventListener('pagehide', onPageHide)
      window.removeEventListener('beforeunload', onPageHide)
      window.removeEventListener('pageshow', onPageShow)
      document.removeEventListener('click', onDocumentClick, true)
    }
    if (typeof inertiaOff === 'function') {
      inertiaOff()
      inertiaOff = null
    }
    leave()
  })

  return {
    editors,
    otherEditors,
    hasOtherEditors,
    lockMessage,
    join,
    leave,
  }
}
