import { dataGet } from '@/utils/helpers'

const escapeHtml = (value) => String(value)
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#39;')

export const formatResponseFieldValue = (value) => {
  if (value === null || value === undefined || value === '') {
    return '—'
  }

  if (typeof value === 'object') {
    return JSON.stringify(value, null, 2)
  }

  return String(value)
}

export const resolveResponseDisplayFields = (payload, action = {}, response = {}) => {
  if (Array.isArray(response?.data?.display) && response.data.display.length > 0) {
    return response.data.display
  }

  if (!Array.isArray(action.responseFields) || action.responseFields.length === 0) {
    return null
  }

  return action.responseFields.map((field) => ({
    key: field.key,
    label: field.label ?? field.key,
    value: dataGet(payload, field.key),
  }))
}

export const buildStructuredResponseDescription = (fields) => {
  if (!Array.isArray(fields) || fields.length === 0) {
    return null
  }

  return fields.map(({ key, label, value }) => {
    const text = formatResponseFieldValue(value)
    const escapedLabel = escapeHtml(label || key)
    const escapedValue = escapeHtml(text).replace(/\n/g, '<br>')

    return `<div class="mb-3"><div class="text-body-small text-medium-emphasis">${escapedLabel}</div><div class="text-body-medium">${escapedValue}</div></div>`
  }).join('')
}

export const buildRemoteApiResponseDescription = (payload, action = {}, response = {}) => {
  const displayMode = action.responseDisplay
    ?? response?.data?.display_mode
    ?? 'fields'

  if (displayMode === 'raw') {
    return typeof payload === 'string'
      ? payload
      : JSON.stringify(payload, null, 2)
  }

  const fields = resolveResponseDisplayFields(payload, action, response)
  const structured = buildStructuredResponseDescription(fields)

  if (structured) {
    return structured
  }

  return typeof payload === 'string'
    ? payload
    : JSON.stringify(payload, null, 2)
}
