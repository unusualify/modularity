<template>
  <v-sheet-rounded
    class="d-flex flex-column fill-height pa-6 elevation-2"
    :style="[isLastStep ? '' : '']"
    :class="[isLastStep ? 'pa-6 bg-primary-darken-2' : '']"
  >
    <template v-if="!isLastStep">
      <slot name="summary.forms">
        <v-sheet class="ue-stepper-form__preview fill-height">
          <!-- Form summaries -->
          <template v-for="(form, i) in forms" :key="`stepper-summary-item-${i}`">
            <slot
              :name="`summary-form-${i+1}`"
              v-bind="{
                index: i,
                order: i+1,
                title: previewTitles[i],
                isPreviewModelFilled,
                model: models[i],
                schema: schemas[i],
                previewModel: previewModel[i] ?? {},
                length: forms.length
              }"
            >
              <v-divider v-if="isPreviewModelFilled(i) && i !== 0 && i < forms.length - 1" class="mb-6"/>
              <!-- Default summary content -->
              <ue-form-summary-item
                v-if="isPreviewModelFilled(i)"
                :index="i"
                :title="previewTitles[i]"
                :model="previewModel[i]"
              />
            </slot>
          </template>
        </v-sheet>

        <v-spacer></v-spacer>

        <!-- Next button -->
        <v-sheet class="ue-stepper-form__preview-bottom">
          <v-btn-secondary
            class="v-stepper-form__nextButton"
            density="comfortable"
            :disabled="$hasRequestInProgress()"
            @click="$emit('next-form', activeStep-1)"
          >
            {{ $t('next').toUpperCase() }}
          </v-btn-secondary>
        </v-sheet>
      </slot>
    </template>
    <template v-else>
      <slot
        name="summary.final"
        v-bind="{
          model: models,
          schema: schemas,
          previewModel: previewModel,
          onComplete: () => $emit('complete-form')
        }"
      ></slot>
    </template>
  </v-sheet-rounded>
</template>

<script>
export default {
  name: 'StepperSummary',
  props: {
    isLastStep: Boolean,
    forms: Array,
    activeStep: Number,
    models: Array,
    schemas: Array,
    previewModel: Array,
    previewTitles: Array,
    isPreviewModelFilled: Function
  },
  emits: ['next-form', 'complete-form']
}
</script>
