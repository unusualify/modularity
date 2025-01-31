<template>
  <v-sheet-rounded class="ue-stepper-form__body d-flex w-100 flex-column justify-space-between elevation-2">
    <v-stepper-window class="fill-height overflow-y-auto px-6" style="max-height: 80vh;">
      <!-- Form steps -->
      <v-stepper-window-item
        v-for="(form, i) in forms"
        :key="`content-${i}`"
        :value="i+1"
      >
        <ue-form-old
          v-if="activeStep > i"
          :ref="formRefs[i]"
          :id="`stepper-form-${i+1}`"

          :modelValue="inputModel[i]"
          @update:modelValue="updateInputModel($event, i)"

          :schema="inputSchemas[i]"
          @update:schema="updateInputSchemas($event, i)"

          @input="$emit('form-input', $event, i)"
          @update:valid="$emit('form-valid', $event, i)"

          :isEditing="isEditing"
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
      }
    },
    emits: ['form-input', 'form-valid', 'update:modelValue', 'update:schemas'],

    computed: {
      inputModel: {
        get () {
          return this.modelValue
        },
        set (value) {
          __log(value)
          this.$emit('update:modelValue', value)
        }
      },
      inputSchemas: {
        get () {
          // __log('inputSchemas get', this.schemas)
          return this.schemas
        },
        set (value) {
          // __log('inputSchemas setter', value)
          // this.$emit('update:schemas', value)
          if (!isEqual(value, this.schemas)) {
            this.$emit('update:schemas', value)
          }
        }
      }
    },
    methods: {
      updateInputModel (value, index) {
        let prevInputModel = cloneDeep(this.inputModel)
        let model = value
        prevInputModel[index] = model

        this.$emit('update:modelValue', prevInputModel)
      },
      updateSchema(newSchema, index) {
        const updatedSchemas = [...this.schemas]
        updatedSchemas[index] = newSchema

        this.$emit('update:schemas', updatedSchemas)
      }
    },
    watch: {
      // Add watchers to ensure prop changes are reflected
      modelValue: {
        handler(newVal) {
          if (newVal !== this.inputModel) {
            this.inputModel = newVal
          }
        },
        deep: true
      },
      schemas: {
        handler(newVal) {
          // __log('schemas watch', newVal, this.inputSchemas)
          if (newVal !== this.inputSchemas) {
            this.inputSchemas = newVal
          }
        },
        deep: true
      }
    }
  }
</script>
