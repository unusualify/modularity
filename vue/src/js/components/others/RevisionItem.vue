<script setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps({
    revision: {
      type: Object,
      required: true,
    },
    allRevisions: {
      type: Array,
      default: () => [],
    },
  })

  const emit = defineEmits(['preview'])
  const { t } = useI18n()

  function getSourceDateByLabel(sourceLabel) {
    const source = props.allRevisions.find((r) => r.label === sourceLabel)

    return source ? formatDate(source.datetime) : sourceLabel
  }

  function getInitials(name) {
    if (!name) return '?'
    return name
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2)
  }

  function formatDate(dateString) {
    const date = new Date(dateString)
    return new Intl.DateTimeFormat(undefined, {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      hour12: false,
    }).format(date)
  }

  function onRowClick() {
    emit('preview', props.revision.id)
  }

  /** Shown after "·" — the source snapshot’s time (what was restored from). */
  const sourceDisplayDate = computed(() => {
    const r = props.revision
    if (r.source_datetime) {
      return formatDate(r.source_datetime)
    }
    if (r.source_label) {
      return getSourceDateByLabel(r.source_label)
    }
    return ''
  })

  /** Tooltip only on the source date: explains that this timestamp is the restored-from revision. */
  const sourceDateTooltip = computed(() =>
    t('messages.revision.source-date-tooltip')
  )

  const statusNorm = computed(() => String(props.revision?.status ?? 'approved').toLowerCase())

  const statusRowClass = computed(() => {
    if (statusNorm.value === 'pending') return 'revision-item-row--pending'
    if (statusNorm.value === 'rejected') return 'revision-item-row--rejected'
    return ''
  })

  const avatarColor = computed(() => {
    if (statusNorm.value === 'pending') return 'warning'
    if (statusNorm.value === 'rejected') return 'error'
    return 'primary'
  })

  const showStatusChip = computed(
    () => statusNorm.value === 'pending' || statusNorm.value === 'rejected'
  )

  const statusChipColor = computed(() =>
    statusNorm.value === 'pending' ? 'warning' : 'error'
  )

  const statusChipLabel = computed(() =>
    statusNorm.value === 'pending'
      ? t('messages.revision.status-pending')
      : t('messages.revision.status-rejected')
  )
</script>

<template>
  <v-list-item
    class="px-3 py-2 revision-item-row"
    :class="statusRowClass"
    @click="onRowClick"
  >
    <template #prepend>
      <v-avatar size="28" :color="avatarColor" variant="tonal" class="mr-3">
        <span class="text-body-small font-weight-medium">{{ getInitials(revision.author) }}</span>
      </v-avatar>
    </template>

    <v-list-item-title class="text-body-medium d-flex align-center flex-wrap ga-1">
      <span class="font-weight-medium">{{ revision.label }}</span>
      <v-chip
        v-if="showStatusChip"
        size="x-small"
        :color="statusChipColor"
        variant="flat"
        class="revision-status-chip"
        label
      >
        {{ statusChipLabel }}
      </v-chip>
      <span class="text-medium-emphasis"> · {{ revision.author }}</span>
    </v-list-item-title>

    <v-list-item-subtitle class="text-body-small text-medium-emphasis d-flex align-center flex-wrap ga-1">
      <span>{{ formatDate(revision.datetime) }}</span>
      <template v-if="sourceDisplayDate">
        <span class="mx-1">·</span>
        <v-tooltip location="bottom" max-width="320">
          <template #activator="{ props: tipProps }">
            <span
              v-bind="tipProps"
              class="revision-source-date-activator text-primary"
              @click.stop
            >
              {{ sourceDisplayDate }}
            </span>
          </template>
          <div class="text-body-small revision-source-tooltip">
            {{ sourceDateTooltip }}
          </div>
        </v-tooltip>
      </template>
    </v-list-item-subtitle>
  </v-list-item>
</template>

<style lang="sass" scoped>
  .revision-item-row
    cursor: pointer
    border-inline-start: 3px solid transparent
    transition: background 0.15s ease, border-color 0.15s ease
    &:hover
      background: rgba(0, 0, 0, 0.04)

  .revision-item-row--pending
    border-inline-start-color: rgb(var(--v-theme-warning))
    background: rgba(var(--v-theme-warning), 0.1)
    &:hover
      background: rgba(var(--v-theme-warning), 0.16)

  .revision-item-row--rejected
    border-inline-start-color: rgb(var(--v-theme-error))
    background: rgba(var(--v-theme-error), 0.08)
    &:hover
      background: rgba(var(--v-theme-error), 0.14)

  .revision-status-chip
    flex-shrink: 0

  .revision-source-date-activator
    cursor: help
    text-decoration: underline dotted
    text-underline-offset: 2px

  .revision-source-tooltip
    white-space: normal
</style>
