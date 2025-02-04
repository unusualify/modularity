<template>
  <div class="mb-6">
    <!-- Step title -->
    <div class="d-inline-flex align-center text-body-2" style="line-height: 1;">
      <v-avatar
        variant="flat"
        color="primary"
        class="ue-avatar--border25"
        style="width: 24px; height: 24px; margin-inline-end: 8px;"
      >
        {{ index + 1 }}
      </v-avatar>
      <span class="font-weight-bold text-primary">
        {{ $t('fields.step') }} {{ index + 1 }}
      </span>
    </div>

    <!-- Secondary title -->
    <div class="text-body-1 font-weight-bold text-truncate mb-5">
      {{ title }}
    </div>

    <!-- Step body -->
    <template
      v-for="(val, inputName) in model"
      :key="`stepper-preview-item-subtitle-${inputName}`"
    >
      <template
        v-for="(context, key) in model[inputName]"
        :key="`stepper-preview-item-subtitle-${inputName}-${index}`"
      >
        <!-- Array context -->
        <template v-if="Array.isArray(context)">
          <div class="">
            <span
              class='text-primary font-weight-bold'
              style="margin-inline-end: 8px;"
            >
              {{ window.__isObject(model[inputName]) ? key : context[0] }}:
            </span>
            <span
              v-for="text in (window.__isObject(model[inputName]) ? context : context.slice(1))"
              :key="`stepper-preview-item-subtitle-${inputName}-${index}-${inputName}-${key}`"
              style="margin-inline-end: 8px;"
            >
              {{ text }}
            </span>
          </div>
        </template>

        <!-- Non-array context -->
        <v-btn
          v-else
          readonly
          active-color="black"
          color="black"
          variant="outlined"
          style="margin-inline-end: 8px;"
        >
          {{ context }}
        </v-btn>
      </template>
    </template>
  </div>
</template>

<script>
export default {
  name: 'FormSummaryItem',
  props: {
    index: {
      type: Number,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    model: {
      type: Object,
      required: true
    }
  },
  created() {
    // __log('FormSummaryItem', this.model)
  }
}
</script>

<style scoped>
.ue-avatar--border25 {
  border-radius: 25%;
}
</style>
