<template>
  <div class="generic-form">
    <ue-form
      v-bind="formAttributes"
    />
  </div>
</template>

<script setup>
import { onMounted, h } from 'vue'
import { usePage } from '@inertiajs/vue3'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'

// Define props
const props = defineProps({
  formAttributes: {
    type: Object,
    default: () => ({}),
  }
})

const page = usePage()

onMounted(() => {
  // console.info('Generic Form component mounted for:', page.props)
  // console.info('Generic Form component mounted props for:', props)
})

defineOptions({
  layout: (h, page) => h(MainLayout, () => page),
  name: 'GenericForm',
})
</script>

<style scoped>
.generic-form {
  /* Generic form styles */
}
</style>
