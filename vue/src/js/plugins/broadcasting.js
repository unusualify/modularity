import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { BROADCAST_TOAST } from '@/store/mutations'

const DOMAIN_CHANNELS = [
  'assignable',
  'payment',
  'stateable',
  'unread-chat-message',
  'model',
]

const DOMAIN_EVENTS = [
  'modularous.assignment.created',
  'modularous.assignment.updated',
  'modularous.payment.completed',
  'modularous.payment.failed',
  'modularous.stateable.updated',
  'modularous.unread.chat.message',
  'modularous.model.created',
  'modularous.model.updated',
]

function resolveStore (app) {
  return app?._context?.config?.globalProperties?.$store
    ?? app?._component?.store
    ?? window?.__VUE_APP_STORE__
    ?? null
}

/**
 * Normalize a broadcast payload into toast fields.
 * Prefers `payload.toast` when present; otherwise maps notification-like fields.
 *
 * @param {Record<string, any>|string|null|undefined} payload
 * @returns {{ title: string|null, description: string|null, detail: string|null, variant: string|null, timeout?: number }|null}
 */
export function resolveBroadcastToastPayload (payload) {
  if (!payload) {
    return null
  }

  if (typeof payload === 'string') {
    return {
      title: null,
      description: payload,
      detail: null,
      variant: null,
    }
  }

  const source = payload.toast && typeof payload.toast === 'object'
    ? payload.toast
    : payload

  const title = source.title ?? source.subject ?? null
  const description = source.description ?? source.message ?? null
  const detail = source.detail ?? null

  if (!title && !description) {
    return null
  }

  return {
    title,
    description,
    detail,
    variant: source.variant ?? null,
    ...(source.timeout !== undefined ? { timeout: source.timeout } : {}),
  }
}

/**
 * Push a toast from any broadcast payload that carries toast or notification fields.
 *
 * @param {import('vuex').Store|null} store
 * @param {Record<string, any>|string|null|undefined} payload
 * @param {string} [fallbackVariant]
 */
export function pushBroadcastToastFromPayload (store, payload, fallbackVariant = 'info') {
  if (!store) {
    return
  }

  const toast = resolveBroadcastToastPayload(payload)
  if (!toast) {
    return
  }

  store.commit(BROADCAST_TOAST.PUSH, {
    title: toast.title,
    description: toast.description,
    detail: toast.detail,
    variant: toast.variant ?? fallbackVariant,
    ...(toast.timeout !== undefined ? { timeout: toast.timeout } : {}),
  })
}

function invalidateMyNotifications (store) {
  if (!store) {
    return
  }

  if (typeof store.dispatch === 'function') {
    store.dispatch('datatable/getDatatableData').catch(() => {})
  }

  window.dispatchEvent(new CustomEvent('modularous:notifications-updated'))
}

function resolveBroadcastUserId (storeBootstrap) {
  const raw = storeBootstrap?.broadcast?.userId
    ?? storeBootstrap?.user?.profile?.id
    ?? null

  const id = Number(raw)

  return Number.isFinite(id) && id > 0 ? id : null
}

function subscribeUserChannel (EchoInstance, userId, store) {
  if (!userId) {
    return
  }

  const channel = EchoInstance.private(`users.${userId}`)

  channel.notification((notification) => {
    pushBroadcastToastFromPayload(store, {
      title: notification.subject || null,
      description: notification.message || notification.subject || 'New notification',
      detail: notification.detail || null,
      variant: 'info',
    })
    invalidateMyNotifications(store)
  })

  // Any private-user event with a structured `toast` object (backend-owned fields).
  channel.listenToAll((_eventName, payload) => {
    if (!payload?.toast) {
      return
    }
    pushBroadcastToastFromPayload(store, payload)
  })
}

/**
 * Same-origin channel authorizer with credentials so Modularous session cookies
 * are always sent (Pusher XHR defaults are fine same-origin; this is explicit).
 */
function createChannelAuthorizer (authEndpoint, headers) {
  return (channel, options) => ({
    authorize: (socketId, callback) => {
      const body = new URLSearchParams({
        socket_id: socketId,
        channel_name: channel.name,
      })

      fetch(authEndpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          ...headers,
        },
        body: body.toString(),
      })
        .then(async (response) => {
          const text = await response.text()
          let data = {}
          try {
            data = text ? JSON.parse(text) : {}
          } catch (_) {
            data = { message: text }
          }

          if (!response.ok) {
            if (import.meta.env.DEV) {
              console.warn(
                '[echo] broadcasting/auth failed',
                response.status,
                channel.name,
                data,
              )
            }
            callback(true, data)
            return
          }

          callback(false, data)
        })
        .catch((error) => {
          if (import.meta.env.DEV) {
            console.warn('[echo] broadcasting/auth error', channel.name, error)
          }
          callback(true, error)
        })
    },
  })
}

function subscribeDomainChannels (EchoInstance, channels, store) {
  const list = Array.isArray(channels) && channels.length ? channels : DOMAIN_CHANNELS

  list.forEach((channelName) => {
    const channel = EchoInstance.channel(channelName)

    DOMAIN_EVENTS.forEach((eventName) => {
      channel.listen(`.${eventName}`, (payload) => {
        const data = typeof payload === 'string' ? JSON.parse(payload) : payload
        if (import.meta.env.DEV) {
          console.debug('[broadcast]', channelName, eventName, data)
        }
      })
    })
  })
}

function resolveReverbScheme () {
  const configured = import.meta.env.VITE_REVERB_SCHEME
  if (configured === 'http' || configured === 'https') {
    return configured
  }

  return window.location.protocol === 'https:' ? 'https' : 'http'
}

function resolveReverbPort (scheme) {
  const configured = import.meta.env.VITE_REVERB_PORT
  if (configured !== undefined && configured !== null && String(configured).trim() !== '') {
    return Number(configured)
  }

  // Direct Reverb default is 8080; nginx-proxied setups should set VITE_REVERB_PORT=80/443.
  return scheme === 'https' ? 443 : 8080
}

/**
 * Leave all channels and close the WebSocket.
 * Call before logout redirect so other clients get presence `leaving` while the
 * session is still valid enough for an unsubscribe (or immediately via disconnect).
 */
export function disconnectEcho () {
  const echo = window.Echo
  if (!echo) {
    return
  }

  try {
    if (typeof echo.leaveAllChannels === 'function') {
      echo.leaveAllChannels()
    }
  } catch (_) {
    // ignore
  }

  try {
    echo.disconnect()
  } catch (_) {
    // ignore
  }

  echo.connected = false
}

export default {
  install: (app) => {
    window.Pusher = Pusher
    window.disconnectEcho = disconnectEcho

    const ns = import.meta.env.VUE_APP_NAME
    const storeBootstrap = window[ns]?.STORE || {}
    const broadcastConfig = storeBootstrap.broadcast || {}

    // Backend shares STORE.broadcast.enabled when Modularous broadcasting is off
    // or the driver SDK is unavailable (missing Pusher/Reverb packages).
    if (broadcastConfig.enabled === false) {
      return
    }

    const reverbKey = import.meta.env.VITE_REVERB_APP_KEY
    if (!reverbKey) {
      return
    }

    const scheme = resolveReverbScheme()
    const forceTLS = scheme === 'https'
    const port = resolveReverbPort(scheme)
    const wsHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname

    if (import.meta.env.DEV) {
      console.debug(
        `[echo] connecting ${forceTLS ? 'wss' : 'ws'}://${wsHost}:${port}/app/${reverbKey}`,
      )
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const authEndpoint = '/broadcasting/auth'
    const authHeaders = {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    }

    window.Echo = new Echo({
      broadcaster: 'reverb',
      key: reverbKey,
      wsHost,
      wsPort: port,
      wssPort: port,
      forceTLS,
      enabledTransports: ['ws', 'wss'],
      // Same-origin relative path; Laravel Broadcast::routes() also exempts CSRF.
      authEndpoint,
      auth: {
        headers: authHeaders,
      },
      authorizer: createChannelAuthorizer(authEndpoint, authHeaders),
    })

    app.config.globalProperties.$echo = window.Echo
    window.Echo.connected = false

    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
      window.Echo.connected = states.current === 'connected'
    })

    const userId = resolveBroadcastUserId(storeBootstrap)

    const store = resolveStore(app)

    subscribeUserChannel(window.Echo, userId, store)

    if (broadcastConfig.domainChannels !== false) {
      subscribeDomainChannels(window.Echo, broadcastConfig.domainChannels, store)
    }
  }
}
