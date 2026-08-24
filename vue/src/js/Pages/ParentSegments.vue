<template>
  <div class="pa-4">
    <v-breadcrumbs
      v-if="breadcrumbItems.length"
      class="px-0 pt-0 pb-2"
      density="compact"
    >
      <template v-for="(item, index) in breadcrumbItems" :key="index">
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
      type="info"
      variant="tonal"
      class="mb-6"
      border="start"
    >
      {{ introText }}
    </v-alert>

    <div class="d-flex justify-end mb-4">
      <v-btn
        color="primary"
        @click="openCreate"
      >
        {{ $t('messages.add', 'Add') }}
      </v-btn>
    </div>

    <v-data-table
      :headers="headers"
      :items="segmentRows"
      class="elevation-1"
    >
      <template #item.enabled="{ item }">
        <v-chip
          :color="item.enabled ? 'success' : 'default'"
          size="small"
          variant="tonal"
        >
          {{ item.enabled ? $t('messages.yes', 'Yes') : $t('messages.no', 'No') }}
        </v-chip>
      </template>
      <template #item.targets="{ item }">
        <span class="text-body-medium">{{ formatTargets(item.targets) }}</span>
      </template>
      <template #item.actions="{ item }">
        <v-btn
          icon
          variant="text"
          size="small"
          :aria-label="$t('messages.edit', 'Edit')"
          @click="openEdit(item)"
        >
          <v-icon>mdi-pencil</v-icon>
        </v-btn>
        <v-btn
          icon
          variant="text"
          size="small"
          color="error"
          :aria-label="$t('messages.delete', 'Delete')"
          :loading="deletingId === item.id"
          @click="confirmDelete(item)"
        >
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <v-dialog
      v-model="dialogOpen"
      max-width="720"
      scrollable
    >
      <v-card>
        <v-card-title>{{ dialogTitle }}</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="form.normalized_prefix"
            :label="$t('messages.parent_segment_path', 'Path prefix')"
            variant="outlined"
            density="comfortable"
            class="mb-3"
            hint="e.g. blog/kurumsal (no leading slash required)"
            persistent-hint
          />
          <v-text-field
            v-model="form.admin_label"
            :label="$t('messages.label', 'Label')"
            variant="outlined"
            density="comfortable"
            class="mb-3"
          />
          <v-switch
            v-model="form.enabled"
            :label="$t('messages.enabled', 'Enabled')"
            color="primary"
            density="comfortable"
            class="mb-3"
          />
          <v-text-field
            v-model.number="form.sort_order"
            type="number"
            :label="$t('messages.sort_order', 'Sort order')"
            variant="outlined"
            density="comfortable"
            class="mb-4"
          />

          <div class="text-title-small mb-2">
            {{ $t('messages.parent_segment_targets', 'Bindings (model class + locale)') }}
          </div>
          <v-card
            v-for="(row, idx) in form.targets"
            :key="idx"
            variant="outlined"
            class="mb-2 pa-2"
          >
            <v-row density="compact">
              <v-col cols="12">
                <v-text-field
                  v-model="row.target_class"
                  label="target_class (FQCN)"
                  variant="outlined"
                  density="compact"
                  hide-details="auto"
                />
              </v-col>
              <v-col
                cols="6"
                sm="4"
              >
                <v-text-field
                  v-model="row.locale"
                  :label="$t('messages.locale', 'Locale')"
                  variant="outlined"
                  density="compact"
                  hide-details="auto"
                  hint="empty = all"
                  persistent-hint
                />
              </v-col>
              <v-col
                cols="6"
                sm="4"
              >
                <v-text-field
                  v-model.number="row.priority"
                  type="number"
                  label="priority"
                  variant="outlined"
                  density="compact"
                  hide-details="auto"
                />
              </v-col>
              <v-col
                cols="12"
                sm="4"
                class="d-flex align-center"
              >
                <v-btn
                  variant="text"
                  color="error"
                  @click="removeTargetRow(idx)"
                >
                  {{ $t('messages.remove', 'Remove') }}
                </v-btn>
              </v-col>
            </v-row>
          </v-card>
          <v-btn
            variant="tonal"
            size="small"
            @click="addTargetRow"
          >
            {{ $t('messages.add_binding', 'Add binding') }}
          </v-btn>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="dialogOpen = false"
          >
            {{ $t('messages.cancel', 'Cancel') }}
          </v-btn>
          <v-btn
            color="primary"
            :loading="saving"
            @click="submitForm"
          >
            {{ $t('messages.save', 'Save') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog
      v-model="deleteDialog"
      max-width="420"
    >
      <v-card>
        <v-card-title>{{ $t('messages.confirm_delete', 'Delete?') }}</v-card-title>
        <v-card-text>{{ deleteMessage }}</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="deleteDialog = false"
          >
            {{ $t('messages.cancel', 'Cancel') }}
          </v-btn>
          <v-btn
            color="error"
            :loading="deletingId !== null"
            @click="runDelete"
          >
            {{ $t('messages.delete', 'Delete') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import axios from 'axios'
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
  name: 'ParentSegments',
  layout: (layoutH, page) => layoutH(MainLayout, () => page),
})

const props = defineProps({
  segments: {
    type: Array,
    default: () => [],
  },
  parentSegmentsEndpoints: {
    type: Object,
    required: true,
  },
})

const headers = [
  { title: 'Path', key: 'normalized_prefix', sortable: true },
  { title: 'Label', key: 'admin_label', sortable: false },
  { title: 'Enabled', key: 'enabled', sortable: true },
  { title: 'Sort', key: 'sort_order', sortable: true },
  { title: 'Bindings', key: 'targets', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
]

const segmentRows = computed(() => props.segments ?? [])

const introText = computed(() =>
  te('messages.parent_segments_intro')
    ? t('messages.parent_segments_intro')
    : 'Shared path prefixes bound to model classes (no per-record id). All matching records use the same public URL namespace. Changes apply after page/registry sync.'
)

const dialogOpen = ref(false)
const saving = ref(false)
const editingId = ref(null)
const deletingId = ref(null)
const deleteDialog = ref(false)
const pendingDelete = ref(null)

const form = ref({
  normalized_prefix: '',
  admin_label: '',
  enabled: true,
  sort_order: 0,
  targets: [{ target_class: 'Modules\\Cms\\Entities\\Page', locale: '', priority: 0 }],
})

const dialogTitle = computed(() =>
  editingId.value ? t('messages.edit', 'Edit') : t('messages.add', 'Add')
)

const deleteMessage = computed(() => {
  const row = pendingDelete.value
  if (!row) {
    return ''
  }
  return String(row.normalized_prefix ?? '')
})

function formatTargets (targets) {
  if (!Array.isArray(targets) || targets.length === 0) {
    return '—'
  }
  return targets
    .map((t) => `${t.target_class} @ ${t.locale === '' || t.locale === null ? '*' : t.locale}`)
    .join('; ')
}

function openCreate () {
  editingId.value = null
  form.value = {
    normalized_prefix: '',
    admin_label: '',
    enabled: true,
    sort_order: 0,
    targets: [{ target_class: 'Modules\\Cms\\Entities\\Page', locale: '', priority: 0 }],
  }
  dialogOpen.value = true
}

function openEdit (row) {
  editingId.value = row.id
  const targets = Array.isArray(row.targets) && row.targets.length > 0
    ? row.targets.map((t) => ({
        target_class: t.target_class,
        locale: t.locale ?? '',
        priority: t.priority ?? 0,
      }))
    : [{ target_class: 'Modules\\Cms\\Entities\\Page', locale: '', priority: 0 }]
  form.value = {
    normalized_prefix: row.normalized_prefix ?? '',
    admin_label: row.admin_label ?? '',
    enabled: !!row.enabled,
    sort_order: row.sort_order ?? 0,
    targets,
  }
  dialogOpen.value = true
}

function addTargetRow () {
  form.value.targets.push({ target_class: '', locale: '', priority: 0 })
}

function removeTargetRow (idx) {
  form.value.targets.splice(idx, 1)
  if (form.value.targets.length === 0) {
    form.value.targets.push({ target_class: '', locale: '', priority: 0 })
  }
}

function buildUrl (template, id) {
  return String(template).replace('__ID__', String(id))
}

function formatValidationErrors (data) {
  if (!data?.errors || typeof data.errors !== 'object') {
    return data?.message ?? 'Validation failed'
  }
  return Object.values(data.errors).flat().join(' ')
}

async function submitForm () {
  if (!props.parentSegmentsEndpoints?.store) {
    return
  }
  saving.value = true
  try {
    const payload = {
      normalized_prefix: form.value.normalized_prefix,
      admin_label: form.value.admin_label || null,
      enabled: form.value.enabled,
      sort_order: form.value.sort_order,
      targets: form.value.targets.filter((r) => String(r.target_class ?? '').trim() !== ''),
    }
    if (editingId.value) {
      const url = buildUrl(props.parentSegmentsEndpoints.update, editingId.value)
      const res = await axios.patch(url, payload, {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        validateStatus: (s) => (s >= 200 && s < 300) || s === 422,
      })
      if (res.status === 422) {
        openAlert({ message: formatValidationErrors(res.data), variant: 'error' })
      } else {
        openAlert({ message: t('messages.saved', 'Saved'), variant: 'success' })
        dialogOpen.value = false
        router.reload({ only: ['segments'] })
      }
    } else {
      const res = await postJson(props.parentSegmentsEndpoints.store, payload)
      if (res.status === 422) {
        openAlert({ message: formatValidationErrors(res.data), variant: 'error' })
      } else {
        openAlert({ message: t('messages.saved', 'Saved'), variant: 'success' })
        dialogOpen.value = false
        router.reload({ only: ['segments'] })
      }
    }
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? 'Request failed'
    openAlert({ message: String(msg), variant: 'error' })
  } finally {
    saving.value = false
  }
}

function confirmDelete (row) {
  pendingDelete.value = row
  deleteDialog.value = true
}

async function runDelete () {
  const row = pendingDelete.value
  if (!row?.id || !props.parentSegmentsEndpoints?.destroy) {
    deleteDialog.value = false
    return
  }
  deletingId.value = row.id
  try {
    const url = buildUrl(props.parentSegmentsEndpoints.destroy, row.id)
    await axios.delete(url, {
      headers: { Accept: 'application/json' },
      validateStatus: (s) => (s >= 200 && s < 300) || s === 422,
    })
    openAlert({ message: t('messages.deleted', 'Deleted'), variant: 'success' })
    deleteDialog.value = false
    router.reload({ only: ['segments'] })
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? 'Request failed'
    openAlert({ message: String(msg), variant: 'error' })
  } finally {
    deletingId.value = null
    pendingDelete.value = null
  }
}
</script>

<style scoped>
</style>
