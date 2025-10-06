// hooks/utils/useGenerate.js
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useDisplay } from 'vuetify'
import { useConfig } from '@/hooks'
import { isSameUrl } from '@/utils/pushState'

export default function useGenerate(props, context) {
  const { smAndUp } = useDisplay()
  const { shouldUseInertia } = useConfig()

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
      // rounded: hasIcon ? true : (defaultButtonProps.rounded ?? null),
      rounded: !smAndUp.value ? true : defaultButtonProps.rounded,
    }
  })

  const generateButtonProps = (action) => {

    let extraProps = {}

    if(action.href){
      extraProps['onClick'] = (e) => {
        e.preventDefault()
        const target = action.target ?? '_blank'

        if(shouldUseInertia.value && isSameUrl(action.href, window.location.href)) {
          router.visit(action.href)
        } else if (target !== '_blank') {
          router.visit(action.href, { target })
        } else {
          window.open(action.href, target)
        }
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
    generateButtonProps,
    generatedButtonProps
  }
}
