<template>
  <v-sheet class="bg-primary-darken-2">
    <!-- Title -->
    <ue-title justify="center" color="white" :text="$t('Total Amount').toUpperCase()" type="h5" />
    <v-divider style="border-color: rgb(var(--v-theme-on-primary-darken-1));" />

    <!-- Summary sections -->
    <slot name="body">

    </slot>

  </v-sheet>

  <v-spacer></v-spacer>
  <v-divider></v-divider>

  <!-- Total -->
  <v-table class="bg-transparent my-3">
    <tbody>
      <tr class="py-0">
        <td class="border-0 h-auto py-1 pl-0 text-h5 text-white">
          {{ $t('Total').toUpperCase() }}
        </td>
        <td class="border-0 h-auto py-1 d-flex justify-end pr-0 font-weight-bold">
          <slot name="total">
            <ue-text-display class="text-h5 text-white" text="$2500" subText="+ VAT" />
          </slot>
        </td>
      </tr>
    </tbody>
  </v-table>

  <!-- Description -->
  <div class="text-caption text-grey-lighten-1 mb-6">
    <slot name="description">
      At
    </slot>
  </div>

  <!-- Complete button -->
  <v-sheet class="pb-0 px-0 ue-stepper-form__preview-bottom bg-primary-darken-3">
    <v-btn-secondary
      class="v-stepper-form__nextButton"
      block
      :disabled="loading || isCompleted"
      :loading="loading"
      @click="$emit('complete')"
    >
      {{ $t('Complete Request').toUpperCase() }}
    </v-btn-secondary>
  </v-sheet>
    <!-- </div> -->
</template>

<script>
export default {
  name: 'StepperFinalSummary',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    isCompleted: {
      type: Boolean,
      default: false
    }
  },
  emits: ['complete']
}
</script>

<style scoped>
.v-table {
  background-color: transparent !important;
}

.v-table td {
  color: inherit !important;
}

/* .ue-stepper-form__preview-bottom {
  margin: 0 -24px -24px;
  padding: 24px;
} */

/* Dark theme overrides */
:deep(.v-btn) {
  color: white;
}

:deep(.v-divider) {
  border-color: rgba(255, 255, 255, 0.12);
}
</style>
