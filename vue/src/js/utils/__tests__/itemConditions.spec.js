import { describe, it, expect, beforeAll } from 'vitest'
import { isObject } from 'lodash-es'
import { handleItemConditions, handleDisabledTextConditions } from '../itemConditions'

const closedNotice = 'This support request has been completed. This conversation is now read-only.'

const makeChatInput = () => ({
  type: 'input-chat',
  conditions: [['state.code', 'in', ['in-progress'], 'and']],
  disabledOnCondition: true,
  disabledText: closedNotice,
  disabledTextConditions: [['state.code', '=', 'closed']]
})

describe('itemConditions', () => {
  beforeAll(() => {
    window.__isObject ??= isObject
  })

  describe('handleDisabledTextConditions', () => {
    it('keeps disabledText when conditions are met', () => {
      const input = makeChatInput()
      handleDisabledTextConditions({ state: { code: 'closed' } }, input)
      expect(input.disabledText).toBe(closedNotice)
    })

    it('clears disabledText when conditions are not met', () => {
      const input = makeChatInput()
      handleDisabledTextConditions({ state: { code: 'pending' } }, input)
      expect(input.disabledText).toBeNull()
    })

    it('restores disabledText when the model changes back', () => {
      const input = makeChatInput()
      handleDisabledTextConditions({ state: { code: 'pending' } }, input)
      handleDisabledTextConditions({ state: { code: 'closed' } }, input)
      expect(input.disabledText).toBe(closedNotice)
    })

    describe('with multiple notices (disabledTexts)', () => {
      const pendingNotice = "Messaging is unavailable until your support ticket is set to 'In Progress'."

      const makeMultiNoticeInput = () => ({
        type: 'input-chat',
        disabledTextColor: 'success',
        disabledTexts: [
          { text: closedNotice, conditions: [['state.code', '=', 'closed']] },
          { text: pendingNotice, color: 'warning', conditions: [['state.code', '=', 'pending']] }
        ]
      })

      it('picks the closed notice with the default color', () => {
        const input = makeMultiNoticeInput()
        handleDisabledTextConditions({ state: { code: 'closed' } }, input)
        expect(input.disabledText).toBe(closedNotice)
        expect(input.disabledTextColor).toBe('success')
      })

      it('picks the pending notice with its own color', () => {
        const input = makeMultiNoticeInput()
        handleDisabledTextConditions({ state: { code: 'pending' } }, input)
        expect(input.disabledText).toBe(pendingNotice)
        expect(input.disabledTextColor).toBe('warning')
      })

      it('restores the default color when switching back', () => {
        const input = makeMultiNoticeInput()
        handleDisabledTextConditions({ state: { code: 'pending' } }, input)
        handleDisabledTextConditions({ state: { code: 'closed' } }, input)
        expect(input.disabledText).toBe(closedNotice)
        expect(input.disabledTextColor).toBe('success')
      })

      it('clears the notice when no condition matches', () => {
        const input = makeMultiNoticeInput()
        handleDisabledTextConditions({ state: { code: 'in-progress' } }, input)
        expect(input.disabledText).toBeNull()
      })
    })

    it('leaves items without disabledTextConditions untouched', () => {
      const input = { disabledText: 'Read only' }
      handleDisabledTextConditions({ state: { code: 'pending' } }, input)
      expect(input).toEqual({ disabledText: 'Read only' })
    })
  })

  describe('handleItemConditions', () => {
    it('disables the chat and shows the notice for closed tickets', () => {
      const res = handleItemConditions(makeChatInput().conditions, { state: { code: 'closed' } }, makeChatInput())
      expect(res.disabled).toBe(true)
      expect(res.disabledText).toBe(closedNotice)
    })

    it('disables the chat without the notice for pending tickets', () => {
      const res = handleItemConditions(makeChatInput().conditions, { state: { code: 'pending' } }, makeChatInput())
      expect(res.disabled).toBe(true)
      expect(res.disabledText).toBeNull()
    })

    it('keeps the chat enabled for in-progress tickets', () => {
      const res = handleItemConditions(makeChatInput().conditions, { state: { code: 'in-progress' } }, makeChatInput())
      expect(res.disabled).toBeFalsy()
    })
  })
})
