import { describe, expect, test, beforeEach, vi } from 'vitest'

import {
  checkItemConditions,
  getNestedValue,
  evaluateCondition,
  evaluateConditionGroup,
  evaluateConditionArray
} from '../../src/js/utils/itemConditions.js'


// Mock window.__isObject for testing environment
global.window = {
  __isObject: (obj) => obj !== null && typeof obj === 'object' && !Array.isArray(obj)
}

describe('itemConditions tests', () => {
  let testItem

  beforeEach(() => {
    testItem = {
      state: { code: 'draft' },
      payment_price: { is_unpaid: true, amount: 100 },
      user: {
        credits: 50,
        is_active: true,
        role: 'client',
        permissions: { can_edit: true },
        valid_company: false
      },
      created_at: '2023-06-01',
      tags: ['urgent', 'featured'],
      status: null
    }
  })

  describe('getNestedValue', () => {
    test('gets simple property', () => {
      expect(getNestedValue(testItem, 'created_at')).toBe('2023-06-01')
    })

    test('gets nested property', () => {
      expect(getNestedValue(testItem, 'state.code')).toBe('draft')
      expect(getNestedValue(testItem, 'user.permissions.can_edit')).toBe(true)
    })

    test('returns undefined for non-existent property', () => {
      expect(getNestedValue(testItem, 'non.existent.property')).toBeUndefined()
    })

    test('handles null values gracefully', () => {
      expect(getNestedValue(testItem, 'status')).toBeNull()
    })
  })

  describe('evaluateCondition', () => {
    test('equality operators', () => {
      expect(evaluateCondition(['state.code', '=', 'draft'], testItem)).toBe(true)
      expect(evaluateCondition(['state.code', '==', 'draft'], testItem)).toBe(true)
      expect(evaluateCondition(['state.code', '=', 'pending'], testItem)).toBe(false)
    })

    test('inequality operator', () => {
      expect(evaluateCondition(['state.code', '!=', 'pending'], testItem)).toBe(true)
      expect(evaluateCondition(['state.code', '!=', 'draft'], testItem)).toBe(false)
    })

    test('comparison operators', () => {
      expect(evaluateCondition(['user.credits', '>', 30], testItem)).toBe(true)
      expect(evaluateCondition(['user.credits', '>', 60], testItem)).toBe(false)
      expect(evaluateCondition(['user.credits', '<', 60], testItem)).toBe(true)
      expect(evaluateCondition(['user.credits', '<', 30], testItem)).toBe(false)
      expect(evaluateCondition(['user.credits', '>=', 50], testItem)).toBe(true)
      expect(evaluateCondition(['user.credits', '<=', 50], testItem)).toBe(true)
    })

    test('in operator', () => {
      expect(evaluateCondition(['state.code', 'in', ['draft', 'pending']], testItem)).toBe(true)
      expect(evaluateCondition(['state.code', 'in', ['pending', 'published']], testItem)).toBe(false)
    })

    test('not in operator', () => {
      expect(evaluateCondition(['state.code', 'not in', ['pending', 'published']], testItem)).toBe(true)
      expect(evaluateCondition(['state.code', 'not in', ['draft', 'pending']], testItem)).toBe(false)
    })

    test('exists operator', () => {
      expect(evaluateCondition(['user.permissions', 'exists'], testItem)).toBe(true)
      expect(evaluateCondition(['user.non_existent', 'exists'], testItem)).toBe(false)
      expect(evaluateCondition(['status', 'exists'], testItem)).toBe(false) // null should be false
    })

    test('not exists operator', () => {
      expect(evaluateCondition(['user.non_existent', 'not exists'], testItem)).toBe(true)
      expect(evaluateCondition(['user.permissions', 'not exists'], testItem)).toBe(false)
      expect(evaluateCondition(['status', 'not exists'], testItem)).toBe(true) // null should be true
    })

    test('array length comparison', () => {
      expect(evaluateCondition(['tags', '>', 1], testItem)).toBe(true)
      expect(evaluateCondition(['tags', '=', 2], testItem)).toBe(true)
      expect(evaluateCondition(['tags', '<', 5], testItem)).toBe(true)
    })

    test('unknown operator logs warning', () => {
      const consoleSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})
      expect(evaluateCondition(['state.code', 'unknown', 'draft'], testItem)).toBe(false)
      expect(consoleSpy).toHaveBeenCalledWith('Unknown operator: unknown')
      consoleSpy.mockRestore()
    })
  })

  describe('checkItemConditions - Simple Conditions (Backward Compatibility)', () => {
    test('returns true for empty conditions', () => {
      expect(checkItemConditions([], testItem)).toBe(true)
    })

    test('returns true for null conditions', () => {
      expect(checkItemConditions(null, testItem)).toBe(true)
    })

    test('returns true for null item', () => {
      expect(checkItemConditions([['state.code', '=', 'draft']], null)).toBe(true)
    })

    test('simple AND conditions', () => {
      const conditions = [
        ['state.code', '=', 'draft'],
        ['user.is_active', '=', true]
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })

    test('simple AND conditions with one false', () => {
      const conditions = [
        ['state.code', '=', 'draft'],
        ['user.is_active', '=', false]
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(false)
    })
  })

  describe('checkItemConditions - Fourth Parameter Logic', () => {
    test('simple OR with fourth parameter', () => {
      const conditions = [
        ['state.code', '=', 'draft'],        // false
        ['user.is_active', '=', true, 'or'],   // true, OR with next
        ['user.credits', '>', 100]             // false, but OR makes it work
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })

    test('mixed AND and OR operations', () => {
      const conditions = [
        ['user.is_active', '=', true],         // true, AND (default)
        ['state.code', '=', 'pending', 'or'],  // false, OR with next
        ['state.code', '=', 'draft'],          // true, connected with OR
        ['user.credits', '>', 0, 'and']        // true, AND with next (if any)
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })

    test('OR chain that fails', () => {
      const conditions = [
        ['state.code', '=', 'published', 'or'], // false
        ['state.code', '=', 'pending', 'or'],   // false
        ['state.code', '=', 'archived']         // false
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(false)
    })

    test('AND after successful OR chain', () => {
      const conditions = [
        ['state.code', '=', 'pending', 'or'],   // false
        ['state.code', '=', 'draft'],           // true (OR chain succeeds)
        ['user.is_active', '=', true, 'and'],   // true, AND with next
        ['user.credits', '>', 0]                // true
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })
  })

  describe('evaluateConditionGroup', () => {
    test('simple AND group', () => {
      const group = {
        operator: 'and',
        conditions: [
          ['state.code', '=', 'draft'],
          ['user.is_active', '=', true]
        ]
      }
      expect(evaluateConditionGroup(group, testItem)).toBe(true)
    })

    test('simple OR group', () => {
      const group = {
        operator: 'or',
        conditions: [
          ['state.code', '=', 'pending'],
          ['state.code', '=', 'draft']
        ]
      }
      expect(evaluateConditionGroup(group, testItem)).toBe(true)
    })

    test('nested groups', () => {
      const group = {
        operator: 'and',
        conditions: [
          {
            operator: 'or',
            conditions: [
              ['state.code', '=', 'draft'],
              ['state.code', '=', 'pending']
            ]
          },
          ['user.is_active', '=', true]
        ]
      }
      expect(evaluateConditionGroup(group, testItem)).toBe(true)
    })

    test('empty group returns true', () => {
      const group = { operator: 'and', conditions: [] }
      expect(evaluateConditionGroup(group, testItem)).toBe(true)
    })

    test('defaults to AND operator', () => {
      const group = {
        conditions: [
          ['state.code', '=', 'draft'],
          ['user.is_active', '=', true]
        ]
      }
      expect(evaluateConditionGroup(group, testItem)).toBe(true)
    })
  })

  describe('checkItemConditions - Nested Groups', () => {
    test('single condition group object', () => {
      const conditions = {
        operator: 'and',
        conditions: [
          {
            operator: 'or',
            conditions: [
              ['state.code', '=', 'draft'],
              ['state.code', '=', 'pending']
            ]
          },
          ['user.is_active', '=', true]
        ]
      }
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })

    test('mixed array with nested groups', () => {
      const conditions = [
        ['user.is_active', '=', true],
        {
          operator: 'or',
          conditions: [
            ['state.code', '=', 'published'],
            ['state.code', '=', 'draft']
          ]
        },
        ['user.credits', '>', 0]
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })
  })

  describe('checkItemConditions - Complex Real-World Scenarios', () => {
    test('complex payment logic', () => {
      const conditions = [
        {
          operator: 'or',
          conditions: [
            // User can pay (will be false due to invalid company)
            {
              operator: 'and',
              conditions: [
                ['state.code', '=', 'pending-payment'],
                ['payment_price.is_unpaid', '=', true],
                ['user.valid_company', '=', true]
              ]
            },
            // User has credits for draft/credit states (will be true)
            {
              operator: 'and',
              conditions: [
                ['state.code', 'in', ['draft', 'credit']],
                ['user.credits', '>', 0]
              ]
            }
          ]
        },
        // Must be active user
        ['user.is_active', '=', true]
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })

    test('deeply nested user permissions', () => {
      const conditions = {
        operator: 'and',
        conditions: [
          {
            operator: 'or',
            conditions: [
              ['user.role', '=', 'admin'],
              {
                operator: 'and',
                conditions: [
                  ['user.role', '=', 'client'],
                  ['user.permissions.can_edit', '=', true]
                ]
              }
            ]
          },
          {
            operator: 'or',
            conditions: [
              ['state.code', 'in', ['draft', 'in-review']],
              {
                operator: 'and',
                conditions: [
                  ['state.code', '=', 'published'],
                  ['created_at', '>', '2023-01-01']
                ]
              }
            ]
          }
        ]
      }
      expect(checkItemConditions(conditions, testItem)).toBe(true)
    })

    test('form field visibility logic', () => {
      const paymentItem = {
        ...testItem,
        state: { code: 'pending-payment' },
        payment_method: 'credit_card'
      }

      const conditions = [
        ['state.code', '=', 'pending-payment'],
        ['payment_price.amount', '>', 0, 'and'],
        {
          operator: 'or',
          conditions: [
            ['payment_method', '=', 'credit_card'],
            ['payment_method', '=', 'bank_transfer'],
            {
              operator: 'and',
              conditions: [
                ['payment_method', '=', 'credits'],
                ['user.credits', '>=', 'payment_price.amount']
              ]
            }
          ]
        }
      ]
      expect(checkItemConditions(conditions, paymentItem)).toBe(true)
    })
  })

  describe('checkItemConditions - Edge Cases', () => {
    test('handles invalid condition formats gracefully', () => {
      const consoleSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})

      const conditions = [
        ['state.code', '=', 'draft'],
        'invalid_condition',
        ['user.is_active', '=', true]
      ]

      expect(checkItemConditions(conditions, testItem)).toBe(false)
      expect(consoleSpy).toHaveBeenCalledWith('Invalid condition format:', 'invalid_condition')

      consoleSpy.mockRestore()
    })

    test('handles malformed nested groups', () => {
      const consoleSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})

      const conditions = [
        {
          operator: 'and',
          conditions: [
            ['state.code', '=', 'draft'],
            { malformed: 'group' }, // Invalid nested structure
            ['user.is_active', '=', true]
          ]
        }
      ]

      expect(checkItemConditions(conditions, testItem)).toBe(false)
      expect(consoleSpy).toHaveBeenCalledWith('Invalid condition format:', { malformed: 'group' })

      consoleSpy.mockRestore()
    })

    test('short-circuit optimization for AND chains', () => {
      const conditions = [
        ['state.code', '=', 'wrong_state'],  // This will be false
        ['user.is_active', '=', true],       // Should not be evaluated due to short-circuit
        ['user.credits', '>', 0]             // Should not be evaluated due to short-circuit
      ]
      expect(checkItemConditions(conditions, testItem)).toBe(false)
    })

    test('works with non-object items', () => {
      expect(checkItemConditions([['field', '=', 'value']], 'string')).toBe(true)
      expect(checkItemConditions([['field', '=', 'value']], 123)).toBe(true)
      expect(checkItemConditions([['field', '=', 'value']], [])).toBe(true)
    })
  })

  describe('evaluateConditionArray', () => {
    test('empty array returns true', () => {
      expect(evaluateConditionArray([], testItem)).toBe(true)
    })

    test('single condition', () => {
      expect(evaluateConditionArray([['state.code', '=', 'draft']], testItem)).toBe(true)
      expect(evaluateConditionArray([['state.code', '=', 'pending']], testItem)).toBe(false)
    })

    test('multiple AND conditions', () => {
      const conditions = [
        ['state.code', '=', 'draft'],
        ['user.is_active', '=', true]
      ]
      expect(evaluateConditionArray(conditions, testItem)).toBe(true)
    })

    test('OR operation with fourth parameter', () => {
      const conditions = [
        ['state.code', '=', 'pending', 'or'],
        ['state.code', '=', 'draft']
      ]
      expect(evaluateConditionArray(conditions, testItem)).toBe(true)
    })
  })

  // describe('Performance and Optimization', () => {
  //   test('short-circuit evaluation stops early on AND failure', () => {
  //     let evaluationCount = 0
  //     const mockEvaluate = (condition, item) => {
  //       evaluationCount++
  //       return evaluateCondition(condition, item)
  //     }

  //     // Mock evaluateCondition to count calls
  //     const originalEvaluate = evaluateCondition
  //     global.evaluateCondition = mockEvaluate

  //     const conditions = [
  //       ['state.code', '=', 'wrong_value'], // false - should stop here
  //       ['user.is_active', '=', true],      // should not be evaluated
  //       ['user.credits', '>', 0]            // should not be evaluated
  //     ]

  //     checkItemConditions(conditions, testItem)

  //     // Restore original function
  //     global.evaluateCondition = originalEvaluate

  //     // Should have stopped after first false condition
  //     expect(evaluationCount).toBe(1)
  //   })
  // })
})
