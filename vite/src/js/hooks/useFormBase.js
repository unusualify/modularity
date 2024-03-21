// hooks/useFormbase.js

import { get, isPlainObject, isFunction, isString, isNumber, isEmpty, orderBy, delay, find, findIndex } from 'lodash-es'
import { ref, reactive, toRefs, computed, shallowReactive } from 'vue'

// by convention, composable function names start with "use"
export default function useFormBase (props, context) {
  const rowDefault = { noGutters: true } // { noGutters:true, justify:'center', align:'center' }
  const orderDirection = 'ASC'

  const states = reactive({
    flatCombinedArray: [],
    clear: 'clear',
    button: 'button',
    treeview: 'treeview',
    list: 'list',

    focus: 'focus',
    blur: 'blur',

    append: 'append',
    appendInner: 'append-inner',
    prepend: 'prepend',
    prependInner: 'prepend-inner',

    hour: 'hour',
    minute: 'minute',
    second: 'second',
    formSchema: props.schema.value,

    valueIntern: computed(() => {
      const model = props.model.value || props.modelValue.value
      this.updateArrayFromState(model, states.formSchema)
      return model
    }),

    flatCombinedArraySorted: computed(() => {
      // __log('flatCombinedArraySorted computed', this.valueIntern, this.formSchema)
      return orderBy(props.flatCombinedArray, ['schema.sort'], [orderDirection])
    }),
    storeStateData: computed(() => {
      // __log('storeStateData computed', this.$lodash.pick(this.valueIntern, ['country_id', 'city_id', 'district_id']))
      this.updateArrayFromState(states.valueIntern, states.formSchema)
      return this.valueIntern
    }),
    storeStateSchema: computed(() => {
      // __log('storeStateSchema computed', this.valueIntern, this.storeStateData)

      this.updateArrayFromState(states.valueIntern, states.formSchema)
      for (const key in states.formSchema) {
        const sch = states.formSchema[key]
        if (sch.type === 'select' && Object.prototype.hasOwnProperty.call(sch, 'cascade')) {
          states.formSchema[sch.cascade].items = find(sch.items, [sch.itemValue, states.valueIntern[sch.name]]).items ?? []
          // this.formSchema[key].items = find(this.formSchema[sch.parent].items, [this.formSchema[sch.parent].itemValue, this.valueIntern[sch.parent]]).items
        }
      }
      return this.formSchema
    })
  })

  const getRow = computed(() => {
    return props.row.value || rowDefault
  })

  __log(states)

  // expose managed state as return value
  return {
    ...toRefs(states)

    // getRow

  }
}
