<script setup>
import { h } from 'vue'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'

const { elements } = defineProps({
  elements: {
    type: Object,
    default: () => ({})
  }
})

defineOptions({
  layout: (h, page) => h(MainLayout, () => page),
  name: 'Profile'
})
</script>

<template>
  <div class="profile pa-3 d-flex flex-column flex-grow-1 min-height-0">
    <ue-recursive-stuff v-for="(context, i) in elements"
      :key="`profile-element-${i}`"
      :configuration="context"
    />
  </div>
</template>

<style scoped>
.profile {
  flex: 1 1 auto;
  min-height: 0;
}
</style>
