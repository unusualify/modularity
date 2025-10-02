<template>
  <div class="generic-index">
    <ue-table
      v-bind="tableAttributes"
    />
  </div>
</template>

<script setup>
import { onMounted, h } from 'vue'
import { usePage } from '@inertiajs/vue3'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'

// Define props
const props = defineProps({
  tableAttributes: {
    type: Object,
    default: () => ({}),
  },
})

const page = usePage()
onMounted(() => {
  // console.info('Generic Index component mounted for:', page.props)
})

defineOptions({
  layout: (h, page) => h(MainLayout, () => page),
  name: 'GenericIndex',
})
</script>

<style scoped>
.generic-index {
  /* Generic index styles */
}
</style>
