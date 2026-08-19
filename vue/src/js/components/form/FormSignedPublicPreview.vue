<template>
  <div
    v-if="visible"
    class="d-flex flex-wrap align-center ga-2 mt-1"
  >
    <v-btn
      variant="tonal"
      size="small"
      color="secondary"
      :loading="loading"
      prepend-icon="mdi-link-variant"
      @click="onCopy"
    >
      {{ t('fields.signed_preview_copy_link') }}
    </v-btn>
    <span
      v-if="meta?.expiresInMinutes"
      class="text-body-small text-medium-emphasis"
    >
      {{
        t('fields.signed_preview_expires_note', {
          n: meta?.expiresInMinutes,
        })
      }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useSignedPublicPreview } from '@/hooks/useSignedPublicPreview'

const props = defineProps({
  meta: {
    type: Object,
    default: null,
  },
  isEditing: {
    type: Boolean,
    default: false,
  },
})

const { t } = useI18n({ useScope: 'global' })
const { loading, copyShareablePreviewLink } = useSignedPublicPreview()

const visible = computed(() => props.isEditing && Boolean(props.meta?.fetchUrl))

function onCopy() {
  copyShareablePreviewLink(props.meta)
}
</script>
