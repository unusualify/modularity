<template>
  <div
    class="module-route-inspect"
    data-viewport-fit-root
  >
    <div class="module-route-inspect__chrome">
      <v-breadcrumbs
        v-if="breadcrumbItems.length"
        class="px-0 pt-0 pb-2"
        density="compact"
      >
        <template
          v-for="(item, index) in breadcrumbItems"
          :key="index"
        >
          <v-breadcrumbs-item
            :disabled="!!item.disabled"
            :class="{ 'text-primary cursor-pointer': isBreadcrumbClickable(item) }"
            @click="onBreadcrumbClick(item, $event)"
          >
            {{ item.title }}
          </v-breadcrumbs-item>
        </template>
      </v-breadcrumbs>

      <v-alert
        v-if="inspectDisabled"
        type="warning"
        variant="tonal"
        class="mb-4"
        border="start"
      >
        {{ $t('messages.module_route_inspect_disabled_hint', 'Module Route Inspect is turned off or you lack access. Set MODULAROUS_MODULE_ROUTE_INSPECT_ENABLED=true and ensure your role is in allowed_roles.') }}
      </v-alert>

      <template v-else>
        <v-alert
          type="info"
          variant="tonal"
          class="mb-4"
          border="start"
          density="compact"
        >
          {{ $t('messages.module_route_inspect_intro', 'Inspect module routes for enable/disable status, feature traits (translation, CMR, revisions, …), and consistency findings.') }}
        </v-alert>

        <v-alert
          v-if="errorMessage"
          type="error"
          variant="tonal"
          class="mb-4"
          border="start"
          density="compact"
          closable
          @click:close="errorMessage = null"
        >
          {{ errorMessage }}
        </v-alert>

        <v-card class="mb-4">
          <v-card-title class="text-h6 d-flex align-center flex-wrap ga-2 py-3">
            <span>{{ $t('messages.module_route_inspect_filters', 'Filters') }}</span>
            <v-spacer />
            <v-chip
              v-if="findingCount > 0"
              color="warning"
              size="small"
              variant="tonal"
            >
              {{ findingCount }} {{ $t('messages.findings', 'findings') }}
            </v-chip>
          </v-card-title>
          <v-card-text class="pb-3">
            <v-row dense>
              <v-col
                cols="12"
                md="3"
              >
                <v-select
                  v-model="moduleFilter"
                  :items="moduleOptions"
                  :label="$t('messages.module', 'Module')"
                  clearable
                  density="comfortable"
                  hide-details
                />
              </v-col>
              <v-col
                cols="12"
                md="3"
              >
                <v-text-field
                  v-model="routeFilter"
                  :label="$t('messages.route', 'Route')"
                  clearable
                  density="comfortable"
                  hide-details
                  @keyup.enter="loadInspect"
                />
              </v-col>
              <v-col
                cols="12"
                md="3"
              >
                <v-select
                  v-model="featureFilter"
                  :items="featureSelectItems"
                  :label="$t('messages.features', 'Features')"
                  multiple
                  chips
                  clearable
                  density="comfortable"
                  hide-details
                />
              </v-col>
              <v-col
                cols="12"
                md="3"
                class="d-flex align-center ga-3"
              >
                <v-switch
                  v-model="findingsOnly"
                  color="primary"
                  density="compact"
                  hide-details
                  class="mx-2"
                  :label="$t('messages.findings_only', 'Findings only')"
                />
                <v-btn
                  color="primary"
                  :loading="loading"
                  @click="loadInspect"
                >
                  {{ $t('messages.refresh', 'Refresh') }}
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </template>
    </div>

    <div
      v-if="!inspectDisabled"
      v-viewport-fit="viewportFitOptions"
      class="module-route-inspect__table-shell"
    >
      <v-data-table
        :headers="headers"
        :items="tableItems"
        :loading="loading"
        item-value="key"
        density="comfortable"
        fixed-header
        :height="tableBodyHeight"
        class="module-route-inspect__table text-body-2"
      >
        <template #item.enabled="{ item }">
          <v-switch
            v-if="allowStatusToggle"
            :model-value="item.enabled"
            color="primary"
            density="compact"
            hide-details
            :loading="togglingKey === item.key"
            :disabled="togglingKey === item.key"
            @update:model-value="(value) => onToggleEnabled(item, value)"
          />
          <v-chip
            v-else
            size="small"
            :color="item.enabled ? 'success' : 'default'"
            variant="tonal"
          >
            {{ item.enabled ? $t('messages.enabled', 'enabled') : $t('messages.disabled', 'disabled') }}
          </v-chip>
        </template>

        <template #item.parent="{ item }">
          <v-icon
            v-if="item.parent"
            icon="mdi-check"
            size="small"
            color="primary"
          />
        </template>

        <template
          v-for="featureKey in activeHighlightKeys"
          :key="`feat-${featureKey}`"
          #[`item.feature_${featureKey}`]="{ item }"
        >
          <v-icon
            v-if="item.features?.[featureKey]?.present"
            icon="mdi-check"
            size="small"
            color="success"
          />
        </template>

        <template #item.other="{ item }">
          <div class="d-flex flex-wrap ga-1">
            <v-chip
              v-for="key in item.otherFeatures"
              :key="`${item.key}-${key}`"
              size="x-small"
              variant="tonal"
            >
              {{ key }}
            </v-chip>
          </div>
        </template>

        <template #item.findings="{ item }">
          <v-menu
            v-if="item.findings?.length"
            location="bottom"
          >
            <template #activator="{ props: menuProps }">
              <v-chip
                v-bind="menuProps"
                size="small"
                color="warning"
                variant="tonal"
              >
                {{ item.findings.length }}
              </v-chip>
            </template>
            <v-list
              density="compact"
              max-width="420"
            >
              <v-list-item
                v-for="(finding, index) in item.findings"
                :key="`${item.key}-finding-${index}`"
                :title="finding.message"
                :subtitle="findingSubtitle(finding)"
              >
                <template
                  v-if="finding.remedy?.artisan || finding.remedy?.tip"
                  #append
                >
                  <div class="d-flex align-center ga-1">
                    <v-btn
                      v-if="finding.remedy?.artisan"
                      icon
                      size="x-small"
                      variant="text"
                      :title="$t('messages.copy_command', 'Copy command')"
                      @click.stop="copyRemedy(finding.remedy.artisan)"
                    >
                      <v-icon
                        icon="mdi-content-copy"
                        size="16"
                      />
                    </v-btn>
                    <v-btn
                      v-if="allowHeal && finding.remedy?.action === 'remake' && finding.remedy?.safe"
                      icon
                      size="x-small"
                      variant="text"
                      color="primary"
                      :loading="healingKey === healKey(item, finding)"
                      :title="$t('messages.heal_dry_run', 'Heal (dry-run)')"
                      @click.stop="runHeal(item, finding, true)"
                    >
                      <v-icon
                        icon="mdi-auto-fix"
                        size="16"
                      />
                    </v-btn>
                  </div>
                </template>
              </v-list-item>
            </v-list>
          </v-menu>
          <span
            v-else
            class="text-medium-emphasis"
          >—</span>
        </template>
      </v-data-table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'
import { useAlert, useModuleRouteInspect } from '@/hooks'

const { t } = useI18n({ useScope: 'global' })
const { openAlert } = useAlert()
const inertiaPage = usePage()

defineOptions({
  name: 'ModuleRouteInspect',
  layout: (layoutH, page) => layoutH(MainLayout, () => page),
})

const props = defineProps({
  inspectDisabled: {
    type: Boolean,
    default: false,
  },
  inspectEndpoints: {
    type: Object,
    required: true,
  },
  allowStatusToggle: {
    type: Boolean,
    default: false,
  },
  allowHeal: {
    type: Boolean,
    default: false,
  },
  highlightFeatureKeys: {
    type: Array,
    default: () => [],
  },
  featureKeys: {
    type: Array,
    default: () => [],
  },
  isSuperadmin: {
    type: Boolean,
    default: false,
  },
})

/**
 * Shell height from v-viewport-fit (full leftover viewport).
 * Vuetify applies `height` only to the scrollable table pane; the footer
 * (pagination) sits below that pane — subtract so footer stays inside the shell.
 */
const tableHeight = ref(360)
const TABLE_FOOTER_OFFSET_PX = 64

const tableBodyHeight = computed(() => Math.max(160, tableHeight.value - TABLE_FOOTER_OFFSET_PX))

const viewportFitOptions = {
  chrome: '.module-route-inspect__chrome',
  heightRef: tableHeight,
  offset: 12,
  min: 240,
}

const breadcrumbItems = computed(() => {
  const raw = inertiaPage.props.mainConfiguration?.navigation?.breadcrumbs
  return Array.isArray(raw) ? raw : []
})

function isBreadcrumbClickable (item) {
  return Boolean(item?.href) && !item?.disabled
}

function onBreadcrumbClick (item, e) {
  if (!isBreadcrumbClickable(item)) {
    return
  }
  e?.preventDefault()
  router.visit(item.href, { preserveScroll: true })
}

const {
  entries,
  findingCount,
  featureKeys,
  highlightFeatureKeys,
  loading,
  togglingKey,
  healingKey,
  errorMessage,
  allowHeal: allowHealFromApi,
  moduleFilter,
  routeFilter,
  findingsOnly,
  featureFilter,
  moduleOptions,
  loadInspect,
  setEnabled,
  healFinding,
  presentFeatures,
} = useModuleRouteInspect(props.inspectEndpoints)

const allowHeal = computed(() => props.allowHeal || allowHealFromApi.value)

if (Array.isArray(props.featureKeys) && props.featureKeys.length) {
  featureKeys.value = props.featureKeys
}
if (Array.isArray(props.highlightFeatureKeys) && props.highlightFeatureKeys.length) {
  highlightFeatureKeys.value = props.highlightFeatureKeys
}

function findingSubtitle (finding) {
  const bits = [finding.severity, finding.code]
  if (finding.feature) {
    bits.push(finding.feature)
  }
  if (finding.remedy?.artisan) {
    bits.push(finding.remedy.artisan)
  } else if (finding.remedy?.tip) {
    bits.push(finding.remedy.tip)
  }
  return bits.filter(Boolean).join(' · ')
}

function healKey (item, finding) {
  return `${item.module}::${item.route}::${finding.code}`
}

async function copyRemedy (text) {
  try {
    await navigator.clipboard.writeText(text)
    openAlert({ type: 'success', text: t('messages.copied', 'Copied') })
  } catch {
    openAlert({ type: 'error', text: t('messages.copy_failed', 'Copy failed') })
  }
}

async function runHeal (item, finding, dryRun = true) {
  try {
    const data = await healFinding(item.module, item.route, finding.code, {
      feature: finding.feature,
      dryRun,
      force: !finding.remedy?.safe,
    })
    openAlert({
      type: data?.ok ? 'success' : 'warning',
      text: dryRun
        ? t('messages.heal_dry_run_done', 'Heal dry-run finished')
        : t('messages.heal_done', 'Heal finished'),
    })
  } catch {
    // errorMessage set in hook
  }
}

const activeHighlightKeys = computed(() => {
  const keys = highlightFeatureKeys.value?.length
    ? highlightFeatureKeys.value
    : props.highlightFeatureKeys
  return Array.isArray(keys) ? keys : []
})

const featureSelectItems = computed(() => {
  const keys = featureKeys.value?.length ? featureKeys.value : props.featureKeys
  return (Array.isArray(keys) ? keys : []).map((key) => ({
    title: key,
    value: key,
  }))
})

const headers = computed(() => {
  const base = [
    { title: t('messages.module', 'Module'), key: 'module', sortable: true },
    { title: t('messages.route', 'Route'), key: 'route', sortable: true },
    { title: t('messages.status', 'Status'), key: 'enabled', sortable: true },
    { title: t('messages.parent', 'Parent'), key: 'parent', sortable: true },
  ]

  for (const key of activeHighlightKeys.value) {
    base.push({
      title: key,
      key: `feature_${key}`,
      sortable: false,
    })
  }

  base.push(
    { title: t('messages.other', 'Other'), key: 'other', sortable: false },
    { title: t('messages.findings', 'Findings'), key: 'findings', sortable: false },
  )

  return base
})

const tableItems = computed(() => {
  const highlighted = new Set(activeHighlightKeys.value)
  return entries.value.map((entry) => {
    const present = presentFeatures(entry)
    return {
      ...entry,
      key: `${entry.module}::${entry.route}`,
      otherFeatures: present.filter((key) => !highlighted.has(key)),
    }
  })
})

async function onToggleEnabled (item, value) {
  try {
    await setEnabled(item.module, item.route, value)
    openAlert({
      message: value
        ? t('messages.route_enabled', 'Route enabled')
        : t('messages.route_disabled', 'Route disabled'),
      variant: 'success',
    })
  } catch (e) {
    openAlert({
      message: e.response?.data?.message ?? e.message ?? 'Failed to update status',
      variant: 'error',
    })
  }
}

watch([moduleFilter, findingsOnly, featureFilter], () => {
  if (!props.inspectDisabled) {
    loadInspect()
  }
})

watch(loading, (isLoading) => {
  if (!isLoading) {
    // Remeasure after rows render (chrome may have settled too)
    requestAnimationFrame(() => {
      window.dispatchEvent(new Event('resize'))
    })
  }
})

onMounted(() => {
  if (!props.inspectDisabled) {
    loadInspect()
  }
})
</script>

<style scoped>
.module-route-inspect {
  overflow: hidden;
}

.module-route-inspect__table-shell {
  width: 100%;
  min-height: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.module-route-inspect__table {
  width: 100%;
  flex: 1 1 auto;
  min-height: 0;
}
</style>
