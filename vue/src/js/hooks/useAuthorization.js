// useAuthorization.js
import { useStore } from 'vuex'

export default function useAuthorization() {
  const store = useStore()

  const can = (permission, moduleName = null) => {
    const name = moduleName ? moduleName + '_' + permission : permission

    return store.getters.isSuperAdmin || store.getters.userPermissions[name]
  }

  const hasRoles = (roles) => {
    if(window.__isString(roles)){
      roles = roles.split(',').map(role => role.trim())
    }

    return store.getters.userRoles.some(role => roles.includes(role))
  }

  return {
    can,
    hasRoles
  }
}
