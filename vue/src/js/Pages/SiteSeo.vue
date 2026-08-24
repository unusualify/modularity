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

    <v-card>
      <v-card-title class="text-title-large">
        {{ $t('messages.site_seo_robots_title', 'Global robots.txt') }}
      </v-card-title>
      <v-card-text>
        <p class="text-body-medium mb-4">
          {{ $t('messages.site_seo_robots_moved', 'Global robots.txt is now managed under System Settings → General (SEO section).') }}
        </p>
        <v-btn
          v-if="systemSettingsUrl"
          color="primary"
          variant="tonal"
          :href="systemSettingsUrl"
        >
          {{ $t('messages.open_system_settings', 'Open System Settings') }}
        </v-btn>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'

const { t, te } = useI18n({ useScope: 'global' })
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
  name: 'SiteSeo',
  layout: (layoutH, page) => layoutH(MainLayout, () => page),
})

defineProps({
  systemSettingsUrl: {
    type: String,
    default: null,
  },
})

const introText = computed(() =>
  te('messages.site_seo_intro')
    ? t('messages.site_seo_intro')
    : 'Page-level SEO is edited on each CMS page. Global robots.txt and other site-wide settings live in System Settings.'
)
</script>

<style scoped>
</style>
