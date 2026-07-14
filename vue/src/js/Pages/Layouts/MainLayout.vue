<script setup>
import { computed, ref, onMounted, provide } from 'vue'
import { usePage, Head } from '@inertiajs/vue3'

const page = usePage()

const loading = ref(true)

const showBusyOverlay = ref(false)
const showSpinner = () => { showBusyOverlay.value = true }
const hideSpinner = () => { showBusyOverlay.value = false }

provide('pageLoadingOverlay', { show: showSpinner, hide: hideSpinner })

const headData = computed(() => {
  return page.props.headLayoutData
})

const mainConfiguration = computed(() => {
  const defaultConfig = {
    headerTitle: 'Modularity',
    hideDefaultSidebar: false,
    fixedAppBar: false,
    appBarOrder: 0,
    navigation: {
      profileMenu: [],
      breadcrumbs: [],
      sidebar: [],
    },
  }

  return {
    ...defaultConfig,
    ...page.props.mainConfiguration,
  }
})

onMounted(() => {
  setTimeout(() => {
    loading.value = false
  }, 700)
})

defineOptions({
  name: 'MainLayout',
})

</script>

<template>
  <div id="admin">
    <ue-main
      ref="main"
      v-bind="mainConfiguration"
    >
      <Head v-if="headData && headData.pageTitle" :title="headData.pageTitle"/>
      <div id="ue-main-body" class="ue--main-container pa-3 h-100">
        <slot />

        <div id="ue-bottom-content">
          <!-- Media library components can be added here if needed -->
        </div>
      </div>

      <!-- Additional slots -->
      <template #slots>
        <slot name="slots" />
      </template>

      <template #top>
        <component v-if="$componentExists('UeCustomMainTopSlot')" is="UeCustomMainTopSlot" />
      </template>

      <template #bottom>
        <component v-if="$componentExists('UeCustomMainBottomSlot')" is="UeCustomMainBottomSlot" />
      </template>
    </ue-main>

    <div class="ue-loading-spinner" id="loading-spinner" v-show="loading">
      <div class="ue-spinner"></div>
    </div>

    <v-overlay
      :model-value="showBusyOverlay"
      class="align-center justify-center"
      persistent
      scrim="rgba(255, 255, 255, 0.97)"
      :z-index="9998"
    >
      <v-progress-circular
        color="primary"
        indeterminate
        size="56"
        width="5"
      />
    </v-overlay>
  </div>
</template>

<style scoped>
  .ue-loading-spinner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 1);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    animation: opacity 1s ease-in-out;
  }

  @keyframes opacity {
    0% { opacity: 1; }
    100% { opacity: 0; }
  }

  .ue-loading-spinner .ue-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(var(--v-theme-primary), 1);
    border-top: 4px solid #fff;
    border-radius: 50%;
    animation: spin 0.3s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>
