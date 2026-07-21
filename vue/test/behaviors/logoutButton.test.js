import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest'
import logoutButton from '../../src/js/behaviors/logoutButton.js'

describe('logoutButton behavior', () => {
  let logoutForm
  let logoutBtn

  beforeEach(() => {
    logoutForm = document.createElement('form')
    logoutForm.setAttribute('data-logout-form', '')
    logoutForm.submit = vi.fn()

    logoutBtn = document.createElement('button')
    logoutBtn.setAttribute('data-logout-btn', '')

    document.body.appendChild(logoutForm)
    document.body.appendChild(logoutBtn)
  })

  afterEach(() => {
    document.body.innerHTML = ''
  })

  test('registers click listener when logout form exists', () => {
    logoutButton()

    const clickEvent = new MouseEvent('click', { bubbles: true })
    logoutBtn.dispatchEvent(clickEvent)

    expect(logoutForm.submit).toHaveBeenCalled()
  })

  test('disconnects Echo before submitting logout form', () => {
    const disconnectEcho = vi.fn()
    window.disconnectEcho = disconnectEcho

    logoutButton()

    const clickEvent = new MouseEvent('click', { bubbles: true })
    logoutBtn.dispatchEvent(clickEvent)

    expect(disconnectEcho).toHaveBeenCalled()
    expect(logoutForm.submit).toHaveBeenCalled()

    delete window.disconnectEcho
  })

  test('falls back to Echo.disconnect when disconnectEcho helper is missing', () => {
    window.Echo = { disconnect: vi.fn() }

    logoutButton()

    const clickEvent = new MouseEvent('click', { bubbles: true })
    logoutBtn.dispatchEvent(clickEvent)

    expect(window.Echo.disconnect).toHaveBeenCalled()
    expect(logoutForm.submit).toHaveBeenCalled()

    delete window.Echo
  })

  test('does nothing when logout form does not exist', () => {
    document.body.removeChild(logoutForm)

    expect(() => logoutButton()).not.toThrow()
  })

  test('ignores clicks on non-logout elements', () => {
    logoutButton()

    const otherBtn = document.createElement('button')
    document.body.appendChild(otherBtn)

    const clickEvent = new MouseEvent('click', { bubbles: true })
    Object.defineProperty(clickEvent, 'target', { value: otherBtn })

    document.body.dispatchEvent(clickEvent)

    expect(logoutForm.submit).not.toHaveBeenCalled()
  })
})
