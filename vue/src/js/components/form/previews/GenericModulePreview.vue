<template>
  <div class="generic-preview">
    <!-- Header -->
    <div class="d-flex align-center ga-2 mb-4">
      <v-icon color="primary" size="28">mdi-eye-check-outline</v-icon>
      <span class="text-title-large font-weight-bold">Preview</span>
    </div>

    <v-divider class="mb-6" />

    <!-- Grouped sections -->
    <template v-if="hasSections">
      <div v-for="section in sections" :key="section.name" class="mb-6">
        <div class="text-body-small text-uppercase text-medium-emphasis font-weight-bold mb-2 letter-spacing-wide">
          {{ section.name }}
        </div>
        <v-table density="comfortable" class="preview-table">
          <tbody>
            <tr v-for="field in section.fields" :key="field.key">
              <td class="label-col text-body-small text-medium-emphasis font-weight-medium border-0">
                {{ field.label }}
              </td>
              <td class="border-0">
                <FieldValue :field="field" :data="data" />
              </td>
            </tr>
          </tbody>
        </v-table>
      </div>
    </template>

    <!-- Flat list (no sections) -->
    <v-table v-else density="comfortable" class="preview-table">
      <tbody>
        <tr v-for="field in visibleFields" :key="field.key">
          <td class="label-col text-body-small text-medium-emphasis font-weight-medium border-0">
            {{ field.label }}
          </td>
          <td class="border-0">
            <FieldValue :field="field" :data="data" />
          </td>
        </tr>
      </tbody>
    </v-table>
  </div>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue'

const props = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
  /**
   * Array of field definition objects:
   * {
   *   key:     string               — key in data (supports dot-notation: 'meta.title')
   *   label:   string               — display label
   *   type?:   'text'|'html'|'badge'|'boolean'|'date'|'list'|'image'
   *                                   default: 'text'
   *   resolve?: (value, data) => any — custom value resolver
   *   section?: string              — group fields under a named section
   *   hide?:   (data) => boolean    — hide this field conditionally
   *   color?:  string               — badge color (type: 'badge' only)
   * }
   */
  fields: {
    type: Array,
    default: () => [],
  },
})

// ---------------------------------------------------------------------------
// Internal field-value renderer as a sub-component
// ---------------------------------------------------------------------------
const FieldValue = defineComponent({
  props: { field: Object, data: Object },
  setup(p) {
    const value = computed(() => resolveValue(p.field, p.data))

    return () => {
      const val = value.value
      const type = p.field.type || 'text'

      if (val === null || val === undefined || val === '') {
        return h('span', { class: 'text-medium-emphasis' }, '—')
      }

      if (type === 'html') {
        return h('div', {
          class: 'text-body-medium preview-html',
          innerHTML: val,
          style: 'max-height: 300px; overflow-y: auto;',
        })
      }

      if (type === 'badge') {
        return h('v-chip', {
          color: p.field.color || 'primary',
          size: 'small',
          variant: 'tonal',
        }, () => String(val))
      }

      if (type === 'boolean') {
        return h('v-icon', {
          color: val ? 'success' : 'error',
          size: 'small',
        }, () => val ? 'mdi-check-circle' : 'mdi-close-circle')
      }

      if (type === 'date') {
        const formatted = val
          ? new Intl.DateTimeFormat(undefined, {
              year: 'numeric', month: 'short', day: '2-digit',
              hour: '2-digit', minute: '2-digit', hour12: false,
            }).format(new Date(val))
          : null
        return h('span', { class: 'text-body-medium' }, formatted || '—')
      }

      if (type === 'list') {
        const items = Array.isArray(val) ? val : [val]
        return h('div', { class: 'd-flex flex-wrap ga-1' },
          items.map((item) =>
            h('v-chip', { size: 'small', variant: 'outlined' }, () => String(item))
          )
        )
      }

      if (type === 'image') {
        return h('v-img', {
          src: val,
          maxWidth: 160,
          maxHeight: 120,
          cover: true,
          rounded: 'md',
        })
      }

      // default: text
      return h('span', { class: 'text-body-medium' }, String(val))
    }
  },
})

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function getNestedValue(obj, key) {
  return key.split('.').reduce((acc, part) => acc?.[part], obj)
}

function resolveValue(field, data) {
  const raw = getNestedValue(data, field.key)

  if (field.resolve) {
    return field.resolve(raw, data)
  }

  // Auto-resolve common object shapes (e.g. a relation object)
  if (raw && typeof raw === 'object' && !Array.isArray(raw)) {
    return raw.name ?? raw.title ?? raw.text ?? raw.label ?? JSON.stringify(raw)
  }

  if (Array.isArray(raw)) {
    return raw.map((item) =>
      (item && typeof item === 'object')
        ? (item.name ?? item.title ?? item.text ?? JSON.stringify(item))
        : item
    )
  }

  return raw ?? null
}

// ---------------------------------------------------------------------------
// Computed layout
// ---------------------------------------------------------------------------
const visibleFields = computed(() =>
  props.fields.filter((f) => !f.hide || !f.hide(props.data))
)

const hasSections = computed(() =>
  visibleFields.value.some((f) => f.section)
)

const sections = computed(() => {
  const map = new Map()

  for (const field of visibleFields.value) {
    const sectionName = field.section || ''
    if (!map.has(sectionName)) map.set(sectionName, [])
    map.get(sectionName).push(field)
  }

  return [...map.entries()].map(([name, fields]) => ({ name, fields }))
})
</script>

<style scoped>
.generic-preview {
  max-width: 720px;
  margin: 0 auto;
}
.preview-table .label-col {
  width: 150px;
  vertical-align: top;
  padding-top: 10px;
}
.letter-spacing-wide {
  letter-spacing: 0.08em;
}
.preview-html :deep(p) {
  margin-bottom: 8px;
}
</style>
