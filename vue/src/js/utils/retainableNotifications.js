/**
 * Helpers for retainable notification tray (unread within TTL).
 */

export const RETAINABLE_DEFAULT_HOURS = 6

/**
 * @param {object} item
 * @param {number} [nowMs]
 * @returns {boolean}
 */
export function isRetainableItemActive (item, nowMs = Date.now()) {
  if (!item || item.dismissed) {
    return false
  }

  if (!item.retainUntil) {
    return true
  }

  const until = Date.parse(item.retainUntil)

  return Number.isFinite(until) && until > nowMs
}

/**
 * @param {object[]} items
 * @param {number} [nowMs]
 * @returns {object[]}
 */
export function filterActiveRetainableItems (items, nowMs = Date.now()) {
  return (items || []).filter((item) => isRetainableItemActive(item, nowMs))
}

/**
 * Upsert a retainable tray item.
 * When `retainGroup` is set, replace by group (one slot per logical entity);
 * otherwise match by `token` then `id`.
 *
 * @param {object[]} items
 * @param {object} next
 * @returns {object[]}
 */
export function upsertRetainableItem (items, next) {
  const list = [...(items || [])]
  const group = next.retainGroup != null && String(next.retainGroup).trim() !== ''
    ? String(next.retainGroup)
    : null

  const index = group
    ? list.findIndex((item) => item.retainGroup && item.retainGroup === group)
    : list.findIndex((item) => (item.token && item.token === next.token) || item.id === next.id)

  if (index === -1) {
    list.unshift(next)

    return list
  }

  list.splice(index, 1, { ...list[index], ...next, dismissed: false })

  return list
}

/**
 * Remove all items matching a retainGroup.
 *
 * @param {object[]} items
 * @param {string} retainGroup
 * @returns {object[]}
 */
export function dismissRetainableByGroup (items, retainGroup) {
  if (!retainGroup) {
    return items || []
  }

  return (items || []).filter((item) => item.retainGroup !== retainGroup)
}

/**
 * @param {number|string|null|undefined} userId
 * @returns {string|null}
 */
export function retainableStorageKey (userId) {
  if (userId == null || userId === '') {
    return null
  }

  return `modularous.retainableNotifications.${userId}`
}

/**
 * @param {string|null} key
 * @returns {object[]}
 */
export function loadRetainableFromStorage (key) {
  if (!key || typeof localStorage === 'undefined') {
    return []
  }

  try {
    const raw = localStorage.getItem(key)
    if (!raw) {
      return []
    }
    const parsed = JSON.parse(raw)

    return Array.isArray(parsed) ? filterActiveRetainableItems(parsed) : []
  } catch (_) {
    return []
  }
}

/**
 * @param {string|null} key
 * @param {object[]} items
 */
export function saveRetainableToStorage (key, items) {
  if (!key || typeof localStorage === 'undefined') {
    return
  }

  try {
    localStorage.setItem(key, JSON.stringify(filterActiveRetainableItems(items)))
  } catch (_) {
    // ignore quota / private mode
  }
}
