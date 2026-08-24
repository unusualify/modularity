import { processInputs } from '@/utils/schema'

const SKIP_TYPES = [
  'hidden',
  'v-sheet',
  'filepond',
  'filepond-avatar',
  'file',
  'image',
  'preview',
  'title',
  'divider',
  'dynamic-component',
]

export const isPreviewPasswordInput = (input) => {
  const type = String(input?.type ?? '').toLowerCase()
  const name = String(input?.name ?? '').toLowerCase()

  return type === 'password' || name.includes('password')
}

export const isPreviewBooleanInput = (input) => {
  return ['switch', 'checkbox', 'input-terms-checkbox'].includes(input?.type)
}

export const isPreviewBooleanTrue = (input, value) => {
  const trueValue = Object.prototype.hasOwnProperty.call(input ?? {}, 'trueValue')
    ? input.trueValue
    : true

  return value === trueValue || value === true || value === 1 || value === '1'
}

const shouldSkipPreviewInput = (input) => {
  if (!input || input.hidden || input.slotable) {
    return true
  }

  if (SKIP_TYPES.includes(input.type)) {
    return true
  }

  if (String(input.name ?? '').startsWith('gap')) {
    return true
  }

  return false
}

const resolveSelectTitle = (input, value) => {
  const items = Array.isArray(input.items) ? input.items : []
  const itemValue = input.itemValue ?? 'id'
  const itemTitle = input.itemTitle ?? 'name'
  const matched = items.find((item) => {
    if (item == null) {
      return false
    }
    if (typeof item !== 'object') {
      return item === value
    }
    return item[itemValue] === value || item.value === value
  })

  if (matched == null) {
    return value
  }

  if (typeof matched !== 'object') {
    return matched
  }

  return matched[itemTitle] ?? matched.label ?? matched.title ?? matched.name ?? value
}

export const getFormPreviewFields = (schema) => {
  if (!schema || typeof schema !== 'object') {
    return []
  }

  return Object.values(processInputs(schema)).filter((input) => !shouldSkipPreviewInput(input))
}

export const getFormPreviewValue = (input, model) => {
  const name = input?.name
  const value = name ? model?.[name] : undefined

  if (isPreviewPasswordInput(input)) {
    return { kind: 'password', value: '••••••••' }
  }

  if (isPreviewBooleanInput(input)) {
    return { kind: 'boolean', value: isPreviewBooleanTrue(input, value) }
  }

  if (value === null || value === undefined || value === '') {
    return { kind: 'empty', value: null }
  }

  if (['select', 'autocomplete', 'combobox'].includes(input.type)) {
    return { kind: 'text', value: resolveSelectTitle(input, value) }
  }

  if (Array.isArray(value)) {
    return { kind: 'text', value: value.join(', ') }
  }

  if (typeof value === 'object') {
    return { kind: 'text', value: value.title ?? value.name ?? value.label ?? '' }
  }

  return { kind: 'text', value }
}
