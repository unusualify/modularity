<script setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import RevisionPreviewMainPanels from '@/components/others/RevisionPreviewMainPanels.vue'
  import RevisionPreviewSidebar from '@/components/others/RevisionPreviewSidebar.vue'

  const VIEWPORTS = [
    { key: 'desktop', icon: 'mdi-monitor', label: 'Desktop (1278px)', width: 1278, iconSize: 'default' },
    { key: 'laptop', icon: 'mdi-laptop', label: 'Laptop (1024px)', width: 1024, iconSize: 'default' },
    { key: 'tablet', icon: 'mdi-tablet', label: 'Tablet (768px)', width: 768, iconSize: 'small' },
    { key: 'mobile', icon: 'mdi-cellphone', label: 'Mobile (320px)', width: 320, iconSize: 'x-small' },
  ]

  const props = defineProps({
    modelValue: {
      type: Boolean,
      required: true,
    },
    previewTab: {
      type: String,
      required: true,
    },
    previewViewport: {
      type: String,
      required: true,
    },
    compareBaseRevisionId: {
      type: [String, Number],
      default: null,
    },
    /**
     * Snapshot from parent: preview HTML, diff/compare state, toolbar flags, etc.
     * (Tab / viewport / compare-base id stay separate v-models.)
     */
    bindings: {
      type: Object,
      required: true,
    },
  })

  const emit = defineEmits([
    'update:modelValue',
    'update:previewTab',
    'update:previewViewport',
    'update:compareBaseRevisionId',
    'after-leave',
    'close',
    'select-revision',
    'approve',
    'reject',
    'restore',
  ])

  const { t } = useI18n()

  const toolbarBusy = computed(() => {
    const b = props.bindings
    return (
      b.approving
      || b.rejecting
      || b.restoring
      || b.previewLoading
      || b.diffLoading
      || b.compareLoading
    )
  })

  function onDialogUpdate(v) {
    emit('update:modelValue', v)
    if (!v) {
      emit('close')
    }
  }

  function onBack() {
    emit('close')
  }
</script>

<template>
  <v-dialog
    :model-value="modelValue"
    fullscreen
    scrollable
    transition="dialog-bottom-transition"
    @update:model-value="onDialogUpdate"
    @after-leave="$emit('after-leave')"
  >
    <v-card class="d-flex flex-column revision-preview-dialog-card">
      <v-toolbar color="primary" density="compact">
        <v-btn icon color="white" @click="onBack">
          <v-icon color="white">mdi-arrow-left</v-icon>
        </v-btn>

        <v-btn-toggle
          :model-value="previewTab"
          mandatory
          divided
          density="compact"
          variant="text"
          class="revision-preview-mode-toggle ms-1"
          @update:model-value="$emit('update:previewTab', $event)"
        >
          <v-btn value="preview" size="small" class="text-white text-capitalize">
            {{ t('messages.revision.preview-tab') }}
          </v-btn>
          <v-btn value="diff" size="small" class="text-white text-capitalize">
            <v-icon start size="small" color="white">mdi-code-json</v-icon>
            {{ t('messages.revision.diff-tab') }}
          </v-btn>
          <v-btn value="compare" size="small" class="text-white text-capitalize">
            <v-icon start size="small" color="white">mdi-view-split-vertical</v-icon>
            {{ t('messages.revision.compare-tab') }}
          </v-btn>
        </v-btn-toggle>

        <v-spacer />

        <div v-if="previewTab === 'preview' || previewTab === 'compare'" class="d-flex align-center ga-1">
          <v-btn
            v-for="vp in VIEWPORTS"
            :key="vp.key"
            icon
            size="small"
            variant="text"
            :title="vp.label"
            :class="previewViewport === vp.key ? 'preview-viewport-btn--active' : 'preview-viewport-btn--inactive'"
            @click="$emit('update:previewViewport', vp.key)"
          >
            <v-icon color="white" :size="vp.iconSize">{{ vp.icon }}</v-icon>
          </v-btn>
        </div>
        <div v-else class="revision-preview-toolbar-spacer" />

        <v-spacer />

        <v-btn
          v-if="bindings.showApproveButtonInPreview"
          variant="elevated"
          color="success"
          size="small"
          class="me-1 text-none"
          :loading="bindings.approving"
          :disabled="toolbarBusy"
          @click="$emit('approve')"
        >
          {{ t('messages.revision.approve-action') }}
        </v-btn>

        <v-btn
          v-if="bindings.showRejectButtonInPreview"
          variant="elevated"
          color="error"
          size="small"
          class="me-1 text-none"
          :loading="bindings.rejecting"
          :disabled="toolbarBusy"
          @click="$emit('reject')"
        >
          {{ t('messages.revision.reject-action') }}
        </v-btn>

        <v-tooltip
          v-if="bindings.canRestore && bindings.showRestoreButtonInPreview && !bindings.canRestoreFromPreview"
          location="bottom"
        >
          <template #activator="{ props: tipProps }">
            <span v-bind="tipProps" class="d-inline-flex">
              <v-btn
                variant="elevated"
                color="grey"
                size="small"
                class="text-none"
                disabled
              >
                {{ t('Restore') }}
              </v-btn>
            </span>
          </template>
          <span class="text-body-small">{{ bindings.restorePreviewDisabledTooltip }}</span>
        </v-tooltip>
        <v-btn
          v-else-if="bindings.canRestore && bindings.showRestoreButtonInPreview && bindings.canRestoreFromPreview"
          variant="elevated"
          color="warning"
          size="small"
          class="text-none"
          :loading="bindings.restoring"
          :disabled="toolbarBusy"
          @click="$emit('restore')"
        >
          {{ t('Restore') }}
        </v-btn>
        <div v-else class="revision-preview-toolbar-spacer" />
      </v-toolbar>

      <div class="d-flex flex-grow-1 revision-preview-body">
        <div class="revision-preview-main flex-grow-1 d-flex flex-column min-width-0">
          <RevisionPreviewMainPanels
            :preview-tab="previewTab"
            :preview-html="bindings.previewHtml"
            :preview-loading="bindings.previewLoading"
            :preview-frame-style="bindings.previewFrameStyle"
            :compare-base-revision-id="compareBaseRevisionId"
            :compare-select-items="bindings.compareSelectItems"
            :compare-candidates="bindings.compareCandidates"
            :compare-html-left="bindings.compareHtmlLeft"
            :compare-html-right="bindings.compareHtmlRight"
            :compare-loading="bindings.compareLoading"
            :compare-left-caption="bindings.compareLeftCaption"
            :compare-right-caption="bindings.compareRightCaption"
            :compare-pane-frame-style="bindings.comparePaneFrameStyle"
            :diff-loading="bindings.diffLoading"
            :diff-line-parts="bindings.diffLineParts"
            :diff-part-class="bindings.diffPartClass"
            @update:compare-base-revision-id="$emit('update:compareBaseRevisionId', $event)"
          />
        </div>

        <v-divider vertical class="revision-preview-sidebar-divider flex-shrink-0" />

        <RevisionPreviewSidebar
          :revisions="bindings.sortedRevisions"
          :preview-revision-id="bindings.previewRevisionId"
          @select="$emit('select-revision', $event)"
        />
      </div>
    </v-card>
  </v-dialog>
</template>

<style lang="sass" scoped>
  .revision-preview-toolbar-spacer
    width: 88px
    min-width: 88px

  .preview-viewport-btn--active
    background: rgba(255, 255, 255, 0.2) !important
    border-radius: 6px

  .preview-viewport-btn--inactive
    opacity: 0.55
    &:hover
      opacity: 0.85

  .revision-preview-mode-toggle
    border: 1px solid rgba(255, 255, 255, 0.35)
    border-radius: 6px

  .revision-preview-dialog-card
    height: 100%
    max-height: 100dvh
    min-height: 0

  .revision-preview-body
    flex: 1 1 auto
    min-height: 0
    overflow: hidden

  .revision-preview-sidebar-divider
    align-self: stretch
    border-color: rgba(255, 255, 255, 0.12) !important

  .min-width-0
    min-width: 0
</style>
