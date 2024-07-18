<template>
  <v-stepper v-model="activeStep" color="info" :class="['v-stepper--no-background', 'fill-height  d-flex flex-column']">
    <template v-slot:default="{ prev, next }">

      <v-stepper-header class="">
        <template v-for="(form, i) in forms" :key="`stepper-item-${i}`">
          <v-stepper-item
            :complete="activeStep > i+1"
            :title="form.title"
            :value="i+1"
            color="info"
            :step="`Step {{ n }}`"
            complete-icon="$complete"
            class="v-stepper-item__icon--border25"
            >
            <template v-slot:title="titleScope">
              <span :class="[ (titleScope.hasCompleted || titleScope.step == activeStep) ? 'text-info font-weight-bold' : '' ]">{{ titleScope.title }}</span>
            </template>
          </v-stepper-item>

          <v-divider
            v-if="i+1 !== forms.length"
            :key="i+1"
          ></v-divider>
        </template>
      </v-stepper-header>

      <v-row class="mt-theme-semi flex-fill">
        <v-col cols="8" v-fit-grid>
          <v-sheet class="v-stepper-window--left d-flex flex-column justify-space-between">
            <v-stepper-window class="mt-5">
              <v-stepper-window-item
                v-for="(form, i) in forms"
                :key="`content-${i}`"
                :value="i+1"
              >
                <!-- :has-submit="true" -->
                <ue-form
                  v-if="activeStep > i"
                  :ref="formRefs[i]"
                  :id="`stepper-form-${i+1}`"
                  v-model="models[i]"
                  v-model:schema="schemas[i]"
                  @input="handleInput($event, i)"
                  @update:valid="updateFormValid($event, i)"
                  />
                <!-- <v-custom-form-base
                  v-if="activeStep > i"
                  :id="`stepper-form-${i+1}`"
                  class="px-theme"
                  v-model="models[i]"
                  v-model:schema="schemas[i]"

                  @input="handleInput($event, i)"
                /> -->
              </v-stepper-window-item>
            </v-stepper-window>

            <v-stepper-actions
              ref="stepperActionRef"
              :disabled="disabled"
              @click:next="next"
              @click:prev="prev"
            >
              <template v-slot:next="nextScope">

                <!-- :disabled="!valids[activeStep-1]" -->
                <!-- <v-btn-secondary

                  @click="goNextForm(nextScope.props.onClick, activeStep-1)"
                  >
                  {{ $t('save').toUpperCase() }}
                </v-btn-secondary> -->
              </template>
            </v-stepper-actions>
          </v-sheet>
        </v-col>
        <v-col cols="4" v-fit-grid class="d-flex flex-column justify-between">
          <v-sheet class="pa-0 v-stepper-form-preview">

            <template
              v-for="(form, i) in forms"
              :key="`stepper-preview-item-${i}`"
              >
              <v-divider v-if="isPreviewModelFilled(i) && i !== 0" />
              <v-card variant="text" class v-if="isPreviewModelFilled(i)">
                <template v-slot:title>
                  <div class="d-inline-flex align-center text-body-2" style="line-height: 1;">
                    <v-avatar variant="flat" color="primary" class="v-avatar--border25" style="width: 24px; height: 24px; margin-inline-end: 8px;">{{ i + 1 }}</v-avatar>
                    <span class="font-weight-bold text-info">Step {{ i + 1 }}</span>
                  </div>
                  <div class="text-body-1 font-weight-bold">
                    {{ previewTitles[i] }}
                  </div>
                </template>

                <template v-slot:text>
                  <template v-for="(val, inputName) in previewModel[i]" :key="`stepper-preview-item-subtitle-${inputName}`">
                    <template v-for="(context, key) in previewModel[i][inputName]" :key="`stepper-preview-item-subtitle-${inputName}-${i}`">
                      <template v-if="Array.isArray(context)">
                        <div class="">
                          <span class='text-decoration-underline text-info font-weight-bold' style="margin-inline-end: 8px;">{{ window.__isObject(previewModel[i][inputName]) ? key : context[0] }}:</span>
                          <span v-for="(text) in ( window.__isObject(previewModel[i][inputName]) ? context : context.slice(1))" :key="`stepper-preview-item-subtitle-${inputName}-${i}-${inputName}-${key}`" style="margin-inline-end: 8px;">
                            {{ text }}
                          </span>
                          <!-- <span> {{ context[1] }}</span> -->
                          <!-- <div v-for="text in element">{{ element[1] }}</div> -->
                        </div>
                      </template>
                      <v-chip v-else variant="outlined" style="margin-inline-end: 8px;">{{context}}</v-chip>
                    </template>
                    <!-- {{ $log(key, previewModel[i][key]) }} -->
                  </template>
                  <!-- <div class="text-body-1" for>

                  </div> -->
                </template>
              </v-card>

            </template>

          </v-sheet>

          <v-sheet class="v-stepper-form-preview__bottom">
            <v-btn-cta class="v-stepper-form__nextButton"
              @click="nextForm(activeStep-1)"
              >
              {{ $t('next').toUpperCase() }}
            </v-btn-cta>
          </v-sheet>
        </v-col>
      </v-row>


    </template>
  </v-stepper>
</template>

<script>
import { toRefs, reactive, ref, computed } from 'vue';
import { map, snakeCase } from 'lodash-es';

import { getModel, handleEvents, handleMultiFormEvents } from '@/utils/getFormData.js'
import { useInputHandlers, useValidation } from '@/hooks'


export default {
  props: {
    forms: {
      type: Object,
      default () {
        return []
        // example
        // [
        //   title: {stepper_item_title},,
        //   schema: v-custom-form-base.schema
        // ]
      }
    },
  },
  setup (props, context) {
    const inputHandlers = useInputHandlers()
    const validations = useValidation(props)

    const stepperActionRef = ref(null)

    const state = reactive({
      stepperActionRef,
    })

    const formRefs = computed(() => map(props.forms, (m,i) => ref(null) ))

    return {
      ...inputHandlers,
      ...validations,
      ...toRefs(state),
      formRefs,
    }
  },
  data () {
    return {
      activeStep: 1,
      // models: this.schema.map(() => {return {}})

      schemas: [],
      models: [],
      valids: [],

      previewModel: [
        // {
        //   locations: ['United States', 'France', 'Turkey'],
        // },
        // {
        //   packages: [
        //     ['United States', 'Wire', '(English, German, Turkish)'],
        //     ['France', 'Premium', '(English, French)']
        //   ],
        // },
      ],

      // schemas: this.forms.map((form) => form.schema),
      // models: this.schemas.map((schema) => getModel())
    }
  },
  methods: {
    isPreviewModelFilled(index){
      const model = this.previewModel[index] ?? {}
      let isFilled = false
      for(const name in model){
        if(!!model){
          isFilled = true
          break
        }
      }

      return isFilled
    },
    handleInput (v, index) {
      const { on, key, obj } = v
      if (on === 'input' && !!key) {
        // if (!this.serverValid) {
        //   this.resetSchemaError(key)
        // }
        // __log(index, key, obj, v)
        // this.handleEvent(obj)
        handleMultiFormEvents(this.models, this.schemas, obj.schema, index, this.previewModel)

        // __log(
        //   'StepperForm previewData',
        //   this.previewModel,
        // )
      }
    },
    updateFormValid(val, index) {
      this.valids[index] = val
      // __log('valid changed', index, val, this.valids)
    },
    async goNextForm(callback, index) {
      // __log(this.formRefs[index].value[0].validModel)
      if(this.formRefs[index].value[0].validModel === true){
        callback()
      }else {
        await this.validateForm(index)
      }
      // callback()
    },
    async nextForm(index) {
      // __log(this.formRefs[index].value[0].validModel)
      // __log(this.stepperActionRef, this.$refs.stepperActionRef.$slots.next())
      if(this.formRefs[index].value[0].validModel === true){
        this.activeStep += 1
        // callback()
      }else {
        await this.validateForm(index)
      }
      // callback()
    },
    async validateForm(i) {
      const formRef = this.formRefs[i]

      formRef.value[0].manualValidation = true

      const result = await formRef.value[0].validate()

      formRef.value[0].manualValidation = false


      return result
    },
    // updateSchema (v, index) {
    //   // __log('updateSchema', v, index)
    //   this.schemas[index] = v
    // },
    // setSchema (schemas, index) {
    //   return schemas[index]
    // }
  },
  computed: {
    disabled () {
      return this.activeStep === 1 ? 'prev' : this.activeStep === this.forms.length ? 'next' : undefined
    },
    previewTitles() {
      return map(this.models, (model, index) => {
        let form = this.forms[index]
        let title = __isset(form['previewTitle']) ? form['previewTitle'] : form['title']
        let castedTitle = this.$castValueMatch(title, model )

        return castedTitle
      })
    }
  },
  watch: {
    schemas: {
      handler (value, oldValue, ...args) {

        // __log('schemas watch', value, oldValue, ...args)
      },
      deep: true
    }
  },
  created() {
    let self = this
    this.forms.forEach((form, index) => {
      let schema = form.schema

      let model = getModel(schema)

      self.models.push(model)
      self.schemas.push(self.invokeRuleGenerator(schema, model))
      self.valids.push(null)
    })
    // models: this.schemas.map((schema) => getModel())
  }
}
</script>

<style lang="sass">

  .v-stepper
    &.v-sheet.v-stepper--no-background
      background-color: transparent !important
      color: currentColor !important
      box-shadow: unset
      border-radius: 0

      .v-stepper-header, .v-stepper-window--left
        background: rgb(var(--v-theme-surface))
        box-shadow: 0px 6px 18px 0px rgba(0, 0, 0, 0.06)
        border-radius: 8px

      .v-stepper-window
        margin: 0

    .v-avatar--border25, .v-stepper-item__icon--border25 .v-avatar
      border-radius: 25%

  .v-stepper-form-preview
    .v-card-item, .v-card-text
      padding-left: $theme-space
      padding-right: $theme-space

  .v-stepper-form-preview__bottom
    display: flex !important
    flex-direction: row-reverse !important
    padding-left: $theme-space
    padding-right: $theme-space

  .v-stepper-form-preview__bottom, .v-card-item
    padding-top: $theme-space
  .v-stepper-form-preview__bottom, .v-card-text
    padding-bottom: $theme-space



</style>
