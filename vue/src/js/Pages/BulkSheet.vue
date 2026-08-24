<template>
  <div class="pa-4">
    <v-breadcrumbs
      v-if="breadcrumbItems.length"
      class="px-0 pt-0 pb-2"
      density="compact"
      :items="breadcrumbItems"
    >
      <template v-slot:title="{ item }">
        <v-breadcrumbs-item
          :disabled="!!item.disabled"
          :class="{ 'text-primary cursor-pointer': isBreadcrumbClickable(item) }"
          @click="onBreadcrumbClick(item, $event)">
          {{ item.title }}
        </v-breadcrumbs-item>
      </template>

    </v-breadcrumbs>

    <v-alert
      type="info"
      variant="tonal"
      class="mb-6"
      border="start"
    >
      {{ bulkT('intro', 'Upload a CSV file. Dry-run validates only; commit writes in a single transaction.') }}
    </v-alert>

    <v-card
      v-if="bulkToolSheet.length"
      class="mb-6"
    >
      <v-card-title class="text-body-large">
        {{ bulkT('columns', 'Expected columns') }}
      </v-card-title>
      <v-card-text>
        <v-table density="compact">
          <thead>
            <tr>
              <th class="text-left">
                {{ bulkT('column', 'Column') }}
              </th>
              <th class="text-left">
                {{ bulkT('csv_headers', 'CSV headers') }}
              </th>
              <th class="text-left">
                {{ bulkT('required', 'Required') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(col, idx) in bulkToolSheet"
              :key="idx"
            >
              <td>{{ col.label || col.key }}</td>
              <td class="text-medium-emphasis">
                {{ [col.key, ...(col.aliases || [])].join(', ') }}
              </td>
              <td>{{ col.required ? '✓' : '—' }}</td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>

    <v-card class="mb-6">
      <v-card-title class="text-title-large">
        {{ bulkT('csv_file', 'CSV file') }}
      </v-card-title>
      <v-card-text>
        <v-file-input
          v-model="fileModel"
          accept=".csv,text/csv"
          density="comfortable"
          variant="outlined"
          :label="bulkT('choose_csv', 'Choose CSV')"
          prepend-icon="mdi-paperclip"
          @update:model-value="onFilePicked"
        />
        <div class="d-flex flex-wrap ga-2 mt-4">
          <v-btn
            color="primary"
            :loading="loadingDryRun"
            :disabled="!csvText"
            @click="runDryRun"
          >
            {{ bulkT('dry_run', 'Dry-run (preview)') }}
          </v-btn>
          <v-btn
            color="warning"
            :loading="loadingCommit"
            :disabled="!csvText"
            @click="confirmCommit = true"
          >
            {{ bulkT('commit', 'Commit import') }}
          </v-btn>
          <v-btn
            variant="tonal"
            color="secondary"
            :href="exportUrl"
          >
            {{ bulkT('export', 'Download export (CSV)') }}
          </v-btn>
        </div>
      </v-card-text>
    </v-card>

    <v-card v-if="lastPayload !== null">
      <v-card-title class="text-body-large">
        {{ bulkT('result', 'Result') }}
      </v-card-title>
      <v-card-text>
        <div
          v-if="lastPayload.summary"
          class="text-body-medium mb-4"
        >
          <span v-if="lastPayload.summary.created !== undefined">
            {{ bulkT('created', 'Created') }}:
            {{ lastPayload.summary.created }} ·
          </span>
          <span v-if="lastPayload.summary.updated !== undefined">
            {{ bulkT('updated', 'Updated') }}:
            {{ lastPayload.summary.updated }} ·
          </span>
          {{ bulkT('valid_rows', 'Valid rows') }}:
          {{ lastPayload.summary.valid }} /
          {{ lastPayload.summary.total }}
        </div>
        <v-data-table
          v-if="(lastPayload.rows || []).length"
          :items="lastPayload.rows"
          :headers="previewTableHeaders"
          density="compact"
          class="bulk-sheet-table"
        >
          <template #item.valid="{ item }">
            <v-icon
              :color="item.valid ? 'success' : 'error'"
              :icon="item.valid ? 'mdi-check-circle' : 'mdi-alert-circle'"
              size="small"
            />
          </template>
          <template #item.errors="{ item }">
            <span class="text-error text-body-medium">{{ (item.errors || []).join('; ') }}</span>
          </template>
          <template #item.warnings="{ item }">
            <span class="text-warning text-body-medium">{{ (item.warnings || []).join('; ') }}</span>
          </template>
        </v-data-table>
        <pre
          v-else
          class="text-body-medium overflow-auto bulk-sheet-json"
        >{{ formattedJson }}</pre>
      </v-card-text>
    </v-card>

    <v-dialog
      v-model="confirmCommit"
      max-width="480"
    >
      <v-card>
        <v-card-title>
          {{ bulkT('confirm_title', 'Commit import?') }}
        </v-card-title>
        <v-card-text>
          {{
            bulkT(
              'confirm_body',
              'All rows must pass validation. The import runs in a single database transaction.'
            )
          }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="confirmCommit = false"
          >
            {{ te('messages.cancel') ? t('messages.cancel') : 'Cancel' }}
          </v-btn>
          <v-btn
            color="warning"
            @click="runCommit"
          >
            {{ te('messages.confirm') ? t('messages.confirm') : 'Confirm' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'
import { useAlert, useStepUpAwareJsonPost } from '@/hooks'

const { t, te } = useI18n({ useScope: 'global' })
const { openAlert } = useAlert()
const { postJson } = useStepUpAwareJsonPost()
const inertiaPage = usePage()

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

defineOptions({
  name: 'BulkSheet',
  layout: (layoutH, page) => layoutH(MainLayout, () => page),
})

const props = defineProps({
  toolKey: {
    type: String,
    required: true,
  },
  bulkToolSheet: {
    type: Array,
    default: () => [],
  },
  bulkSheetUi: {
    type: Object,
    default: () => ({}),
  },
  bulkSheetEndpoints: {
    type: Object,
    required: true,
  },
  bulkSheetPreviewTableColumns: {
    type: Array,
    default: null,
  },
})

/**
 * Server prop (submodule-specific) → vue-i18n `messages.bulk.*` → literal fallback.
 */
function bulkT (key, fallback) {
  const fromServer = props.bulkSheetUi?.[key]
  if (typeof fromServer === 'string' && fromServer !== '') {
    return fromServer
  }
  const path = `messages.bulk.${key}`
  if (te(path)) {
    return t(path)
  }
  return fallback
}

const previewTableHeaders = computed(() => {
  const c = props.bulkSheetPreviewTableColumns
  if (Array.isArray(c) && c.length) {
    return c
  }
  return [
    { title: bulkT('preview_line', 'Line'), key: 'line', width: '72px' },
    { title: bulkT('preview_ok', 'OK'), key: 'valid', sortable: false },
    { title: bulkT('preview_action', 'Action'), key: 'action' },
    { title: bulkT('preview_locale', 'Locale'), key: 'locale' },
    { title: bulkT('preview_from', 'From'), key: 'from_path' },
    { title: bulkT('preview_to', 'To'), key: 'to_path' },
    { title: bulkT('preview_errors', 'Errors'), key: 'errors', sortable: false },
    { title: bulkT('preview_warnings', 'Warnings'), key: 'warnings', sortable: false },
  ]
})

const exportUrl = computed(() => {
  const u = props.bulkSheetEndpoints?.export
  if (!u || !props.toolKey) {
    return u
  }
  return u.includes('?')
    ? `${u}&tool_key=${encodeURIComponent(props.toolKey)}`
    : `${u}?tool_key=${encodeURIComponent(props.toolKey)}`
})

const fileModel = ref(null)
const csvText = ref('')
const loadingDryRun = ref(false)
const loadingCommit = ref(false)
const lastPayload = ref(null)
const confirmCommit = ref(false)

const formattedJson = computed(() => {
  if (lastPayload.value === null) {
    return ''
  }
  try {
    return JSON.stringify(lastPayload.value, null, 2)
  } catch {
    return String(lastPayload.value)
  }
})

function onFilePicked (files) {
  const f = Array.isArray(files) ? files[0] : files
  if (!f) {
    csvText.value = ''
    return
  }
  const reader = new FileReader()
  reader.onload = () => {
    csvText.value = typeof reader.result === 'string' ? reader.result : ''
  }
  reader.readAsText(f, 'UTF-8')
}

async function runDryRun () {
  if (!props.bulkSheetEndpoints.dryRun || !csvText.value) {
    return
  }
  loadingDryRun.value = true
  lastPayload.value = null
  try {
    const { data } = await postJson(
      props.bulkSheetEndpoints.dryRun,
      { csv: csvText.value, tool_key: props.toolKey }
    )
    lastPayload.value = data
    openAlert({
      message: data?.ok === false
        ? (data.message || bulkT('dry_run_failed', 'Dry-run failed'))
        : bulkT('dry_run_ok', 'Dry-run OK'),
      variant: data?.ok === false ? 'error' : 'success',
    })
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? bulkT('request_failed', 'Request failed')
    openAlert({ message: String(msg), variant: 'error' })
    lastPayload.value = e.response?.data ?? { error: String(msg) }
  } finally {
    loadingDryRun.value = false
  }
}

async function runCommit () {
  if (!props.bulkSheetEndpoints.commit || !csvText.value) {
    return
  }
  confirmCommit.value = false
  loadingCommit.value = true
  lastPayload.value = null
  try {
    const { data } = await postJson(
      props.bulkSheetEndpoints.commit,
      { csv: csvText.value, tool_key: props.toolKey }
    )
    lastPayload.value = data
    openAlert({
      message: data?.ok === false
        ? (data.message || bulkT('import_failed', 'Import failed'))
        : bulkT('import_completed', 'Import completed'),
      variant: data?.ok === false ? 'error' : 'success',
    })
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? bulkT('request_failed', 'Request failed')
    openAlert({ message: String(msg), variant: 'error' })
    lastPayload.value = e.response?.data ?? { error: String(msg) }
  } finally {
    loadingCommit.value = false
  }
}
</script>

<style scoped>
.bulk-sheet-json {
  max-height: 420px;
  white-space: pre-wrap;
  word-break: break-word;
}
.bulk-sheet-table :deep(.v-data-table__td) {
  vertical-align: top;
}
</style>
