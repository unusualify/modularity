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
      v-if="promotionDisabled"
      type="warning"
      variant="tonal"
      class="mb-6"
      border="start"
    >
      {{ $t('messages.promotion_disabled_hint', 'CMS promotion is turned off. Set MODULAROUS_CMS_PROMOTION_ENABLED=true in the environment and clear config cache.') }}
    </v-alert>

    <template v-else>
    <v-alert
      type="info"
      variant="tonal"
      class="mb-6"
      border="start"
    >
      {{ introText }}
    </v-alert>

    <v-card class="mb-6">
      <v-card-title class="text-title-large">
        {{ $t('messages.promotion_scope', 'Scope') }}
      </v-card-title>
      <v-card-text>
        <v-row density="compact">
          <v-col
            v-for="key in scopeKeys"
            :key="key"
            cols="12"
            sm="6"
            md="4"
          >
            <v-switch
              v-model="scope[key]"
              :label="labelFor(key)"
              color="primary"
              density="compact"
              hide-details
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <div class="d-flex flex-wrap ga-2 mb-6">
      <v-btn
        color="primary"
        :loading="loadingDryRun"
        @click="runDryRun"
      >
        {{ $t('messages.promotion_dry_run', 'Dry-run (preview)') }}
      </v-btn>
      <v-btn
        color="warning"
        :loading="loadingExecute"
        @click="confirmExecute = true"
      >
        {{ $t('messages.promotion_execute', 'Execute') }}
      </v-btn>
    </div>

    <v-card v-if="lastResponse !== null">
      <v-card-title class="text-body-large">
        {{ $t('messages.promotion_last_result', 'Last response') }}
      </v-card-title>
      <v-card-text>
        <pre class="text-body-medium overflow-auto promotion-json">{{ formattedJson }}</pre>
      </v-card-text>
    </v-card>

    <v-dialog
      v-model="confirmExecute"
      max-width="480"
    >
      <v-card>
        <v-card-title>{{ $t('messages.promotion_confirm_title', 'Run promotion execute?') }}</v-card-title>
        <v-card-text>
          {{ $t('messages.promotion_confirm_body', 'This will flush Modularous cache when not in dry-run mode. Data is not copied between environments.') }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="confirmExecute = false"
          >
            {{ $t('messages.cancel', 'Cancel') }}
          </v-btn>
          <v-btn
            color="warning"
            @click="runExecute"
          >
            {{ $t('messages.confirm', 'Confirm') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    </template>
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
  name: 'Promotion',
  layout: (layoutH, page) => layoutH(MainLayout, () => page),
})

const props = defineProps({
  promotionDisabled: {
    type: Boolean,
    default: false,
  },
  promotionEndpoints: {
    type: Object,
    required: true,
  },
  defaultScope: {
    type: Object,
    default: () => ({}),
  },
})

const scopeKeys = ['settings', 'content', 'seo', 'redirects', 'layouts']

const scope = ref({})
scopeKeys.forEach((k) => {
  scope.value[k] = props.defaultScope[k] !== undefined ? !!props.defaultScope[k] : true
})

const loadingDryRun = ref(false)
const loadingExecute = ref(false)
const lastResponse = ref(null)
const confirmExecute = ref(false)

const introText = computed(() =>
  te('messages.promotion_intro')
    ? t('messages.promotion_intro')
    : 'Preview counts for the current database (dry-run). Execute flushes the Modularous cache; it does not sync data between servers.'
)

function labelFor (key) {
  const map = {
    settings: 'Site settings',
    content: 'Pages & registry',
    seo: 'SEO (translations)',
    redirects: 'Redirects',
    layouts: 'Layouts',
  }
  return map[key] ?? key
}

const formattedJson = computed(() => {
  if (lastResponse.value === null) {
    return ''
  }
  try {
    return JSON.stringify(lastResponse.value, null, 2)
  } catch {
    return String(lastResponse.value)
  }
})

async function runDryRun () {
  if (props.promotionDisabled || !props.promotionEndpoints.dryRun) {
    return
  }
  loadingDryRun.value = true
  lastResponse.value = null
  try {
    const { data } = await postJson(
      props.promotionEndpoints.dryRun,
      { scope: scope.value }
    )
    lastResponse.value = data
    openAlert({ message: data?.ok === false ? (data.message || 'Dry-run failed') : 'Dry-run OK', variant: data?.ok === false ? 'error' : 'success' })
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? 'Request failed'
    openAlert({ message: String(msg), variant: 'error' })
    lastResponse.value = e.response?.data ?? { error: String(msg) }
  } finally {
    loadingDryRun.value = false
  }
}

async function runExecute () {
  if (props.promotionDisabled || !props.promotionEndpoints.execute) {
    return
  }
  confirmExecute.value = false
  loadingExecute.value = true
  lastResponse.value = null
  try {
    const { data } = await postJson(
      props.promotionEndpoints.execute,
      { scope: scope.value, dry_run: false }
    )
    lastResponse.value = data
    openAlert({
      message: data?.ok === false ? (data.message || 'Execute failed') : 'Execute finished',
      variant: data?.ok === false ? 'error' : 'success',
    })
  } catch (e) {
    const msg = e.response?.data?.message ?? e.message ?? 'Request failed'
    openAlert({ message: String(msg), variant: 'error' })
    lastResponse.value = e.response?.data ?? { error: String(msg) }
  } finally {
    loadingExecute.value = false
  }
}
</script>

<style scoped>
.promotion-json {
  max-height: 420px;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
