import { describe, expect, test, vi } from 'vitest'
import {
  pushBroadcastToastFromPayload,
  resolveBroadcastToastPayload,
} from '../../src/js/plugins/broadcasting.js'
import { BROADCAST_TOAST } from '../../src/js/store/mutations/index.js'

vi.mock('laravel-echo', () => ({ default: vi.fn() }))
vi.mock('pusher-js', () => ({ default: {} }))

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
    })
  })

  test('does not commit when payload has no toast content', () => {
    const commit = vi.fn()
    pushBroadcastToastFromPayload({ commit }, { status: 'started' })
    expect(commit).not.toHaveBeenCalled()
  })
})
