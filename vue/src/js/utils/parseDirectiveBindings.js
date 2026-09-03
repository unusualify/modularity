/**
 * Normalize `ue-recursive-stuff` directive maps for `withDirectives`.
 *
 * Keys may include modifiers (`scrollable.height`).
 * Values may be a raw binding, `false` to skip, or a descriptor:
 * `{ value, arg, modifiers }`.
 */

export function parseDirectiveKey (directiveName) {
  const [name, ...modParts] = String(directiveName).split('.')
  const modifiers = {}

  for (const part of modParts) {
    if (part) {
      modifiers[part] = true
    }
  }

  return { name, modifiers }
}

export function isDirectiveDescriptor (value) {
  if (value == null || typeof value !== 'object' || Array.isArray(value)) {
    return false
  }

  const keys = Object.keys(value)

  return keys.length > 0 && keys.every((key) => (
    key === 'value' || key === 'arg' || key === 'modifiers'
  ))
}

export function isEnabledDirectiveValue (value) {
  return value !== false && value != null
}

export function normalizeDirectiveBinding (directiveName, rawValue) {
  const { name, modifiers: keyModifiers } = parseDirectiveKey(directiveName)

  if (isDirectiveDescriptor(rawValue)) {
    return {
      name,
      value: rawValue.value,
      arg: rawValue.arg ?? null,
      modifiers: {
        ...keyModifiers,
        ...(rawValue.modifiers || {}),
      },
    }
  }

  return {
    name,
    value: rawValue,
    arg: null,
    modifiers: keyModifiers,
  }
}
