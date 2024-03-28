// hooks/useTable.js
import { ref, reactive, toRefs } from 'vue'
import filters from '@/utils/filters'

// by convention, composable function names start with "use"
export default function useFormatPermalink (props, context) {
  const permalink = ref(null)

  const state = reactive({

  })

  const methods = reactive({
    formatPermalink (newValue) {
      let text = ''
      if (newValue.value && typeof newValue.value === 'string') {
        text = newValue.value
      } else if (typeof newValue === 'string') {
        text = newValue
      }

      return filters.slugify(text)
    },
    formatPermalink_: function (newValue, ref = null) {
      // const permalinkRef = this.$refs.permalink
      __log('formatPermalink', newValue, permalink)
      return
      if (!permalink.value) return

      if (newValue) {
        let text = ''

        if (newValue.value && typeof newValue.value === 'string') {
          text = newValue.value
        } else if (typeof newValue === 'string') {
          text = newValue
        }

        const slug = filters.slugify(text)

        const field = {
          name: permalink.value.attributes ? permalink.value.attributes.name : permalink.value.name,
          value: slug
        }

        if (newValue.locale) {
          field.locale = newValue.locale
        } else {
          field.locale = this.currentLocale.value
        }

        // Update value in the store
        // this.$store.commit(FORM.UPDATE_FORM_FIELD, field)
      }
    }
  })

  return {
    ...toRefs(state),
    ...toRefs(methods)
  }
}
