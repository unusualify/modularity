<template>
  <v-sheet-rounded class="ue-stepper-form__body d-flex w-100 flex-column justify-space-between elevation-2">
    <v-stepper-window id="ue-stepper-content-window" class="fill-height overflow-y-auto px-4" :style="`max-height: calc(${maxHeight} - ${coverHeight}px)`">
      <!-- Form steps -->
      <v-stepper-window-item
        v-for="(form, i) in forms"
        :key="`content-${i}`"
        :value="i+1"
      >
        <ue-form
          v-if="activeStep > i"
          :ref="formRefs[i]"
          :id="`stepper-form-${i+1}`"
          :isEditing="isEditing"

          v-model="models[i]"
          @update:modelValue="val => updateFormModel(val, i)"
          :XmodelValue="inputModels[i]"
          @Xupdate:modelValue="updateFormModel($event, i)"

          :schema="localSchemas[i]"
          @update:schema="updateFormSchema($event, i)"
          :Xschema="inputSchemas[i]"
          @Xupdate:schema="updateFormSchema($event, i)"

          @input_="$log('input emitted from form on steppercontent', $event)"
          @input="$emit('form-input', {event: $event, index: i})"

          @update:valid="$emit('form-valid', {event: $event, index: i})"

          noDefaultFormPadding
        />
      </v-stepper-window-item>

      <!-- Preview step -->
      <v-stepper-window-item
        :value="forms.length + 1"
        class="pt-3"
      >
        <slot name="preview"></slot>
      </v-stepper-window-item>
    </v-stepper-window>
  </v-sheet-rounded>
</template>

<script>
  import { cloneDeep, isEqual } from 'lodash-es'

  export default {
    name: 'StepperContent',
    props: {
      modelValue: {
        type: Array,
        required: true
      },
      forms: {
        type: Array,
        required: true
      },
      schemas: {
        type: Array,
        required: true,
        default: () => [] // Added default value
      },
      activeStep: {
        type: Number,
        required: true
      },
      formRefs: {
        type: Array,
        required: true
      },
      isEditing: {
        type: Boolean,
        default: false
      },
      maxHeight: {
        type: String,
        default: '80vh'
      },
      coverHeight: {
        type: Number,
        default: 0
      }

    },
    emits: ['form-input', 'form-valid', 'update:modelValue', 'update:schemas'],

    data () {
      return {
        models: cloneDeep(this.modelValue),
        localSchemas: cloneDeep(this.schemas)
      }
    },
    computed: {
      inputModels: {
        get () {
          return this.modelValue
        },
        set (newVal, oldVal) {
          // this.$emit('update:modelValue', newVal)
        }
      },
      inputSchemas: {
        get () {
          return this.schemas
        },
        set (newVal, oldVal) {
          if (!isEqual(newVal, oldVal)) {
            // this.$emit('update:schemas', newVal)
          }
        }
      }
    },
    methods: {
      updateFormModel (newVal, index) {
        this.models.splice(index, 1, newVal)
        // this.$emit('update:modelValue', [...this.models])

        // let updatedModels = [...this.inputModels]
        // updatedModels[index] = newVal
        // this.inputModels = updatedModels
        // this.$emit('update:modelValue', prevInputModel)
      },
      updateFormSchema(newSchema, index) {
        this.localSchemas.splice(index, 1, cloneDeep(newSchema))
        // this.$emit('update:schemas', [...this.localSchemas])

        // const updatedSchemas = [...this.inputSchemas]
        // updatedSchemas[index] = newSchema
        // this.inputSchemas = updatedSchemas
        // this.$emit('update:schemas', updatedSchemas)
      }
    },
    watch: {
      // Add watchers to ensure prop changes are reflected
      modelValue: {
        handler(newVal) {
          if (!isEqual(newVal, this.models)) {
            this.models = cloneDeep(newVal)
          }
        },
        deep: true
      },
      schemas: {
        handler(newVal) {
          if (!isEqual(newVal, this.localSchemas)) {
            this.localSchemas = cloneDeep(newVal)
          }
        },
        deep: true
      },

      // Watch local changes and emit updates
      models: {
        handler(newVal) {
          if (!isEqual(newVal, this.modelValue)) {
            this.$emit('update:modelValue', cloneDeep(newVal))
          }
        },
        deep: true
      },
      localSchemas: {
        handler(newVal) {
          if (!isEqual(newVal, this.schemas)) {
            this.$emit('update:schemas', cloneDeep(newVal))
          }
        },
        deep: true
      }
    }
  }
</script>
