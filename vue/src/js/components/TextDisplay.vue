<template>
  <div class="d-flex align-baseline">
    <span class="font-weight-bold">{{ text }}</span>
    <span v-if="subText" class="text-medium-emphasis">{{ subText }}</span>
  </div>
</template>

<script>
export default {
  name: 'ue-text-display',
  props: {
    text: {
      type: String,
      required: true
    },
    subText: {
      type: String,
      required: false
    }
  }
}
</script>
