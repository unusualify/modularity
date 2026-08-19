<script setup>
  import { useI18n } from 'vue-i18n'

  defineProps({
    previewTab: {
      type: String,
      required: true,
    },
    previewHtml: {
      type: String,
      default: '',
    },
    previewLoading: {
      type: Boolean,
      default: false,
    },
    previewFrameStyle: {
      type: Object,
      required: true,
    },
    compareBaseRevisionId: {
      type: [String, Number],
      default: null,
    },
    compareSelectItems: {
      type: Array,
      default: () => [],
    },
    compareCandidates: {
      type: Array,
      default: () => [],
    },
    compareHtmlLeft: {
      type: String,
      default: '',
    },
    compareHtmlRight: {
      type: String,
      default: '',
    },
    compareLoading: {
      type: Boolean,
      default: false,
    },
    compareLeftCaption: {
      type: String,
      default: '',
    },
    compareRightCaption: {
      type: String,
      default: '',
    },
    comparePaneFrameStyle: {
      type: Object,
      required: true,
    },
    diffLoading: {
      type: Boolean,
      default: false,
    },
    diffLineParts: {
      type: Array,
      default: () => [],
    },
    diffPartClass: {
      type: Function,
      required: true,
    },
  })

  const emit = defineEmits(['update:compareBaseRevisionId'])

  const { t } = useI18n()

  function setCompareBase(id) {
    emit('update:compareBaseRevisionId', id)
  }
</script>

<template>
  <v-card-text class="flex-grow-1 pa-0 overflow-y-auto" style="background: #3a3a3a;">
    <template v-if="previewTab === 'preview'">
      <v-progress-linear v-if="previewLoading" indeterminate color="primary" />
      <div v-else :style="previewFrameStyle">
        <iframe
          v-if="previewHtml"
          class="revision-preview-iframe"
          sandbox="allow-same-origin allow-scripts allow-forms allow-popups"
          :title="t('Revision preview')"
          :srcdoc="previewHtml"
        />
      </div>
    </template>

    <div v-else-if="previewTab === 'compare'" class="revision-compare-panel text-white">
      <v-alert
        v-if="!compareCandidates.length"
        type="info"
        variant="tonal"
        class="ma-4"
        density="compact"
      >
        {{ t('messages.revision.diff-no-older') }}
      </v-alert>
      <template v-else>
        <div class="px-4 pt-4 pb-2">
          <v-sheet rounded="lg" class="pa-2 bg-surface">
            <v-select
              :model-value="compareBaseRevisionId"
              :items="compareSelectItems"
              item-title="title"
              item-value="value"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('messages.revision.diff-compare-with')"
              color="primary"
              @update:model-value="setCompareBase"
            />
          </v-sheet>
          <p class="text-body-small text-medium-emphasis mt-2 mb-0">
            {{ t('messages.revision.compare-hint') }}
          </p>
        </div>
        <v-progress-linear v-if="compareLoading" indeterminate color="primary" />
        <v-row v-else class="ma-0 revision-compare-row" density="compact">
          <v-col cols="12" md="6" class="pa-2">
            <div class="text-body-small text-medium-emphasis mb-1">
              {{ t('messages.revision.compare-older') }}
            </div>
            <div class="text-body-medium mb-2 text-white">{{ compareLeftCaption }}</div>
            <div :style="comparePaneFrameStyle">
              <iframe
                v-if="compareHtmlLeft"
                class="revision-preview-iframe revision-compare-iframe"
                sandbox="allow-same-origin allow-scripts allow-forms allow-popups"
                :title="t('messages.revision.compare-older')"
                :srcdoc="compareHtmlLeft"
              />
            </div>
          </v-col>
          <v-col cols="12" md="6" class="pa-2">
            <div class="text-body-small text-medium-emphasis mb-1">
              {{ t('messages.revision.compare-newer') }}
            </div>
            <div class="text-body-medium mb-2 text-white">{{ compareRightCaption }}</div>
            <div :style="comparePaneFrameStyle">
              <iframe
                v-if="compareHtmlRight"
                class="revision-preview-iframe revision-compare-iframe"
                sandbox="allow-same-origin allow-scripts allow-forms allow-popups"
                :title="t('messages.revision.compare-newer')"
                :srcdoc="compareHtmlRight"
              />
            </div>
          </v-col>
        </v-row>
      </template>
    </div>

    <div v-else-if="previewTab === 'diff'" class="revision-diff-panel text-white">
      <v-alert
        v-if="!compareCandidates.length"
        type="info"
        variant="tonal"
        class="ma-4"
        density="compact"
      >
        {{ t('messages.revision.diff-no-older') }}
      </v-alert>
      <template v-else>
        <div class="px-4 pt-4 pb-2">
          <v-sheet rounded="lg" class="pa-2 bg-surface">
            <v-select
              :model-value="compareBaseRevisionId"
              :items="compareSelectItems"
              item-title="title"
              item-value="value"
              density="compact"
              variant="outlined"
              hide-details
              :label="t('messages.revision.diff-compare-with')"
              color="primary"
              @update:model-value="setCompareBase"
            />
          </v-sheet>
          <p class="text-body-small text-medium-emphasis mt-2 mb-0">
            {{ t('messages.revision.diff-from-to') }}
          </p>
        </div>
        <v-progress-linear v-if="diffLoading" indeterminate color="primary" />
        <pre
          v-else
          class="revision-diff-pre font-monospace text-body-medium pa-4 pt-2"
        ><template v-for="(part, di) in diffLineParts" :key="di"><span :class="diffPartClass(part)">{{ part.value }}</span></template></pre>
      </template>
    </div>
  </v-card-text>
</template>

<style lang="sass" scoped>
  .revision-preview-iframe
    display: block
    width: 100%
    min-height: calc(100vh - 120px)
    border: 0
    background: #fff

  .revision-diff-panel
    min-height: calc(100vh - 48px)

  .revision-diff-pre
    margin: 0
    white-space: pre-wrap
    word-break: break-word
    overflow-x: auto
    max-height: calc(100vh - 200px)

  .revision-diff-part--added
    background-color: rgba(76, 175, 80, 0.35)

  .revision-diff-part--removed
    background-color: rgba(244, 67, 54, 0.35)
    text-decoration: line-through
    text-decoration-color: rgba(255, 255, 255, 0.7)

  .revision-compare-panel
    min-height: calc(100vh - 48px)

  .revision-compare-row
    max-width: 100%

  .revision-compare-iframe
    min-height: calc(100vh - 220px)
</style>
