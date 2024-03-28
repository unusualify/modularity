// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'
import { ref, reactive, toRefs, h } from 'vue'
import { propsFactory } from 'vuetify/lib/util/propsFactory.mjs'

export const makeFormatterProps = propsFactory({
  ignoreFormatters: {
    type: [Array],
    default: []
  }
})
// by convention, composable function names start with "use"
export default function useFormatter (props, context, headers) {
  // state encapsulated and managed by the composable
  const { d } = useI18n({ useScope: 'global' })
  const formatterColumns = ref(headers.filter((h) =>
    Object.prototype.hasOwnProperty.call(h, 'formatter') &&
    h.formatter.length > 0 &&
    (!Object.prototype.hasOwnProperty.call(props, 'ignoreFormatters') || !props.ignoreFormatters.includes(h.formatter[0]))
  ))

  const methods = reactive({
    dateFormatter: function (value, datetimeFormat = 'long') {
      // __log(
      //   value
      //   // new Date(value),
      //   // d(new Date(value), datetimeFormat)
      // )
      return {
        configuration: methods.makeText(d(new Date(value), datetimeFormat))
      }
    },
    chipFormatter: function (value, color = '') {
      return {
        configuration: methods.makeChip(value, color)
      }
    },
    editFormatter: function (value) {
      return `<span @click="editItem">
        ${value}
      </span>`
    },
    pascalFormatter: function (value) {
      return _.startCase(_.camelCase(value)).replace(/ /g, '')
    },
    makeChip: function (value, color = '') {
      return {
        tag: 'v-chip',
        attributes: {
          color
        },
        elements: value
      }
    },
    makeText: function (value) {
      return {
        elements: value
      }
    }
  })

  function handleFormatter (formatter, value) {
    const name = formatter[0]
    // const pascalCase = methods.(name)
    const func = `${name}Formatter`

    return methods[func](value, ..._(formatter.slice(1)))
  }

  // expose managed state as return value
  return {
    formatterColumns,
    handleFormatter,
    ...toRefs(methods)
    // formatterDate
  }
}
