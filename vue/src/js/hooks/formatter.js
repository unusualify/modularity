// hooks/formatter .js

// import { ref, watch, computed, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import _ from 'lodash'
import { ref, reactive, toRefs } from 'vue'

// by convention, composable function names start with "use"
export function useFormatter (headers) {
  // state encapsulated and managed by the composable
  const { d } = useI18n({ useScope: 'global' })

  const formatterColumns = ref(headers.filter((h) =>
    h.hasOwnProperty('formatter') && h.formatter.length > 0
  ))

  const methods = reactive({
    dateFormatter: function (value, datetimeFormat = 'long') {
      return d(new Date(value), datetimeFormat)
    },
    pascalFormatter: function (value) {
      return _.startCase(_.camelCase(value)).replace(/ /g, '')
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
