import { describe, expect, test, vi } from 'vitest'
import {
  pushBroadcastToastFromPayload,
  resolveBroadcastToastPayload,
} from '../../src/js/plugins/broadcasting.js'
import { BROADCAST_TOAST, RETAINABLE_NOTIFICATION } from '../../src/js/store/mutations/index.js'

vi.mock('laravel-echo', () => ({ default: vi.fn() }))
vi.mock('pusher-js', () => ({ default: {} }))

const ephemeralDefaults = {
  retainable: false,
  retainUntil: null,
  retainGroup: null,
  token: null,
  notificationType: null,
}

describe('resolveBroadcastToastPayload', () => {
  test('uses nested toast fields as-is when present', () => {
    expect(resolveBroadcastToastPayload({
      status: 'skipped',
      toast: {
        title: 'Cache warm',
        description: 'Skipped: cache is disabled',
        detail: 'Blog:Post',
        variant: 'warning',
      },
    })).toEqual({
      title: 'Cache warm',
      description: 'Skipped: cache is disabled',
      detail: 'Blog:Post',
      variant: 'warning',
      redirector: null,
      hasRedirector: false,
      redirectorText: null,
      ...ephemeralDefaults,
    })
  })

  test('maps notification-like subject/message to title/description', () => {
    expect(resolveBroadcastToastPayload({
      subject: 'Payment failed',
      message: 'Card declined',
      detail: 'INV-1',
    })).toEqual({
      title: 'Payment failed',
      description: 'Card declined',
      detail: 'INV-1',
      variant: null,
      redirector: null,
      hasRedirector: false,
      redirectorText: null,
      ...ephemeralDefaults,
    })
  })

  test('ignores payloads without toast or title/description content', () => {
    expect(resolveBroadcastToastPayload({ status: 'progress', job: 'WarmJob' })).toBeNull()
    expect(resolveBroadcastToastPayload(null)).toBeNull()
    expect(resolveBroadcastToastPayload({})).toBeNull()
  })

  test('treats string payload as description', () => {
    expect(resolveBroadcastToastPayload('Hello')).toEqual({
      title: null,
      description: 'Hello',
      detail: null,
      variant: null,
      redirector: null,
      hasRedirector: false,
      redirectorText: null,
      ...ephemeralDefaults,
    })
  })

  test('passes redirector and retainable fields from notification-like payload', () => {
    expect(resolveBroadcastToastPayload({
      subject: 'Unread chat',
      message: 'You have a new message',
      redirector: '/admin/orders/1',
      hasRedirector: true,
      redirectorText: 'Open chat',
      retainable: true,
      retainUntil: '2026-07-22T00:00:00+00:00',
      retainGroup: 'chat:42',
      token: 'abc',
      notification_type: 'ChatableUnreadNotification',
    })).toEqual({
      title: 'Unread chat',
      description: 'You have a new message',
      detail: null,
      variant: null,
      redirector: '/admin/orders/1',
      hasRedirector: true,
      redirectorText: 'Open chat',
      retainable: true,
      retainUntil: '2026-07-22T00:00:00+00:00',
      retainGroup: 'chat:42',
      token: 'abc',
      notificationType: 'ChatableUnreadNotification',
    })
  })
})

describe('pushBroadcastToastFromPayload', () => {
  test('commits nested toast with backend variant', () => {
    const commit = vi.fn()
    const store = { commit }

    pushBroadcastToastFromPayload(store, {
      toast: {
        title: 'Done',
        description: 'Warmed 3 records',
        detail: 'Blog:Post',
        variant: 'success',
      },
    })

    expect(commit).toHaveBeenCalledWith(BROADCAST_TOAST.PUSH, {
      title: 'Done',
      description: 'Warmed 3 records',
      detail: 'Blog:Post',
      variant: 'success',
      redirector: null,
      hasRedirector: false,
      redirectorText: null,
    })
  })

  test('commits redirector fields', () => {
    const commit = vi.fn()

    pushBroadcastToastFromPayload({ commit }, {
      subject: 'Hello',
      message: 'Body',
      redirector: 'https://example.test/x',
      hasRedirector: true,
      redirectorText: 'Look',
    })

    expect(commit).toHaveBeenCalledWith(BROADCAST_TOAST.PUSH, {
      title: 'Hello',
      description: 'Body',
      detail: null,
      variant: 'info',
      redirector: 'https://example.test/x',
      hasRedirector: true,
      redirectorText: 'Look',
    })
  })

  test('routes retainable notifications to retainable tray store', () => {
    const commit = vi.fn()

    pushBroadcastToastFromPayload({ commit }, {
      subject: 'Chat',
      message: 'New message',
      retainable: true,
      retainUntil: '2026-07-22T12:00:00+00:00',
      retainGroup: 'chat:5',
      token: 'tok-1',
      notification_type: 'ChatableUnreadNotification',
      redirector: '/n/1',
      hasRedirector: true,
      redirectorText: 'Look',
    })

    expect(commit).toHaveBeenCalledWith(RETAINABLE_NOTIFICATION.PUSH, {
      title: 'Chat',
      description: 'New message',
      detail: null,
      variant: 'info',
      redirector: '/n/1',
      hasRedirector: true,
      redirectorText: 'Look',
      retainUntil: '2026-07-22T12:00:00+00:00',
      retainGroup: 'chat:5',
      token: 'tok-1',
      notificationType: 'ChatableUnreadNotification',
    })
    expect(commit).not.toHaveBeenCalledWith(BROADCAST_TOAST.PUSH, expect.anything())
  })

  test('does not commit when payload has no toast content', () => {
    const commit = vi.fn()
    pushBroadcastToastFromPayload({ commit }, { status: 'started' })
    expect(commit).not.toHaveBeenCalled()
  })
})
