<script setup>
  import { ref, computed, watch, inject } from 'vue'
  import { useStore } from 'vuex'
  import { useI18n } from 'vue-i18n'
  import {
    useInput,
    makeInputProps,
    makeInputEmits,
    useAlert,
  } from '@/hooks'
  import { useRevisionDiff, useRevisionVisualCompare, revisionIdEq, formatRevisionDate } from '@/hooks/revision'
  import axios from 'axios'
  import { getActiveContentLocale } from '@/utils/locale'
  import api from '@/store/api/form'
  import RevisionItem from '@/components/others/RevisionItem.vue'
  import RevisionPreviewDialog from '@/components/others/RevisionPreviewDialog.vue'
  import RevisionConfirmDialog from '@/components/others/RevisionConfirmDialog.vue'

  const VIEWPORTS = [
    { key: 'desktop', icon: 'mdi-monitor', label: 'Desktop (1278px)', width: 1278, iconSize: 'default' },
    { key: 'laptop', icon: 'mdi-laptop', label: 'Laptop (1024px)', width: 1024, iconSize: 'default' },
    { key: 'tablet', icon: 'mdi-tablet', label: 'Tablet (768px)', width: 768, iconSize: 'small' },
    { key: 'mobile', icon: 'mdi-cellphone', label: 'Mobile (320px)', width: 320, iconSize: 'x-small' },
  ]

  const props = defineProps({
    ...makeInputProps(),
    variant: {
      type: String,
      default: 'outlined',
    },
    density: {
      type: String,
      default: 'default',
    },
    items: {
      type: Array,
      default: () => [],
    },
    fetchEndpoint: {
      type: String,
      required: true,
    },
    showEndpoint: {
      type: String,
      required: true,
    },
    restoreEndpoint: {
      type: String,
      required: true,
    },
    approveEndpoint: {
      type: String,
      default: '',
    },
    rejectEndpoint: {
      type: String,
      default: '',
    },
    /** Fallback when not set via schema (RevisionHydrate passes maxHeight). */
    maxHeight: {
      type: [String, Number],
      default: null,
    },
  })

  const emit = defineEmits([...makeInputEmits])

  const { input } = useInput(props, { emit })
  const { t } = useI18n()
  const store = useStore()
  /** Laravel `App::setLocale` — same as Form.vue / `language` store `active.value`. */
  const previewActiveLanguage = computed(() => getActiveContentLocale(store) ?? '')
  const Alert = useAlert()
  const applyRevisionRestoreResponse = inject('applyRevisionRestoreResponse', null)
  const applyRevisionApproveResponse = inject('applyRevisionApproveResponse', null)
  const applyRevisionRejectResponse = inject('applyRevisionRejectResponse', null)

  const revisions = ref([...props.items])
  const loading = ref(false)
  const expanded = ref(true)
  const previewDialogActive = ref(false)
  const previewLoading = ref(false)
  const previewHtml = ref('')
  const previewRevisionId = ref(null)
  const restoring = ref(false)
  const approving = ref(false)
  const rejecting = ref(false)
  /** Second-step confirmation before approve / reject / restore API calls. */
  const revisionConfirmOpen = ref(false)
  /** @type {import('vue').Ref<'approve' | 'reject' | 'restore' | null>} */
  const revisionConfirmKind = ref(null)
  const previewViewport = ref('desktop')
  /** 'preview' | 'diff' | 'compare' */
  const previewTab = ref('preview')

  const recordId = computed(() => (input.value != null && input.value !== '' ? String(input.value) : ''))

  /** From {@see RevisionHydrate} (schema). */
  const canApprove = computed(() => props.obj?.schema?.canApprove === true)

  /** From {@see RevisionHydrate} (schema). */
  const canRestore = computed(() => props.obj?.schema?.canRestore === true)

  /** From {@see RevisionHydrate} (schema). */
  const canReject = computed(() => props.obj?.schema?.canReject === true)

  const approveEndpointResolved = computed(
    () => props.approveEndpoint || props.obj?.schema?.approveEndpoint || ''
  )

  const rejectEndpointResolved = computed(
    () => props.rejectEndpoint || props.obj?.schema?.rejectEndpoint || ''
  )

  /** Height + smooth scroll for the revision list (from RevisionHydrate maxHeight). */
  const revisionListScrollStyle = computed(() => {
    const raw =
      props.obj?.schema?.maxHeight
      ?? props.maxHeight
      ?? '320px'
    const maxHeight = typeof raw === 'number' ? `${raw}px` : String(raw)

    return {
      maxHeight,
      overflowX: 'hidden',
      overflowY: 'auto',
      scrollBehavior: 'smooth',
      overscrollBehavior: 'contain',
      WebkitOverflowScrolling: 'touch',
    }
  })

  const sortedRevisions = computed(() =>
    [...revisions.value].sort((a, b) => new Date(b.datetime) - new Date(a.datetime))
  )

  /** When the latest revision is pending, older snapshots must not be restorable until resolved. */
  const hasPendingHeadRevision = computed(() => {
    const head = sortedRevisions.value[0]
    if (!head) return false
    return String(head.status ?? 'approved').toLowerCase() === 'pending'
  })

  const {
    compareBaseRevisionId,
    diffLoading,
    diffLineParts,
    compareCandidates,
    compareSelectItems,
    effectiveCompareBaseId,
    resetDiffState,
    diffPartClass,
  } = useRevisionDiff({
    recordId,
    targetRevisionId: previewRevisionId,
    restoreEndpoint: computed(() => props.restoreEndpoint),
    sortedRevisions,
    previewDialogActive,
    previewTab,
    onDiffError: () => {
      Alert.openAlert({
        message: t('messages.revision.diff-load-error'),
        variant: 'error',
        location: 'top',
      })
    },
  })

  const {
    compareHtmlLeft,
    compareHtmlRight,
    compareLoading,
    resetCompareVisuals,
  } = useRevisionVisualCompare({
    recordId,
    targetRevisionId: previewRevisionId,
    showEndpoint: computed(() => props.showEndpoint),
    compareBaseRevisionId,
    compareCandidates,
    effectiveCompareBaseId,
    previewDialogActive,
    previewTab,
    activeLanguage: previewActiveLanguage,
    onError: () => {
      Alert.openAlert({
        message: t('messages.revision.compare-load-error'),
        variant: 'error',
        location: 'top',
      })
    },
  })

  const lastEditedText = computed(() => {
    if (!sortedRevisions.value.length) return ''
    return timeAgo(new Date(sortedRevisions.value[0].datetime))
  })

  const previewFrameStyle = computed(() => {
    const vp = VIEWPORTS.find((v) => v.key === previewViewport.value) ?? VIEWPORTS[0]
    return {
      maxWidth: `${vp.width}px`,
      width: '100%',
      margin: '32px auto',
      borderRadius: '8px',
      backgroundColor: '#ffffff',
      boxShadow: '0 8px 40px rgba(0,0,0,0.5)',
      padding: '0',
      transition: 'max-width 0.3s ease',
      minHeight: 'calc(100vh - 112px)',
    }
  })

  /** Revision row currently open in the preview dialog. */
  const previewedRevision = computed(() =>
    sortedRevisions.value.find((r) => revisionIdEq(r.id, previewRevisionId.value)) ?? null
  )

  const compareLeftCaption = computed(() => {
    const id = effectiveCompareBaseId.value
    const r = sortedRevisions.value.find((x) => revisionIdEq(x.id, id))
    return r ? `${r.label} · ${formatRevisionDate(r.datetime)}` : '—'
  })

  const compareRightCaption = computed(() => {
    const r = previewedRevision.value
    return r ? `${r.label} · ${formatRevisionDate(r.datetime)}` : '—'
  })

  const comparePaneFrameStyle = computed(() => {
    const vp = VIEWPORTS.find((v) => v.key === previewViewport.value) ?? VIEWPORTS[0]
    return {
      maxWidth: `${vp.width}px`,
      width: '100%',
      margin: '0 auto',
      borderRadius: '8px',
      backgroundColor: '#ffffff',
      boxShadow: '0 4px 24px rgba(0,0,0,0.45)',
      padding: '0',
      transition: 'max-width 0.3s ease',
      minHeight: 'calc(100vh - 200px)',
    }
  })

  /** Show Restore (enabled or disabled) only when previewing an older snapshot (not the latest). */
  const showRestoreButtonInPreview = computed(() => {
    if (previewRevisionId.value == null || previewRevisionId.value === '' || !sortedRevisions.value.length) {
      return false
    }
    const headId = sortedRevisions.value[0]?.id
    return !revisionIdEq(headId, previewRevisionId.value)
  })

  /** Latest revision cannot be “restored”; a revision created by restore (is_restored) cannot be restored again. */
  const canRestoreFromPreview = computed(() => {
    if (!showRestoreButtonInPreview.value) return false
    if (revisionStatusNorm(previewedRevision.value) === 'rejected') return false
    if (hasPendingHeadRevision.value) return false
    if (previewedRevision.value?.is_restored) return false
    return true
  })

  const restorePreviewDisabledTooltip = computed(() => {
    if (!showRestoreButtonInPreview.value || !canRestore.value) return ''
    if (revisionStatusNorm(previewedRevision.value) === 'rejected') {
      return t('messages.revision.restore-blocked-rejected')
    }
    if (hasPendingHeadRevision.value) return t('messages.revision.restore-blocked-pending')
    if (previewedRevision.value?.is_restored) return t('messages.revision.restore-disabled-already-restored')
    return ''
  })

  /** Approve applies to the latest pending snapshot (workflow). */
  const showApproveButtonInPreview = computed(() => {
    if (!canApprove.value || !previewedRevision.value) return false
    return previewedRevision.value.status === 'pending'
  })

  /** Reject applies to the latest pending snapshot (workflow); live row is not updated. */
  const showRejectButtonInPreview = computed(() => {
    if (!canReject.value || !previewedRevision.value) return false
    return previewedRevision.value.status === 'pending'
  })

  /**
   * Single snapshot for {@link RevisionPreviewDialog} (avoids long prop lists).
   * Tab / viewport / compare-base id stay as v-models for two-way sync with hooks.
   */
  const revisionPreviewBindings = computed(() => ({
    previewHtml: previewHtml.value,
    previewLoading: previewLoading.value,
    previewFrameStyle: previewFrameStyle.value,
    comparePaneFrameStyle: comparePaneFrameStyle.value,
    sortedRevisions: sortedRevisions.value,
    previewRevisionId: previewRevisionId.value,
    compareSelectItems: compareSelectItems.value,
    compareCandidates: compareCandidates.value,
    compareHtmlLeft: compareHtmlLeft.value,
    compareHtmlRight: compareHtmlRight.value,
    compareLoading: compareLoading.value,
    compareLeftCaption: compareLeftCaption.value,
    compareRightCaption: compareRightCaption.value,
    diffLoading: diffLoading.value,
    diffLineParts: diffLineParts.value,
    diffPartClass,
    canRestore: canRestore.value,
    showRestoreButtonInPreview: showRestoreButtonInPreview.value,
    canRestoreFromPreview: canRestoreFromPreview.value,
    restorePreviewDisabledTooltip: restorePreviewDisabledTooltip.value,
    showApproveButtonInPreview: showApproveButtonInPreview.value,
    showRejectButtonInPreview: showRejectButtonInPreview.value,
    approving: approving.value,
    rejecting: rejecting.value,
    restoring: restoring.value,
  }))

  function revisionStatusNorm(rev) {
    return String(rev?.status ?? 'approved').toLowerCase()
  }

  function timeAgo(date) {
    const now = new Date()
    const seconds = Math.floor((now - date) / 1000)
    const intervals = [
      { label: 'year', seconds: 31536000 },
      { label: 'month', seconds: 2592000 },
      { label: 'week', seconds: 604800 },
      { label: 'day', seconds: 86400 },
      { label: 'hour', seconds: 3600 },
      { label: 'minute', seconds: 60 },
    ]
    for (const interval of intervals) {
      const count = Math.floor(seconds / interval.seconds)
      if (count >= 1) {
        return `Edited ${count} ${interval.label}${count > 1 ? 's' : ''} ago`
      }
    }
    return 'Edited just now'
  }

  /**
   * @param {string|number|null|undefined} explicitId - When the form hydrates async, pass modelValue so fetch runs before recordId computed catches up in the same tick.
   */
  const fetchRevisions = async (explicitId) => {
    const id =
      explicitId !== undefined && explicitId !== null && explicitId !== ''
        ? String(explicitId)
        : recordId.value
    if (!id) {
      revisions.value = []
      return
    }
    const endpoint = props.fetchEndpoint.replace(':id', id)
    loading.value = true
    try {
      const { data } = await axios.get(endpoint)
      revisions.value = Array.isArray(data) ? data : []
    } catch (e) {
      revisions.value = []
      Alert.openAlert({
        message: t('Could not load revisions.'),
        variant: 'error',
        location: 'top',
      })
    } finally {
      loading.value = false
    }
  }

  /**
   * Loads HTML for the Preview tab only (showView PUT with revisionId).
   */
  async function loadPreviewHtmlForRevision(revisionId) {
    if (!recordId.value || revisionId == null || revisionId === '') return
    previewLoading.value = true
    previewHtml.value = ''
    const url = props.showEndpoint.replace(':id', recordId.value)
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

    try {
      const params = { revisionId }
      if (previewActiveLanguage.value) {
        params.activeLanguage = previewActiveLanguage.value
      }

      const { data } = await axios.put(
        url,
        { _token: token },
        {
          params,
          responseType: 'text',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'text/html',
          },
        }
      )
      previewHtml.value = typeof data === 'string' ? data : ''
    } catch (e) {
      previewHtml.value = ''
      Alert.openAlert({
        message: t('Could not load preview.'),
        variant: 'error',
        location: 'top',
      })
    } finally {
      previewLoading.value = false
    }
  }

  watch(previewActiveLanguage, () => {
    if (!previewDialogActive.value || previewTab.value !== 'preview') return
    const rid = previewRevisionId.value
    if (rid == null || rid === '') return
    loadPreviewHtmlForRevision(rid)
  })

  /**
   * Switch target revision from the modal sidebar (diff/compare hooks react via watch).
   */
  async function switchPreviewRevision(revisionId) {
    if (!recordId.value || revisionId == null || revisionIdEq(revisionId, previewRevisionId.value)) return
    previewRevisionId.value = revisionId
    if (previewTab.value === 'preview') {
      await loadPreviewHtmlForRevision(revisionId)
    }
  }

  /**
   * HTML preview for a revision (showView with revisionId). Route is registered as PUT.
   */
  const openRevisionPreview = async (revisionId) => {
    if (!recordId.value) return
    previewRevisionId.value = revisionId == null ? null : revisionId
    previewDialogActive.value = true
    previewViewport.value = 'desktop'
    previewTab.value = 'preview'
    resetDiffState()
    resetCompareVisuals()
    await loadPreviewHtmlForRevision(revisionId)
  }

  const closePreviewDialog = () => {
    previewDialogActive.value = false
    previewHtml.value = ''
    previewRevisionId.value = null
    previewTab.value = 'preview'
    resetDiffState()
    resetCompareVisuals()
  }

  function onPreviewDialogAfterLeave() {
    previewHtml.value = ''
    resetDiffState()
    resetCompareVisuals()
  }

  const revisionConfirmTitle = computed(() => {
    if (revisionConfirmKind.value === 'approve') return t('messages.revision.approve-confirm-title')
    if (revisionConfirmKind.value === 'reject') return t('messages.revision.reject-confirm-title')
    if (revisionConfirmKind.value === 'restore') return t('messages.revision.restore-confirm-title')
    return ''
  })

  const revisionConfirmBody = computed(() => {
    if (revisionConfirmKind.value === 'approve') return t('messages.revision.approve-confirm-body')
    if (revisionConfirmKind.value === 'reject') return t('messages.revision.reject-confirm-body')
    if (revisionConfirmKind.value === 'restore') return t('messages.revision.restore-confirm-body')
    return ''
  })

  const revisionConfirmSubmitColor = computed(() => {
    if (revisionConfirmKind.value === 'restore') return 'warning'
    if (revisionConfirmKind.value === 'reject') return 'error'
    return 'primary'
  })

  function openRevisionConfirm(kind) {
    revisionConfirmKind.value = kind
    revisionConfirmOpen.value = true
  }

  function closeRevisionConfirm() {
    revisionConfirmOpen.value = false
    revisionConfirmKind.value = null
  }

  function submitRevisionConfirm() {
    const kind = revisionConfirmKind.value
    closeRevisionConfirm()
    if (kind === 'approve') {
      executeApproveFromPreview()
    } else if (kind === 'reject') {
      executeRejectFromPreview()
    } else if (kind === 'restore') {
      executeRestoreFromPreview()
    }
  }

  const executeApproveFromPreview = () => {
    if (!recordId.value || !previewRevisionId.value || !showApproveButtonInPreview.value) return
    const base = approveEndpointResolved.value
    if (!base) return

    const url = base.replace(':id', recordId.value)
    const revisionId = previewRevisionId.value
    approving.value = true

    api.put(
      `${url}?revisionId=${revisionId}`,
      {},
      (response) => {
        approving.value = false
        closePreviewDialog()
        if (typeof applyRevisionApproveResponse === 'function') {
          applyRevisionApproveResponse(response)
        } else if (response.data?.form_fields) {
          window.location.reload()
          return
        }
        if (response.data?.revisions) {
          revisions.value = response.data.revisions
        } else {
          fetchRevisions()
        }
      },
      () => {
        approving.value = false
        Alert.openAlert({
          message: t('messages.revision.approve-failed'),
          variant: 'error',
          location: 'top',
        })
      }
    )
  }

  const executeRejectFromPreview = () => {
    if (!recordId.value || !previewRevisionId.value || !showRejectButtonInPreview.value) return
    const base = rejectEndpointResolved.value
    if (!base) return

    const url = base.replace(':id', recordId.value)
    const revisionId = previewRevisionId.value
    rejecting.value = true

    api.put(
      `${url}?revisionId=${revisionId}`,
      {},
      (response) => {
        rejecting.value = false
        closePreviewDialog()
        if (typeof applyRevisionRejectResponse === 'function') {
          applyRevisionRejectResponse(response)
        } else if (response.data?.form_fields) {
          window.location.reload()
          return
        }
        if (response.data?.revisions) {
          revisions.value = response.data.revisions
        } else {
          fetchRevisions()
        }
      },
      () => {
        rejecting.value = false
        Alert.openAlert({
          message: t('messages.revision.reject-failed'),
          variant: 'error',
          location: 'top',
        })
      }
    )
  }

  const executeRestoreFromPreview = () => {
    if (!recordId.value || !previewRevisionId.value || !canRestoreFromPreview.value || !canRestore.value) return

    const url = props.restoreEndpoint.replace(':id', recordId.value)
    const revisionId = previewRevisionId.value
    restoring.value = true

    api.get(
      `${url}?revisionId=${revisionId}`,
      (response) => {
        restoring.value = false
        closePreviewDialog()
        if (typeof applyRevisionRestoreResponse === 'function') {
          applyRevisionRestoreResponse(response)
        } else if (response.data?.form_fields) {
          window.location.reload()
          return
        }
        if (response.data?.revisions) {
          revisions.value = response.data.revisions
        } else {
          fetchRevisions()
        }
      },
      () => {
        restoring.value = false
        Alert.openAlert({
          message: t('Failed to restore revision.'),
          variant: 'error',
          location: 'top',
        })
      }
    )
  }

  /** Toolbar: open confirm dialog first. */
  const runApproveFromPreview = () => openRevisionConfirm('approve')
  const runRejectFromPreview = () => openRevisionConfirm('reject')
  const runRestoreFromPreview = () => openRevisionConfirm('restore')

  // Form model (revisionable_id) is often filled after mount; watch modelValue so fetch runs when the id appears.
  watch(
    () => props.modelValue,
    (val) => {
      if (val !== undefined && val !== null && val !== '') {
        fetchRevisions(val)
      } else {
        revisions.value = []
      }
    },
    { immediate: true }
  )
</script>

<template>
  <v-input
    v-model="input"
    :variant="variant"
    :density="density"
    hide-details
    class="v-input-revision"
    :loading="loading"
  >
    <template #default>
      <v-card variant="outlined" class="v-input-revision__card" rounded="lg">
        <div
          class="v-input-revision__header d-flex align-center justify-space-between pa-3"
          @click="expanded = !expanded"
        >
          <div class="d-flex align-center ga-2">
            <v-icon size="small" color="primary">mdi-history</v-icon>
            <span class="text-body-medium font-weight-medium">
              Revisions
              <span v-if="revisions.length" class="text-body-small text-medium-emphasis">({{ revisions.length }})</span>
            </span>
          </div>
          <div class="d-flex align-center ga-2">
            <span v-if="lastEditedText" class="text-body-small text-medium-emphasis">{{ lastEditedText }}</span>
            <v-icon size="small">{{ expanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
          </div>
        </div>

        <v-expand-transition>
          <div v-show="expanded" class="v-input-revision__expand">
            <v-divider />

            <div class="v-input-revision__items" :style="revisionListScrollStyle">
              <v-skeleton-loader v-if="loading" type="list-item-avatar-two-line" class="ma-2" />
              <v-list v-else density="compact" class="pa-0" bg-color="transparent">
                <RevisionItem
                  v-for="revision in sortedRevisions"
                  :key="revision.id"
                  :revision="revision"
                  :all-revisions="sortedRevisions"
                  @preview="openRevisionPreview"
                />
              </v-list>
            </div>
          </div>
        </v-expand-transition>
      </v-card>
    </template>
  </v-input>

  <RevisionPreviewDialog
    v-model="previewDialogActive"
    v-model:preview-tab="previewTab"
    v-model:preview-viewport="previewViewport"
    v-model:compare-base-revision-id="compareBaseRevisionId"
    :bindings="revisionPreviewBindings"
    @after-leave="onPreviewDialogAfterLeave"
    @close="closePreviewDialog"
    @select-revision="switchPreviewRevision"
    @approve="runApproveFromPreview"
    @reject="runRejectFromPreview"
    @restore="runRestoreFromPreview"
  />

  <RevisionConfirmDialog
    v-model="revisionConfirmOpen"
    :title="revisionConfirmTitle"
    :body="revisionConfirmBody"
    :submit-color="revisionConfirmSubmitColor"
    @cancel="closeRevisionConfirm"
    @confirm="submitRevisionConfirm"
  />

</template>

<style lang="sass" scoped>
  .v-input-revision
    &__card
      width: 100% !important
      max-width: 100% !important
      min-width: 0 !important
      border-color: rgba(0, 0, 0, 0.38) !important
      transition: border-color 0.2s ease
      &:hover,
      &:focus-within
        border-color: rgba(0, 0, 0, 0.87) !important

    &__header
      cursor: pointer

    &__expand
      width: 100%
      overflow-x: hidden

    &__items
      min-height: 0
</style>
