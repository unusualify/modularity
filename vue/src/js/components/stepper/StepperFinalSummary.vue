<template>
  <v-sheet class="bg-primary-darken-2">
    <!-- <div class="flex-grow-1"> -->
      <!-- Title -->
      <ue-title justify="center" color="white" :text="$t('Total Amount').toUpperCase()" type="h5" />
      <v-divider/>

      <!-- Summary sections -->
      <template v-for="(sample, index) in formattedSummary" :key="`summary-section-${index}`">
        <!-- Section title -->
        <ue-title
          :text="sample.title"
          transform="capitalize"
          type="h6"
          color="white"
          padding="x-6"
          margin="t-6"
          class="mx-n6 py-3 bg-primary-darken-1"
        />

        <!-- Section values -->
        <v-table class="bg-transparent my-3">
          <tbody>
            <template v-for="(value, valueIndex) in sample.values" :key="`summary-value-${valueIndex}`">
              <tr class="py-0">
                <td class="border-0 h-auto py-1 pl-0 text-body-1 text-white">
                  {{ value.parentTitle || value.title || 'N/A' }}
                </td>
                <td class="border-0 h-auto py-1 text-right pr-0 font-weight-bold text-body-1 text-white">
                  {{ value.value || 'N/A' }}
                </td>
              </tr>
            </template>
          </tbody>
        </v-table>
      </template>

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
          {{ $t('Complete').toUpperCase() }}
        </v-btn-secondary>
      </v-sheet>
    <!-- </div> -->
</template>

<script>
export default {
  name: 'StepperFinalSummary',
  props: {
    formattedSummary: {
      type: Object,
      required: true,
      validator(value) {
        return Object.values(value).every(section => {
          return typeof section.title === 'string' &&
                 Array.isArray(section.values)
        })
      }
    },
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
