<template>
  <div class="pa-4 ue-cms-sitemap-index">
    <v-breadcrumbs
      v-if="breadcrumbItems.length"
      class="px-0 pt-0 pb-2"
      density="compact"
      :items="breadcrumbItems"
    >
      <template #title="{ item }">
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
      v-if="publicSitemapUrl"
      type="info"
      variant="tonal"
      class="mb-4"
      border="start"
      density="compact"
    >
      <a
        :href="publicSitemapUrl"
        target="_blank"
        rel="noopener noreferrer"
      >{{ publicSitemapUrl }}</a>
    </v-alert>

    <v-alert
      type="info"
      variant="tonal"
      class="mb-4"
      border="start"
    >
      {{ introText }}
    </v-alert>

    <div class="d-flex flex-wrap ga-2 mb-6">
      <v-btn
        color="primary"
        :loading="loadingDry"
        :disabled="!sitemapEndpoints.dryRun"
        @click="runDryRun"
      >
        {{ t('modules.cms.sitemap.dry_run', 'Dry-run (preview XML)') }}
      </v-btn>
      <v-btn
        color="warning"
        :loading="loadingCommit"
        :disabled="!sitemapEndpoints.commit"
        @click="confirmCommit = true"
      >
        {{ t('modules.cms.sitemap.commit', 'Commit to live cache') }}
      </v-btn>
    </div>

    <v-data-table
      :items="rowModels"
      :headers="dataHeaders"
      density="compact"
      :items-per-page="50"
    >
      <template #item.changefreq="{ item }">
        <v-select
          v-model="item.draftChangefreq"
          :items="changefreqItems"
          density="compact"
          hide-details
          variant="outlined"
          class="sitemap-cf"
        />
      </template>
      <template #item.priority="{ item }">
        <v-text-field
          v-model="item.draftPriority"
          type="number"
          step="0.1"
          min="0"
          max="1"
          density="compact"
          hide-details
          variant="outlined"
          class="sitemap-prio"
        />
      </template>
      <template #item.save="{ item }">
        <v-btn
          size="small"
          color="primary"
          variant="tonal"
          :loading="item.saving"
          :disabled="!sitemapEndpoints.itemUpsert"
          @click="saveRow(item)"
        >
          {{ t('modules.cms.sitemap.save_row', 'Save') }}
        </v-btn>
      </template>
    </v-data-table>

    <div
      v-if="lastDryPayload && !dryRunPreviewOpen"
      class="mt-4"
    >
      <v-btn
        variant="tonal"
        color="primary"
        prepend-icon="mdi-eye"
        @click="dryRunPreviewOpen = true"
      >
        {{ t('modules.cms.sitemap.reopen_preview', 'Reopen last dry-run preview') }}
      </v-btn>
    </div>

    <v-dialog
      v-model="dryRunPreviewOpen"
      max-width="1280"
      width="92vw"
      scrollable
    >
      <v-card v-if="lastDryPayload">
        <v-card-title class="d-flex align-center flex-wrap ga-2 pe-2">
          <span class="text-body-large">
            {{ t('modules.cms.sitemap.last_dry', 'Last dry-run') }}
          </span>
          <v-spacer />
          <span class="text-body-medium text-medium-emphasis font-weight-regular">
            {{ t('modules.cms.sitemap.url_count', 'URL count') }}: {{ lastDryPayload.urlCount }}
            ·
            {{ t('modules.cms.sitemap.bytes', 'Bytes') }}: {{ lastDryPayload.bytes }}
          </span>
          <v-btn
            icon="mdi-close"
            variant="text"
            size="small"
            @click="dryRunPreviewOpen = false"
          />
        </v-card-title>

        <v-tabs
          v-model="dryRunPreviewTab"
          color="primary"
          density="compact"
          class="px-4"
        >
          <v-tab value="browser">
            {{ t('modules.cms.sitemap.preview_browser', 'Browser view') }}
          </v-tab>
          <v-tab value="raw">
            {{ t('modules.cms.sitemap.preview_raw', 'Raw XML') }}
          </v-tab>
        </v-tabs>

        <v-divider />

        <v-card-text class="pa-0 sitemap-preview-dialog-body">
          <v-tabs-window v-model="dryRunPreviewTab">
            <v-tabs-window-item value="browser">
              <div class="pa-4">
                <iframe
                  v-if="lastDryPayload.html"
                  class="sitemap-html-preview"
                  title="Sitemap dry-run preview"
                  :srcdoc="lastDryPayload.html"
                />
                <v-alert
                  v-else
                  type="warning"
                  variant="tonal"
                  density="compact"
                >
                  {{ t('modules.cms.sitemap.styled_preview_unavailable', 'Styled preview unavailable; use the Raw XML tab.') }}
                </v-alert>
              </div>
            </v-tabs-window-item>
            <v-tabs-window-item value="raw">
              <pre class="text-body-small overflow-auto sitemap-xml-preview ma-4">{{ lastDryPayload.xml }}</pre>
            </v-tabs-window-item>
          </v-tabs-window>
        </v-card-text>

        <v-divider />

        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="dryRunPreviewOpen = false"
          >
            {{ te('messages.close') ? t('messages.close') : 'Close' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog
      v-model="confirmCommit"
      max-width="480"
    >
      <v-card>
        <v-card-title>
          {{ t('modules.cms.sitemap.commit_confirm', 'Write sitemap to live cache?') }}
        </v-card-title>
        <v-card-text>
          {{ t('modules.cms.sitemap.commit_hint', 'This updates the public sitemap.xml served from cache.') }}
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
            :loading="loadingCommit"
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
  import { computed, ref, watch } from 'vue'
  import { router, usePage } from '@inertiajs/vue3'
  import { useI18n } from 'vue-i18n'
  import MainLayout from '@/Pages/Layouts/MainLayout.vue'
  import { useAlert, useStepUpAwareJsonPost } from '@/hooks'

  const props = defineProps({
    tableAttributes: { type: Object, default: () => ({}) },
    endpoints: { type: Object, default: () => ({}) },
  })

  const { t, te } = useI18n({ useScope: 'global' })
  const { openAlert } = useAlert()
  const { postJson } = useStepUpAwareJsonPost()
  const inertiaPage = usePage()

  const sitemapPanel = computed(() => props.tableAttributes?.sitemapPanel || {})
  const publicSitemapUrl = computed(() => sitemapPanel.value?.publicSitemapUrl || null)
  const introText = t(
    'modules.cms.sitemap.intro',
    'Table lists every URL line that will appear in the sitemap. Edit changefreq and priority per public page, then use dry-run to preview XML and commit to publish.',
  )

  const sitemapEndpoints = computed(() => {
    const e = props.endpoints || {}
    return {
      dryRun: e.sitemapDryRun,
      commit: e.sitemapCommit,
      itemUpsert: e.sitemapItemUpsert,
    }
  })

  const changefreqItems = [
    'always',
    'hourly',
    'daily',
    'weekly',
    'monthly',
    'yearly',
    'never',
  ]

  const dataHeaders = [
    { title: '#', key: 'sort', width: '64px' },
    { title: t('modules.cms.sitemap.col_locale', 'Locale'), key: 'locale' },
    { title: t('modules.cms.sitemap.col_path', 'Path'), key: 'normalized_path' },
    { title: t('modules.cms.sitemap.col_url', 'URL'), key: 'loc' },
    { title: t('modules.cms.sitemap.col_lastmod', 'Last mod'), key: 'lastmod' },
    { title: t('modules.cms.sitemap.changefreq', 'Change frequency'), key: 'changefreq' },
    { title: t('modules.cms.sitemap.priority', 'Priority'), key: 'priority' },
    { title: '', key: 'save', sortable: false, width: '100px' },
  ]

  const rowModels = ref([])

  watch(
    () => sitemapPanel.value?.rows,
    (rows) => {
      if (!Array.isArray(rows)) {
        rowModels.value = []
        return
      }
      rowModels.value = rows.map((r) => ({
        ...r,
        draftChangefreq: r.changefreq,
        draftPriority: String(r.priority),
        saving: false,
      }))
    },
    { immediate: true, deep: true }
  )

  const breadcrumbItems = computed(() => {
    const raw = inertiaPage.props?.tableAttributes?.breadcrumbs
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

  const loadingDry = ref(false)
  const loadingCommit = ref(false)
  const lastDryPayload = ref(null)
  const confirmCommit = ref(false)
  const dryRunPreviewOpen = ref(false)
  const dryRunPreviewTab = ref('browser')

  function transformXmlWithXsl (xmlString, xslString) {
    if (!xmlString || !xslString || typeof window.XSLTProcessor === 'undefined') {
      return null
    }
    try {
      const parser = new DOMParser()
      const xmlDoc = parser.parseFromString(xmlString, 'application/xml')
      const xslDoc = parser.parseFromString(xslString, 'application/xml')
      if (xmlDoc.querySelector('parsererror') || xslDoc.querySelector('parsererror')) {
        return null
      }
      const proc = new XSLTProcessor()
      proc.importStylesheet(xslDoc)
      const result = proc.transformToDocument(xmlDoc)
      if (!result) {
        return null
      }
      return new XMLSerializer().serializeToString(result)
    } catch (e) {
      console.warn('[CmsSitemap] client XSLT failed', e)
      return null
    }
  }

  function withStyledHtml (payload) {
    if (!payload || typeof payload !== 'object') {
      return payload
    }
    if (payload.html) {
      return payload
    }
    const html = transformXmlWithXsl(payload.xml, payload.xsl)
    return html ? { ...payload, html } : payload
  }

  async function runDryRun () {
    const url = sitemapEndpoints.value.dryRun
    if (!url) { return }
    loadingDry.value = true
    lastDryPayload.value = null
    dryRunPreviewOpen.value = false
    try {
      const { data } = await postJson(url, {})
      lastDryPayload.value = withStyledHtml(data)
      dryRunPreviewTab.value = lastDryPayload.value?.html ? 'browser' : 'raw'
      dryRunPreviewOpen.value = true
      openAlert({
        message: t('modules.cms.sitemap.dry_ok', 'Dry-run complete.'),
        variant: 'success',
      })
    } catch (e) {
      const msg = e.response?.data?.message ?? e.message ?? t('modules.cms.sitemap.request_failed', 'Request failed')
      openAlert({ message: String(msg), variant: 'error' })
    } finally {
      loadingDry.value = false
    }
  }

  async function runCommit () {
    const url = sitemapEndpoints.value.commit
    if (!url) { return }
    loadingCommit.value = true
    try {
      const { data } = await postJson(url, {})
      confirmCommit.value = false
      openAlert({
        message: data?.message || t('modules.cms.sitemap.commit_ok', 'Sitemap cache updated.'),
        variant: 'success',
      })
      router.reload({ only: ['tableAttributes', 'endpoints'] })
    } catch (e) {
      const msg = e.response?.data?.message ?? e.message ?? t('modules.cms.sitemap.request_failed', 'Request failed')
      openAlert({ message: String(msg), variant: 'error' })
    } finally {
      loadingCommit.value = false
    }
  }

  async function saveRow (item) {
    const url = sitemapEndpoints.value.itemUpsert
    if (!url) { return }
    const prio = parseFloat(String(item.draftPriority).replace(',', '.'))
    if (Number.isNaN(prio) || prio < 0 || prio > 1) {
      openAlert({ message: t('modules.cms.sitemap.bad_priority', 'Priority must be between 0 and 1.'), variant: 'error' })
      return
    }
    item.saving = true
    try {
      const { data } = await postJson(url, {
        sitemapable_type: item.urlable_type,
        sitemapable_id: item.urlable_id,
        changefreq: item.draftChangefreq,
        priority: String(prio),
      })
      if (data?.ok && data?.item) {
        item.sitemapable_item_id = data.item.id
        item.changefreq = data.item.changefreq
        item.priority = data.item.priority
        item.draftChangefreq = data.item.changefreq
        item.draftPriority = data.item.priority
      }
      openAlert({ message: t('modules.cms.sitemap.save_ok', 'Saved.'), variant: 'success' })
    } catch (e) {
      const msg = e.response?.data?.message ?? (e.response?.data?.errors
        ? Object.values(e.response.data.errors).flat().join(' ')
        : e.message) ?? t('modules.cms.sitemap.request_failed', 'Request failed')
      openAlert({ message: String(msg), variant: 'error' })
    } finally {
      item.saving = false
    }
  }

  defineOptions({
    name: 'CmsSitemapIndex',
    layout: (h, page) => h(MainLayout, () => page),
  })
</script>

<style scoped>
.sitemap-cf { min-width: 150px; max-width: 200px; }
.sitemap-prio { max-width: 110px; }
.sitemap-preview-dialog-body {
  min-height: 60vh;
}
.sitemap-xml-preview {
  max-height: calc(70vh - 48px);
  white-space: pre-wrap;
  word-break: break-word;
}
.sitemap-html-preview {
  display: block;
  width: 100%;
  height: calc(70vh - 48px);
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  background: #fff;
}
</style>
