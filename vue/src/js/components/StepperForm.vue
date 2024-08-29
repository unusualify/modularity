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
          ></v-divider>
        </template>
        <v-stepper-item
            :complete="activeStep > forms.length"
            title="Preview & Summary"
            :value="forms.length+1"
            color="info"
            :step="`Step Summary`"
            complete-icon="$complete"
            class="v-stepper-item__icon--border25"
            >
            <template v-slot:title="titleScope">
              <span :class="[ (titleScope.hasCompleted || titleScope.step == activeStep) ? 'text-info font-weight-bold' : '' ]">{{ titleScope.title }}</span>
            </template>
        </v-stepper-item>
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
              <v-stepper-window-item
                :value="forms.length +1"
              >
                <slot name="summary">
                  <v-sheet class="px-theme">
                    <ue-title no-bold default-classes="" class="pt-0 py-0 text-h8">{{ $t('Preview & Summary').toUpperCase() }}</ue-title>


                  </v-sheet>
                </slot>
                <!-- :has-submit="true" -->
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
                  {{ $t('fields.save').toUpperCase() }}
                </v-btn-secondary> -->
              </template>
            </v-stepper-actions>
          </v-sheet>
        </v-col>
        <v-col cols="4">
          <slot name="preview">
            <v-sheet-rounded class="d-flex flex-column fill-height"
              :style="[isLastStep ? 'background-color: #005868;' : '']"
              :class="[isLastStep ? 'pa-theme' : '']"
              >
              <template v-if="!isLastStep">
                <slot name="preview.forms">
                  <v-sheet class="v-stepper-form-preview fill-height">
                      <template
                        v-for="(form, i) in forms"
                        :key="`stepper-preview-item-${i}`"
                        >
                        <slot
                          :name="`preview-form-${i+1}`"
                          v-bind="{
                            index: i,
                            order: i+1,
                            title: previewTitles[i],
                            isPreviewModelFilled: isPreviewModelFilled,
                            model: models[i],
                            schema: schemas[i],
                            previewModel: previewModel[i] ?? {},
                            // previewModels: previewModel,

                          }"
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
                        </slot>
                      </template>
                  </v-sheet>
                  <v-spacer></v-spacer>
                  <v-sheet class="v-stepper-form-preview__bottom">
                    <v-btn-cta class="v-stepper-form__nextButton"
                      @click="nextForm(activeStep-1)"
                      >
                      {{ $t('next').toUpperCase() }}
                    </v-btn-cta>
                  </v-sheet>
                </slot>
              </template>
              <template v-else>
                <slot name="preview.summary">
                  <v-theme-provider theme="dark" with-background="" style="background-color: #005868;">
                    <ue-title default-classes="" class="pt-0 text-h8 ue-title text-center">{{ $t('Total Amount').toUpperCase() }}</ue-title>
                    <v-divider/>
                    {{ $log(this.models, this.schemas, this.forms) }}
                    <ue-title default-classes="" class="pl-0 pb-0 text-h8 ue-title">{{ $t('modules.package', 1) }}</ue-title>
                    <v-table class="bg-transparent ">
                      <tbody>
                        <tr class="py-0">
                          <td class="border-0 h-auto py-1 pl-0">United States:</td>
                          <td class="border-0 h-auto py-1 text-right pr-0 font-weight-bold">$100</td>
                        </tr>
                        <tr class>
                          <td class="border-0 h-auto py-1 pl-0">Turkey:</td>
                          <td class="border-0 h-auto py-1 text-right pr-0 font-weight-bold">$50</td>
                        </tr>
                        <tr class>
                          <td class="border-0 h-auto py-1 pl-0">France:</td>
                          <td class="border-0 h-auto py-1 text-right pr-0 font-weight-bold">$70</td>
                        </tr>
                        <!-- <tr
                          v-for="item in desserts"
                          :key="item.name"
                        >
                          <td>{{ item.name }}</td>
                          <td>{{ item.calories }}</td>
                        </tr> -->
                      </tbody>
                    </v-table>
                  </v-theme-provider>
                  <v-spacer></v-spacer>
                  <v-sheet class="pb-0 px-0 v-stepper-form-preview__bottom" style="background-color: #005868;">
                    <v-btn-cta class="v-stepper-form__nextButton" block
                      @click="completeForm"
                      >
                      {{ $t('Complete').toUpperCase() }}
                    </v-btn-cta>
                  </v-sheet>
                  <!-- <v-sheet class="v-stepper-form-preview fill-height rounded-0" style="background-color: #005868;">
                  </v-sheet> -->
                </slot>
              </template>
            </v-sheet-rounded>
          </slot>
        </v-col>

      </v-row>


    </template>
  </v-stepper>
</template>

<script>
import { toRefs, reactive, ref, computed } from 'vue';
import { map, reduce } from 'lodash-es';

import { getModel, handleEvents, handleMultiFormEvents } from '@/utils/getFormData.js'
import { useInputHandlers, useValidation } from '@/hooks'

import api from '@/store/api/form'

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
    actionUrl: {
      type: String,
    },
    modelValue: {
      type: Object,
      default: () => {
        return {
        }
      }
    },
    currentStep: {
      type: Number,
      default: 1
    }
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
      activeStep: this.currentStep,
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
    completeForm (){
      const formData = reduce(this.models, function(acc, model, index){
        return {...acc, ...model}
      }, {})

      // __log(formData)
      const method = formData?.id ? 'put' :'post'

      api[method](this.actionUrl, formData, function (response) {
        // self.formLoading = false
        __log(response.data)
        // redirector(response.data)

        // if (callback && typeof callback === 'function') callback(response.data)
      }, function (response) {
        // self.formLoading = false
        __log(response)
        // if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
      })
      return
      // const formData = getSubmitFormData(this.rawSchema, this.model, this.$store._state.data)
      // const method = Object.prototype.hasOwnProperty.call(formData, 'id') ? 'put' : 'post'
      const self = this

      api[method](this.actionUrl, formData, function (response) {
        self.formLoading = false
        if (Object.prototype.hasOwnProperty.call(response.data, 'errors')) {
          self.$store.commit(FORM.SET_SERVER_VALID, false)

          self.formErrors = response.data.errors
        } else if (Object.prototype.hasOwnProperty.call(response.data, 'variant')) {
          self.$store.commit(FORM.SET_SERVER_VALID, false)
          self.$store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
        }

        redirector(response.data)

        if (callback && typeof callback === 'function') callback(response.data)
      }, function (response) {
        self.formLoading = false
        if (Object.prototype.hasOwnProperty.call(response.data, 'exception')) {
          self.$store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be processed.', variant: 'error' })
        } else {
          self.$store.dispatch(ACTIONS.HANDLE_ERRORS, response.response.data)
          self.$store.commit(ALERT.SET_ALERT, { message: 'Your submission could not be validated, please fix and retry', variant: 'error' })
        }

        if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
      })
    },
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
      }else if(index < this.forms.length) {
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
    },
    isLastStep(){
      return this.activeStep > this.forms.length
    }
  },
  watch: {
    schemas: {
      handler (value, oldValue) {
        // __log(value[0].wrap_location, oldValue[0].wrap_location)
        // __log('schemas watch', value, oldValue)
      },
      deep: true
    },
    models: {
      handler (value, oldValue) {
        // __log(value[0].wrap_location, oldValue[0].wrap_location)
        // __log('models watcher')
        this.models.forEach((model, index) => {
          if(!!this.previewModel[index]){
            Object.keys(this.previewModel[index]).forEach((key) => {
              if(!this.models[index][key]){
                delete this.previewModel[index][key]
              }
            })
          }
        })
      },
      deep: true
    }
  },
  created() {
    let self = this
    this.forms.forEach((form, index) => {
      let schema = form.schema

      let model = getModel(schema, this.modelValue)

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
    // height: 100% !important

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
