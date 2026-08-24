// hooks/utils/useSurface.js
import { computed, reactive, toValue } from 'vue'
import { propsFactory } from 'vuetify/lib/util/index.mjs'
import { makeBorderProps, useBorder } from 'vuetify/lib/composables/border.js'
import { makeElevationProps, useElevation } from 'vuetify/lib/composables/elevation.js'
import { makeRoundedProps, useRounded } from 'vuetify/lib/composables/rounded.js'

export const makeSurfaceProps = propsFactory({
  ...makeBorderProps(),
  ...makeElevationProps(),
  ...makeRoundedProps(),
}, 'surface')

/**
 * Layout-root surface utilities (border / elevation / rounded).
 *
 * Wraps Vuetify composables. When `utility` is true (default), boolean `true`
 * maps to global utility classes (`border`, `rounded`) instead of BEM
 * (`${name}--border`, `${name}--rounded`).
 *
 * @param {object|import('vue').MaybeRefOrGetter<object>} props
 * @param {{ name?: string, utility?: boolean }} [options]
 */
export function useSurface (props, options = {}) {
  const name = options.name ?? 'ue'
  const utility = options.utility !== false

  const proxy = reactive({
    get border () { return toValue(props)?.border },
    get elevation () { return toValue(props)?.elevation },
    get hoverElevation () { return toValue(props)?.hoverElevation },
    get rounded () { return toValue(props)?.rounded },
    get tile () { return toValue(props)?.tile },
  })

  const { borderClasses } = useBorder(proxy, name)
  const { elevationClasses } = useElevation(proxy)
  const { roundedClasses, roundedStyles } = useRounded(proxy, name)

  const surfaceClasses = computed(() => {
    const p = toValue(props) ?? {}
    const classes = []

    if (utility && (p.border === true || p.border === '')) {
      classes.push('border')
    } else {
      classes.push(...[].concat(borderClasses.value ?? []))
    }

    classes.push(...[].concat(elevationClasses.value ?? []))

    if (utility && (p.rounded === true || p.rounded === '')) {
      classes.push('rounded')
    } else {
      classes.push(...[].concat(roundedClasses.value ?? []))
    }

    return classes.filter(Boolean)
  })

  return {
    surfaceClasses,
    surfaceStyles: roundedStyles,
    borderClasses,
    elevationClasses,
    roundedClasses,
  }
}

export default useSurface
