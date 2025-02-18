<template>
  <v-sheet>
    <ue-title weight="medium" color="black" padding="a-0">
      {{ $t('Preview & Summary').toUpperCase() }}
    </ue-title>

    <v-row>
      <!-- Preview cards -->
      <template v-for="(context, index) in formattedPreview" :key="`summary-${index}`">
        <v-col cols="12" :md="context.col || 6" v-fit-grid>
          <ue-configurable-card v-bind="context" elevation="2" class="my-2"/>
        </v-col>
      </template>

      <!-- Final form data -->
      <v-col cols="12" v-if="previewFormData.length > 0">
        <v-sheet class="px-4">
          <ue-title
            type="body-1"
            color="primary"
            font-weight="bold"
            padding="a-0"
            margin="y-4"
          >
            {{ finalFormTitle }}
          </ue-title>
          <!-- Preview form items -->
          <template
            v-for="(data, index) in previewFormData"
            :key="`final-form-data-${index}`"
          >
            <ue-configurable-card
              style="background-color: transparent;"
              :class="[
                lastStepModel[data.fieldName].includes(data.id) ? 'bg-primary' : 'bg-grey-lighten-5'
              ]"
              class="mx-n4 mb-4 py-4"
              elevation="2"
              :items="[
                [
                  data.name || 'N/A ',
                  data.description || 'N/A',
                ],
                data.basePrice_show || 'N/A'
              ]"
              :actions="[
                {
                  onClick: () => {}
                }
              ]"
              hide-separator
              align-center-columns
              justify-center-columns
              :column-styles="{
                1: 'flex-basis: 50%;',
                2: 'flex-grow: 1; display: flex; justify-content: center; align-items: center;',
                3: 'flex-grow: 1; display: flex; align-items: end;',
              }"
            >
              <!-- Name and description -->
              <template #[`segment.1`]="segmentScope">
                <div class="text-body-2 font-weight-medium mb-1">
                  {{ segmentScope.data[0] }}
                </div>
                <div class="text-caption">
                  {{ segmentScope.data[1] }}
                </div>
              </template>

              <!-- Price -->
              <template #[`segment.2`]="segmentScope">
                <div class="text-body-2 font-weight-medium py-auto">
                  {{ segmentScope.data }}
                </div>
              </template>

              <!-- Actions -->
              <template #[`segment.actions`]="segmentScope">
                <div class="d-flex fill-height justify-space-evenly align-content-md-center">
                  <v-divider v-if="$vuetify.display.lgAndUp ? true : false" vertical></v-divider>
                  <v-btn
                    class="mx-1 rounded-circle"
                    :min-width="segmentScope.actionProps.actionIconMinHeight"
                    :min-height="segmentScope.actionProps.actionIconMinHeight"
                    :size="segmentScope.actionProps.actionIconSize"
                    :icon="lastStepModel[data.fieldName].includes(data.id) ? 'mdi-minus' : 'mdi-plus'"
                    :color="lastStepModel[data.fieldName].includes(data.id) ? 'grey' : 'primary'"
                    @click="$emit('final-form-action', index)"
                  />
                </div>
              </template>
            </ue-configurable-card>
          </template>
        </v-sheet>
      </v-col>
    </v-row>
  </v-sheet>
</template>

<script>
export default {
  name: 'StepperPreview',
  props: {
    formattedPreview: {
      type: Array,
      required: true
    },
    previewFormData: {
      type: Array,
      required: true
    },
    lastStepModel: {
      type: Object,
      required: true
    },
    finalFormTitle: {
      type: String,
      required: true
    }
  },
  emits: ['final-form-action']
}
</script>

<style scoped>
.ue-configurable-card {
  transition: background-color 0.2s ease;
}
</style>
