<script setup>
import { computed, ref, onMounted } from 'vue'
import { usePage, Head } from '@inertiajs/vue3'

const page = usePage()

const loading = ref(true)

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
