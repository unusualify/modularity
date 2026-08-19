<template>
  <v-row class="ue-form-preview" dense>
    <v-col
      v-for="input in fields"
      :key="input.name"
      v-bind="colAttrs(input)"
      class="ue-form-preview__col py-3"
    >
      <div class="ue-form-preview__label text-body-small text-medium-emphasis mb-1">
        {{ input.label }}
      </div>
      <div class="ue-form-preview__value text-body-large font-weight-medium">
        <v-icon
          v-if="display(input).kind === 'boolean' && display(input).value"
          color="primary"
          size="20"
        >
          mdi-check
        </v-icon>
        <span v-else-if="display(input).kind === 'boolean'" class="text-medium-emphasis">—</span>
        <span v-else-if="display(input).kind === 'empty'" class="text-medium-emphasis">—</span>
        <span v-else>{{ display(input).value }}</span>
      </div>
    </v-col>
  </v-row>
</template>

<script>
import { computed } from 'vue'
import { getFormPreviewFields, getFormPreviewValue } from '@/utils/formPreview'

export default {
  name: 'ue-form-preview-fields',
  props: {
    schema: {
      type: Object,
      default: () => ({}),
    },
    model: {
      type: Object,
      default: () => ({}),
    },
  },
  setup (props) {
    const fields = computed(() => getFormPreviewFields(props.schema))

    const colAttrs = (input) => {
      const col = input.col
      if (!col) {
        return { cols: 12 }
      }
      if (typeof col === 'object') {
        const { class: _class, ...attrs } = col
        return Object.keys(attrs).length ? attrs : { cols: 12 }
      }
      return { cols: col }
    }

    const display = (input) => getFormPreviewValue(input, props.model)

    return {
      fields,
      colAttrs,
      display,
    }
  },
}
</script>
