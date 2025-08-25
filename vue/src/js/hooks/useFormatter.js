// hooks/formatter .js

import { ref, reactive, toRefs, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import _ from 'lodash-es'
import { propsFactory } from 'vuetify/lib/util/propsFactory.mjs'

export const makeFormatterProps = propsFactory({
  ignoreFormatters: {
    type: [Array],
    default: []
  }
})
// by convention, composable function names start with "use"
export default function useFormatter (props, context, headers = null) {
  // state encapsulated and managed by the composable
  const { d, te, t } = useI18n({ useScope: 'global' })

  const formatterColumns = computed(() => {
    return (headers?.value ?? []).filter((h) =>
      (Object.prototype.hasOwnProperty.call(h, 'formatter') &&
      h.formatter.length > 0 &&
      (!Object.prototype.hasOwnProperty.call(props, 'ignoreFormatters') || !props.ignoreFormatters.includes(h.formatter[0]))
      ) || (Object.prototype.hasOwnProperty.call(h, 'formatterName') && ['edit', 'activate'].includes(h.formatterName))

    ).map((h) => {
      let formatterName = Object.prototype.hasOwnProperty.call(h, 'formatterName') && ['edit', 'activate'].includes(h.formatterName) ? h.formatterName : h.formatter[0]
      return {
        ...h,
        formatterName: Object.prototype.hasOwnProperty.call(h, 'formatterName') && ['edit', 'activate'].includes(h.formatterName) ? h.formatterName : h.formatter[0],
        isFormatting: formatterName !== h.formatter[0]
      }
    })
  })

  const methods = reactive({
    dateFormatter: function (value, datetimeFormat = 'long') {

      return {
        configuration: methods.makeText(d(new Date(value), datetimeFormat))
      }
    },
    chipFormatter: function (value, attributes = {}) {
      return {
        configuration: methods.makeChip(value, attributes)
      }
    },
    badgeFormatter: function (value, attributes = {}) {
      return {
        configuration: methods.makeBadge(value, attributes)
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
      const trueValue = value === true || value === 'true' || value === 1 || value === '1'

      return {
        configuration : {
          tag: 'v-icon',
          attributes : {
            size: 'small',
            style: {
              color: trueValue ? 'green' : 'red'
            },
            icon: trueValue ? 'mdi-check' : 'mdi-close'
          },
        }

      }
    },
    shortenFormatter: function (value, max = 10) {
      return {
        configuration: {
          elements: window.__shorten(value, max)
        }
      }
    },

    makeChip: function (value, attributes = {}) {
      return {
        tag: 'v-chip',
        attributes: {
          ...attributes,
        },
        elements: value
      }
    },
    makeBadge: function (value, attributes = {}) {
      return {
        tag: 'v-badge',
        attributes: {
          ...attributes,
          content: value
        },
      }
    },
    makeText: function (value) {
      return {
        elements: value
      }
    },

    castMatch: function (value, ownerItem) {
      let castPattern = /\$([\w\d\.\*\_]+)/
      let matches

      let returnValue = value

      if(window.__isString(value) && (matches = value.match(castPattern))){
        let notation = matches[1]
        let quoted = window.__preg_quote(matches[0])
        let parts = notation.split('.')

        let newParts = []
        for(const j in parts){
          let part = parts[j]
          if(part === '*'){
            // let searchedValue =
            let _id = ownerItem.id
            // parts[j] = `*id=${_id}`
          }else{
            newParts.push(part)
          }
        }

        notation = newParts.join('.')

        let newValue = window.__data_get(ownerItem, notation)

        if(newValue !== undefined && newValue !== null){
          let _value

          if(Array.isArray(newValue) && newValue.length > 0){
            _value = newValue.join(',')
          }else if(window.__isString(newValue)){
            _value = newValue

            let snakeCased = _.snakeCase(_value)

            if(te(`modules.${snakeCased}`)){
              _value = t(`modules.${snakeCased}`)
            }
          }else if(window.__isNumber(newValue)){
            _value = newValue.toString()
          }

          if(_value !== undefined){
            let remainingQuote = '\\w\\s' + window.__preg_quote('çşıİğüö.,;?|:_')
            let pattern = new RegExp( String.raw`^([${remainingQuote}]+)?(${quoted})([${remainingQuote}]+)?$`)

            if(value.match(pattern)){
              returnValue = value.replace(pattern, '$1' + _value + '$3')
            }else{
              __log(
                'Not matched sentence',
                pattern,
                value,
                value.match(pattern)
              )
            }
          }
        }
      }

      return returnValue
    }

  })

  function handleFormatter (formatter, value) {
    let args = _.cloneDeep(formatter)
    const name = args.shift()
    // const pascalCase = methods.(name)
    const func = `${name}Formatter`

    try {
      if(value === null || value === undefined || value === '') {
        return {
          configuration: {
            elements: ''
          }
        }
      }
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
