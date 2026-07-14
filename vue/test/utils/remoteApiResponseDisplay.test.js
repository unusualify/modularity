import { describe, expect, test } from 'vitest'
import {
  buildRemoteApiResponseDescription,
  buildStructuredResponseDescription,
  formatResponseFieldValue,
  resolveResponseDisplayFields,
} from '../../src/js/utils/remoteApiResponseDisplay.js'

describe('remoteApiResponseDisplay', () => {
  test('formatResponseFieldValue renders empty values as em dash', () => {
    expect(formatResponseFieldValue(null)).toBe('—')
    expect(formatResponseFieldValue('')).toBe('—')
  })

  test('resolveResponseDisplayFields prefers server display payload', () => {
    const payload = { name: 'From payload' }
    const response = {
      data: {
        display: [{ key: 'name', label: 'Name', value: 'From server' }],
      },
    }

    expect(resolveResponseDisplayFields(payload, {}, response)).toEqual([
      { key: 'name', label: 'Name', value: 'From server' },
    ])
  })

  test('buildStructuredResponseDescription renders labeled rows', () => {
    const html = buildStructuredResponseDescription([
      { key: 'name', label: 'Name', value: 'Europe' },
      { key: 'description', label: 'Description', value: 'Regional package' },
    ])

    expect(html).toContain('Name')
    expect(html).toContain('Europe')
    expect(html).toContain('Regional package')
  })

  test('buildRemoteApiResponseDescription falls back to raw json', () => {
    const payload = { id: 1, name: 'Europe' }

    expect(buildRemoteApiResponseDescription(payload, { responseDisplay: 'raw' }))
      .toBe(JSON.stringify(payload, null, 2))
  })
})
