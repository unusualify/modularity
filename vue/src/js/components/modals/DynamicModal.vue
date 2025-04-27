<script setup>
  import { inject, computed, provide } from 'vue'

  const service = inject('modalService')
  if (!service) {
    throw new Error('[DynamicModal] modalService not found')
  }

  const state = service.state

  // remap emits so we can do <component v-on="listeners" />
  const listeners = computed(() => {
    const out = {}
    Object.entries(state.emits).forEach(([evt, fn]) => {
      out[evt] = payload => fn(payload)
    })
    return out
  })

  // expose a dialogRef for child to close itself or read incoming data
  provide('modalRef', {
    close: service.close.bind(service),
    data: state.data
  })
</script>

<template>
  <ue-modal
    v-model="state.visible"
    persistent
    widthType="md"
    v-bind="state.modalProps"
  >
    <template #body.description>
      <component
        v-if="state.component"
        :is="state.component"
        v-bind="state.props"
        v-on="listeners"
      />
    </template>
  </ue-modal>
</template>

