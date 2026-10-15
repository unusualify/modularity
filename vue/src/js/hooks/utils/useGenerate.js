// hooks/utils/useGenerate.js
import { computed, getCurrentInstance, inject } from 'vue'
import { router } from '@inertiajs/vue3'
import { useDisplay } from 'vuetify'
import { useConfig } from '@/hooks'
import { isSameUrl } from '@/utils/pushState'

/**
 * Append nested params in Laravel's bracket notation (arrays keep their indexes,
 * so e.g. sortBy[0][key] / sortBy[0][order] stay together)
 */
const appendQueryParameters = (searchParams, value, prefix = '') => {
  if(value === null || value === undefined) return

  if(typeof value === 'object') {
    Object.entries(value).forEach(([key, item]) => {
      appendQueryParameters(searchParams, item, prefix ? `${prefix}[${key}]` : key)
    })
  } else {
    searchParams.append(prefix, String(value))
  }
}

export default function useGenerate(props, context) {
  const { smAndUp } = useDisplay()
  const { shouldUseInertia } = useConfig()
  // provided by useTable, only available for actions rendered inside a table
  const tableRequestPayload = getCurrentInstance() ? inject('tableRequestPayload', null) : null

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

        // file downloads: pass the table's current query (page, itemsPerPage, sortBy, search, filter)
        // and the columns hidden via the table cog (see useTableHeaders), and skip inertia
        if(action.download) {
          const url = new URL(action.href, window.location.origin)

          if(tableRequestPayload) {
            appendQueryParameters(url.searchParams, tableRequestPayload())
          }

          let hiddenColumns = ''
          try {
            hiddenColumns = localStorage.getItem(`table_unvisible_columns_${window.location.pathname}`) ?? ''
          } catch (error) {}

          if(hiddenColumns) url.searchParams.set('hidden_columns', hiddenColumns)

          window.location.href = url.toString()
        } else if(shouldUseInertia.value && isSameUrl(action.href, window.location.href)) {
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
