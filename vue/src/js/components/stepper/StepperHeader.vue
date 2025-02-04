<template>
  <v-stepper-header class="rounded elevation-2">
    <!-- Form steps -->
    <template v-for="(form, i) in forms" :key="`stepper-item-${i}`">
      <v-stepper-item
        :complete="activeStep > i+1"
        :title="form.title"
        :value="i+1"
        color="primary"
        :step="`Step {{ n }}`"
        complete-icon="$complete"
        class="ue-stepper-item__icon--border25"
      >
        <template v-slot:title="titleScope">
          <div @click="$emit('step-click', i+1)" style="cursor: pointer">
            <span :class="[ (titleScope.hasCompleted || titleScope.step == activeStep) ? 'text-primary font-weight-bold' : '' ]">
              {{ titleScope.title }}
            </span>
          </div>
        </template>
      </v-stepper-item>
    </template>

    <!-- Summary step -->
    <v-stepper-item
      :complete="activeStep > forms.length"
      title="Preview & Summary"
      :value="forms.length+1"
      color="primary"
      :step="`Step Summary`"
      complete-icon="$complete"
      class="ue-stepper-item__icon--border25"
    >
      <template v-slot:title="titleScope">
        <span :class="[ (titleScope.hasCompleted || titleScope.step == activeStep) ? 'text-primary font-weight-bold' : '' ]">
          {{ titleScope.title }}
        </span>
      </template>
    </v-stepper-item>
  </v-stepper-header>
</template>

<script>
export default {
  name: 'StepperHeader',
  props: {
    forms: {
      type: Array,
      required: true
    },
    activeStep: {
      type: Number,
      required: true
    }
  },
  emits: ['step-click']
}
</script>
