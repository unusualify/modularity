<script setup>
import { h } from 'vue'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'

const { blockItems } = defineProps({
  blockItems: {
    type: Object,
    default: () => ({})
  }
})

defineOptions({
  layout: (h, page) => h(MainLayout, () => page),
  name: 'Dashboard'
})
</script>

<template>
  <div class="ue-dashboard pa-3 h-100">
    <ue-blocks :items='blockItems' />
  </div>
</template>

<style lang="sass" scoped>
.ue-dashboard
  min-height: 0
</style>
