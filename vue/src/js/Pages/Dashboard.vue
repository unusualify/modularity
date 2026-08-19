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
  <div class="dashboard pa-3 flex-grow-1 min-height-0 d-flex flex-column">
    <ue-blocks :items='blockItems' />
  </div>
</template>

<style lang="sass">
.dashboard
  flex: 1 1 auto
  min-height: 0
  display: flex
  flex-direction: column
</style>
