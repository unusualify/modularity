<template>
  <v-sheet>
    <ue-title type="body-1" weight="bold" padding="a-0">
      {{ $t('Preview & Summary').toUpperCase() }}
    </ue-title>

    <v-row>
      <!-- Preview cards -->
      <template v-for="(context, index) in formattedPreview" :key="`summary-${index}`">
        <v-col cols="12" :md="context.col || 6" class="d-flex">
          <ue-configurable-card v-bind="context" elevation="2" title-color="primary" class="my-2 w-100 h-100"/>
        </v-col>
      </template>

      <!-- Final form data -->
      <v-col cols="12" v-if="previewFormData.length > 0" v-fit-grid>
        <v-sheet class="px-0">
          <div class="d-flex flex-column ga-2 my-4">
            <ue-title
              type="body-1"
              font-weight="bold"
              padding="a-0"
            >
              {{ finalFormTitle }}
            </ue-title>
            <ue-title
              v-if="finalFormSubtitle"
              type="caption"
              weight="medium"
              color="grey-darken-1"
              transform="none"
              padding="a-0"
            >
              {{ finalFormSubtitle }}
            </ue-title>
          </div>
          <!-- Preview form items -->
          <template
            v-for="(data, index) in previewFormData"
            :key="`final-form-data-${index}`"
          >
            <ue-configurable-card
              row-min-height="120px"
              style="background-color: transparent; min-height: 150px;"
              :class="[
                $lodash.includes(lastStepModel[data.fieldName], data.id) ? 'bg-primary' : 'bg-grey-lighten-5',
                'w-100'
              ]"
              class="mb-4 py-4"
              elevation="2"
              :items="data.form_card_items || [
                [
                  data.name || 'N/A ',
                  data.description || 'N/A',
                  data.tags || [],
                ],
                data.base_price_without_vat_formatted || 'N/A'
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
                '0': '',
                '1': '',
                '_actions': '',
              }"
              :column-classes="{
                '0': 'flex-1-1-100 flex-md-1-0',
                '1': 'flex-0-1 d-flex align-center',
                '_actions': 'flex-0-1',
              }"
            >
              <!-- Name, description, and tags -->
              <template #[`segment.1`]="segmentScope">
                <div class="text-body-2 font-weight-medium mb-1">
                  {{ segmentScope.data[0] }}
                </div>
                <div class="text-caption">
                  {{ segmentScope.data[1] }}
                </div>
                <div v-if="Array.isArray(segmentScope.data[2]) && segmentScope.data[2].length > 0" class="text-caption mt-4 d-flex ga-4 flex-wrap" >
                  <v-chip v-for="tag in segmentScope.data[2]" :key="tag" variant="outlined" size="small" class="">
                    {{ tag.name ?? tag.slug }}
                  </v-chip>
                </div>
              </template>

              <!-- Price -->
              <template #[`segment.2`]="segmentScope">
                <div class="text-h4 font-weight-medium py-auto">
                  {{ segmentScope.data }}
                </div>
              </template>

              <!-- Actions -->
              <template #[`segment.actions`]="segmentScope">
                <div class="d-flex fill-height align-content-md-center">
                  <!-- <v-divider v-if="$vuetify.display.lgAndUp ? true : false" vertical></v-divider> -->
                  <v-btn
                    class="mx-1 rounded-circle"
                    :min-width="segmentScope.actionProps.actionIconMinHeight"
                    :min-height="segmentScope.actionProps.actionIconMinHeight"
                    :size="segmentScope.actionProps.actionIconSize"
                    :icon="$lodash.includes(lastStepModel[data.fieldName], data.id) ? 'mdi-minus' : 'mdi-plus'"
                    :color="$lodash.includes(lastStepModel[data.fieldName], data.id) ? 'grey' : 'primary'"
                    @click="$emit('final-form-action', index)"

                    :readonly="$lodash.includes(protectedLastStepModel[data.fieldName], data.id)"
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
  emits: ['final-form-action'],
  props: {
    formattedPreview: {
      type: Array,
      default: () => []
    },
    previewFormData: {
      type: Array,
      default: () => []
    },
    lastStepModel: {
      type: Object,
      required: true
    },
    finalFormTitle: {
      type: String,
      required: true
    },
    finalFormSubtitle: {
      type: String,
      default: null
    },
    protectedLastStepModel: {
      type: Object,
      default: () => {}
    }
  },
}
</script>

<style scoped>
  .ue-configurable-card {
    transition: background-color 0.2s ease;
  }
</style>
