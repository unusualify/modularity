<template>
  <div class="d-inline-block">
    <v-tooltip
      :model-value="showTooltip"
      text="Copied!"
      location="top"
      :open-on-hover="false"
      :open-on-focus="false"
    >
      <template v-slot:activator="{ props }">
        <v-icon
          v-bind="{
            ...props,
            'onClick': handleCopy
          }"
          :icon="icon"
          :color="color"
          :size="size"
          :class="{ 'cursor-pointer': true }"
        />
      </template>
    </v-tooltip>
  </div>
</template>

<script>

export default {
  name: 'CopyText',
  props: {
    text: {
      type: [String, Number],
      required: true
    },
    icon: {
      type: String,
      default: 'mdi-content-copy'
    },
    color: {
      type: String,
      default: 'primary'
    },
    size: {
      type: String,
      default: 'small'
    }
  },
  data() {
    return {
      showTooltip: false
    }
  },
  methods: {
    handleCopy() {
      this.$copy(this.text);
      this.showTooltip = true;

      setTimeout(() => {
        this.showTooltip = false;
      }, 2000);
    }
  }
}
</script>
