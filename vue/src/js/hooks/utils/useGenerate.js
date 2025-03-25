// hooks/utils/useGenerate.js
import { computed } from 'vue'

export default function useGenerate(props, context) {

  const generateButtonProps = (action) => {

    let extraProps = {}
    if(action.href){
      extraProps['onClick'] = (e) => {
        e.preventDefault()
        window.open(action.href, action.target ?? '_blank')
      }
    }
    return {
      ...(action.componentProps ?? {}),
      ...extraProps,
      icon: !action.forceLabel ? action.icon : null,
      text: action.forceLabel ? action.label : null,
      color: action.color,
      variant: action.variant,
      density: action.density ?? 'comfortable',
      size: action.size ?? 'default',
      disabled: action.disabled ?? action.componentProps?.disabled ?? false,
      rounded: action.forceLabel ? null : true,
    }
  }

  return {
    generateButtonProps
  }
}
