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
      return {
        configuration : {
          elements: `${value}`,
          tag: 'span',
          attributes : {
            onClick: 'editItem'
          }

        }
      }
      return `<span @click="editItem">
        ${value}
      </span>`
    },
    pascalFormatter: function (value) {
      return _.startCase(_.camelCase(value)).replace(/ /g, '')
    },
    priceFormatter:(value, unit = '₺', taxContent= null) => {
      return {
        configuration:{
          elements : [{
            tag:'p',
            attributes:{
              class: 'featured',
            },
            elements: `${unit}${value}`
          },
          {
            tag:'p',
            attributes:{
              class: 'value'
            },
            elements: `${taxContent ? `+${taxContent}` : ''}`
          },
        ]
        }
      }
    },
    statusFormatter:(value, placeHolders = null, colors = null) => {
      return {
        configuration : {
          tag: 'p',
          attributes : {
            style: {
              color: colors?.[value] ?? 'red'
            },
          },
          elements: placeHolders?.[value] ?? value
        }

      }
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
    },
    shortenFormatter: function (value) {
      console.log(value);
      return window.__shorten(value, 10)
    }
  })

  function handleFormatter (formatter, value) {
    let args = _.cloneDeep(formatter)
    const name = args.shift()
    // const pascalCase = methods.(name)
    const func = `${name}Formatter`
    try {
      return methods[func](value, ..._(args))
    } catch (error) {
      console.error(`${error}: ${func}`);
    }

  }

  // expose managed state as return value
  return {
    formatterColumns,
    handleFormatter,
    ...toRefs(methods)
    // formatterDate
  }
}
