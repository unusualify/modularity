<template>
  <template v-if="visible">
    <v-chip-group
      v-if="!$vuetify.display.smAndDown"
      :model-value="currentLocale?.value"
      @update:model-value="updateLocale"
      selected-class="bg-primary"
      mandatory
      class="mt-n2 pt-2"
    >
      <v-chip
        v-for="language in languages"
        :key="language.value"
        :text="language.shortlabel"
        :value="language.value"
        variant="outlined"
      />
    </v-chip-group>

    <v-select
      v-else
      :model-value="currentLocale?.value"
      :items="languages"
      item-value="value"
      item-title="shortlabel"
      density="compact"
      hide-details
      variant="outlined"
      class="ue-form-locale-selector mt-n2 pt-2"
      @update:model-value="updateLocale"
    />
  </template>
</template>

<script setup>
import { computed } from 'vue'
import { useLocale } from '@/hooks'

const props = defineProps({
  hasTranslationInputs: {
    type: Boolean,
    default: false,
  },
})

const { currentLocale, languages, updateLocale } = useLocale()

const visible = computed(() =>
  props.hasTranslationInputs
  && Array.isArray(languages.value)
  && languages.value.length > 1
)

defineOptions({
  name: 'FormLocaleSelector',
})
</script>

<style lang="sass" scoped>
.ue-form-locale-selector
  min-width: 5.5rem
  max-width: 7rem
</style>
