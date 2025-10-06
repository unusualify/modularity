// hooks/utils/useBadge.js
import { computed } from 'vue'
import { toNumber, isNumber } from 'lodash'

export default function useBadge(props, context) {

  const isBadge = (action) => {
    if(!window.__isset(action.badge)){
      return false
    }

    let badge = action.badge

    if(window.__isString(badge)){
      let badgeNumber = toNumber(badge)

      if(!isNaN(badgeNumber))
        return badgeNumber > 0
    }

    return badge
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
