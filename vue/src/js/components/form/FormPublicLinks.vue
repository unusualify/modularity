<template>
  <div
    v-if="hasAny"
    class="ue-form-public-links"
  >
    <div
      v-if="localizedPublicPermalinks && localizedPublicPermalinks.length"
      class="d-flex flex-wrap align-center ga-2 mt-2"
    >
      <span class="text-body-small text-medium-emphasis">{{ t('fields.public_permalink', 'Public page') }}</span>
      <a
        v-for="row in localizedPublicPermalinks"
        :key="row.locale"
        :href="row.url"
        target="_blank"
        rel="noopener noreferrer"
        class="d-inline-flex align-center ga-1 text-body-small text-primary text-decoration-none"
      >
        <span>{{ permalinkLocaleLabel(row.locale) }}</span>
        <v-icon size="small" color="primary">mdi-open-in-new</v-icon>
      </a>
    </div>
    <FormSignedPublicPreview
      :meta="signedPublicPreview"
      :is-editing="isEditing"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import FormSignedPublicPreview from './FormSignedPublicPreview.vue'

const props = defineProps({
  localizedPublicPermalinks: {
    type: Array,
    default: null,
  },
  languages: {
    type: Array,
    default: null,
  },
  signedPublicPreview: {
    type: Object,
    default: null,
  },
  isEditing: {
    type: Boolean,
    default: false,
  },
})

const { t } = useI18n({ useScope: 'global' })

const hasAny = computed(() => {
  const hasPermalinks = Array.isArray(props.localizedPublicPermalinks) && props.localizedPublicPermalinks.length > 0
  const hasSignedPreview = props.isEditing && Boolean(props.signedPublicPreview?.fetchUrl)

  return hasPermalinks || hasSignedPreview
})

function permalinkLocaleLabel(locale) {
  const langs = props.languages
  if (!Array.isArray(langs)) {
    return locale
  }
  const hit = langs.find((l) => l.value === locale)

  return hit?.shortlabel ?? hit?.label ?? locale
}
</script>
