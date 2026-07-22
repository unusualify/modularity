/**
 * Vuex mutation types for retainable (bottom-right tray) notifications.
 */
export const PUSH = '__pushRetainableNotification'
export const DISMISS = '__dismissRetainableNotification'
export const DISMISS_BY_GROUP = '__dismissRetainableNotificationByGroup'
export const PRUNE = '__pruneRetainableNotifications'
export const HYDRATE = '__hydrateRetainableNotifications'
export const TOGGLE = '__toggleRetainableNotificationsPanel'
export const CLEAR = '__clearRetainableNotifications'

export default {
  PUSH,
  DISMISS,
  DISMISS_BY_GROUP,
  PRUNE,
  HYDRATE,
  TOGGLE,
  CLEAR,
}
