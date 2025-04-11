// hooks/utils/useGenerate.js
import { computed } from 'vue'
import { useDisplay } from 'vuetify'

export default function useGenerate(props, context) {

  const { smAndUp } = useDisplay()

  const generatedButtonProps = computed(() => {

    if(!props)
      return {}

    const defaultButtonProps = generateButtonProps(props)
    const hasIcon = defaultButtonProps.icon
      || defaultButtonProps.prependIcon
      || defaultButtonProps.appendIcon
      || props.icon
      || props.prependIcon
      || props.appendIcon

    return {
      ...defaultButtonProps,

      // size: props.size ?? 'default',
      // rounded: props.forceLabel ? null : true,
      icon: hasIcon && !smAndUp.value ? hasIcon : defaultButtonProps.icon,
      density: (hasIcon && !smAndUp.value) ? 'compact' : (props.density ?? 'comfortable'),
      rounded: hasIcon ? false : (defaultButtonProps.rounded ?? false),
    }
  })

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
      rounded: action.forceLabel ? null : false,
    }
  }

  return {
    generateButtonProps,
    generatedButtonProps
  }
}
