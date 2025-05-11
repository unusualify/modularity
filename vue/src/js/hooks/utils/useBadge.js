// hooks/utils/useBadge.js
import { computed } from 'vue'

export default function useBadge(props, context) {

  const isBadge = (action) => {
    if(!window.__isset(action.badge)){
      return false
    }

    let badge = action.badge

    if(window.__isString(badge)){
      badge = parseInt(badge)
    }

    return badge > 0
  }

  const badgeProps = (action) => {
    return {
      ...(action.componentProps ?? {}),
      content: action.badgeContent ?? action.badge,
      color: action.badgeColor ?? 'warning',
      textColor: action.badgeTextColor ?? 'white',
    }
  }

  return {
    isBadge,
    badgeProps
  }
}
