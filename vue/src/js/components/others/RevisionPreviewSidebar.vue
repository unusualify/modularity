<script setup>
  import { useI18n } from 'vue-i18n'
  import { revisionIdEq, formatRevisionDate } from '@/hooks/revision'

  const props = defineProps({
    revisions: {
      type: Array,
      default: () => [],
    },
    previewRevisionId: {
      type: [String, Number],
      default: null,
    },
  })

  const emit = defineEmits(['select'])

  const { t } = useI18n()

  function revisionStatusNorm(rev) {
    return String(rev?.status ?? 'approved').toLowerCase()
  }

  function revisionSidebarItemClass(rev, active) {
    const classes = ['revision-preview-sidebar-item']
    if (active) classes.push('revision-preview-sidebar-item--active')
    const s = revisionStatusNorm(rev)
    if (s === 'pending') classes.push('revision-preview-sidebar-item--pending')
    if (s === 'rejected') classes.push('revision-preview-sidebar-item--rejected')
    return classes
  }

  function showRevisionStatusChip(rev) {
    const s = revisionStatusNorm(rev)
    return s === 'pending' || s === 'rejected'
  }

  function revisionSidebarAvatarColor(rev, active) {
    const s = revisionStatusNorm(rev)
    if (s === 'pending') return 'warning'
    if (s === 'rejected') return 'error'
    return active ? 'primary' : 'surface-variant'
  }

  function revisionStatusChipLabel(rev) {
    return revisionStatusNorm(rev) === 'pending'
      ? t('messages.revision.status-pending')
      : t('messages.revision.status-rejected')
  }
</script>

<template>
  <div class="revision-preview-sidebar d-flex flex-column flex-shrink-0">
    <div class="text-body-small font-weight-medium pa-3 pb-2 text-medium-emphasis revision-preview-sidebar-header">
      {{ t('messages.revision.preview-sidebar-title') }}
    </div>
    <v-list
      class="pa-0 flex-grow-1 overflow-y-auto revision-preview-sidebar-list"
      bg-color="transparent"
      density="compact"
    >
      <template v-for="rev in revisions" :key="rev.id">
        <v-tooltip
          v-if="revisionIdEq(rev.id, previewRevisionId)"
          location="start"
        >
          <template #activator="{ props: tipProps }">
            <div v-bind="tipProps">
              <v-list-item
                :class="revisionSidebarItemClass(rev, true)"
                disabled
                rounded="lg"
              >
                <v-list-item-title class="text-body-medium d-flex align-center flex-wrap ga-1 min-width-0">
                  <span class="text-truncate">{{ rev.label }}</span>
                  <v-chip
                    v-if="showRevisionStatusChip(rev)"
                    size="x-small"
                    :color="revisionStatusNorm(rev) === 'pending' ? 'warning' : 'error'"
                    variant="flat"
                    class="flex-shrink-0"
                    label
                  >
                    {{ revisionStatusChipLabel(rev) }}
                  </v-chip>
                </v-list-item-title>
                <v-list-item-subtitle class="text-truncate">
                  {{ rev.author }} · {{ formatRevisionDate(rev.datetime) }}
                </v-list-item-subtitle>
                <template #prepend>
                  <v-avatar
                    size="28"
                    :color="revisionSidebarAvatarColor(rev, true)"
                    variant="flat"
                    class="text-body-small"
                  >
                    {{ String(rev.label || '?').slice(0, 1).toUpperCase() }}
                  </v-avatar>
                </template>
              </v-list-item>
            </div>
          </template>
          <span>{{ t('messages.revision.preview-sidebar-current') }}</span>
        </v-tooltip>
        <v-list-item
          v-else
          :class="revisionSidebarItemClass(rev, false)"
          rounded="lg"
          @click="emit('select', rev.id)"
        >
          <v-list-item-title class="text-body-medium d-flex align-center flex-wrap ga-1 min-width-0">
            <span class="text-truncate">{{ rev.label }}</span>
            <v-chip
              v-if="showRevisionStatusChip(rev)"
              size="x-small"
              :color="revisionStatusNorm(rev) === 'pending' ? 'warning' : 'error'"
              variant="flat"
              class="flex-shrink-0"
              label
            >
              {{ revisionStatusChipLabel(rev) }}
            </v-chip>
          </v-list-item-title>
          <v-list-item-subtitle class="text-truncate">
            {{ rev.author }} · {{ formatRevisionDate(rev.datetime) }}
          </v-list-item-subtitle>
          <template #prepend>
            <v-avatar
              size="28"
              :color="revisionSidebarAvatarColor(rev, false)"
              variant="tonal"
              class="text-body-small"
            >
              {{ String(rev.label || '?').slice(0, 1).toUpperCase() }}
            </v-avatar>
          </template>
        </v-list-item>
      </template>
    </v-list>
  </div>
</template>

<style lang="sass" scoped>
  .revision-preview-sidebar
    width: 280px
    min-width: 260px
    max-width: min(280px, 40vw)
    background: rgba(0, 0, 0, 0.22)

  .revision-preview-sidebar-header
    border-bottom: 1px solid rgba(255, 255, 255, 0.1)

  .revision-preview-sidebar-list
    min-height: 0

  .revision-preview-sidebar-item
    margin: 2px 8px
    border-inline-start: 3px solid transparent

  .revision-preview-sidebar-item--active
    opacity: 1

  .revision-preview-sidebar-item--pending
    border-inline-start-color: rgb(var(--v-theme-warning))
    background: rgba(var(--v-theme-warning), 0.12)

  .revision-preview-sidebar-item--rejected
    border-inline-start-color: rgb(var(--v-theme-error))
    background: rgba(var(--v-theme-error), 0.1)
</style>
