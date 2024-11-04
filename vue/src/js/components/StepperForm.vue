<template>
  <v-stepper v-model="activeStep" color="info" :class="['ue-stepper-form','ue-stepper--no-background', 'fill-height  d-flex flex-column']">
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
            class="ue-stepper-item__icon--border25"
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
            class="ue-stepper-item__icon--border25"
            >
            <template v-slot:title="titleScope">
              <span :class="[ (titleScope.hasCompleted || titleScope.step == activeStep) ? 'text-info font-weight-bold' : '' ]">{{ titleScope.title }}</span>
            </template>
        </v-stepper-item>
      </v-stepper-header>

      <v-row class="mt-4 flex-fill">
        <v-col cols="8" lg="8" v-fit-grid>
          <v-sheet class="ue-stepper-form__body d-flex flex-column justify-space-between">
            <v-stepper-window class="fill-height">
              <v-stepper-window-item
                v-for="(form, i) in forms"
                :key="`content-${i}`"
                :value="i+1"
              >
                <ue-form
                  v-if="activeStep > i"
                  :ref="formRefs[i]"
                  :id="`stepper-form-${i+1}`"
                  v-model="models[i]"
                  v-model:schema="schemas[i]"
                  @input="handleInput($event, i)"
                  @update:valid="updateFormValid($event, i)"
                />
              </v-stepper-window-item>
              <v-stepper-window-item
                :value="forms.length +1"
              >
                <slot name="preview">
                  <v-sheet class="px-6 py-4">
                    <ue-title weight="medium" color="black" padding="a-0">{{ $t('Preview & Summary').toUpperCase() }}</ue-title>
                    <v-row>
                      <template v-for="(context, index) in formattedPreview" :key="`summary-${index}`">
                        <v-col cols="12" :lg="context.col || 6" v-fit-grid>
                          <ConfigurableCard v-bind="context" elevation="3" class="my-2"/>
                        </v-col>
                      </template>

                      <v-col cols="12">
                        <!-- <ConfigurableCard class="my-3" elevation="3"
                          align-center-columns-x
                          justify-center-columns-x
                          v-bind="getPreviewConfigurableData()"
                        /> -->
                      </v-col>

                      <v-col cols="12">

                        <!-- <div class="text-h5 mb-4">ADD ONS</div> -->
                        <template v-for="(addon, index) in [
                            {
                              id: 1,
                              title: 'Lorem Ipsum Dolor',
                              description: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis At vero eos et accusamus etiu.',
                              price: 50
                            },
                            {
                              id: 2,
                              title: 'Lorem Ipsum Dolor',
                              description: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis At vero eos et accusamus etiu.',
                              price: 50
                            }
                          ]"
                          :key="addon.id"
                          :title="addon.title"
                        >
                          <!-- <ConfigurableCard
                            :items="[
                              [
                                addon.title,
                              ],
                              addon.price
                            ]"
                          /> -->
                          <v-card
                            v-if="false"
                            elevation="2"
                            class="addon-card pa-4"
                            :class="{ 'addon-card--selected': selected }"
                          >
                            <v-row align="center" no-gutters>
                              <v-col cols="8">
                                <div class="text-h6 mb-2">{{ addon.title }}</div>
                                <div class="text-body-2 text-grey-darken-1">{{ addon.description }}</div>
                              </v-col>

                              <v-col cols="2" class="text-h4 text-center">
                                ${{ addon.price }}
                              </v-col>

                              <v-col cols="2" class="d-flex justify-center">
                                <v-btn
                                  icon
                                  size="large"
                                  @click="$emit('toggle')"
                                >
                                <!-- :color="selected ? 'primary' : 'grey'"
                                :variant="selected ? 'flat' : 'outlined'" -->
                                  <v-icon>{{ selected ? 'mdi-minus' : 'mdi-plus' }}</v-icon>
                                </v-btn>
                              </v-col>
                            </v-row>
                          </v-card>
                        </template>

                      </v-col>
                    </v-row>
                  </v-sheet>
                </slot>
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
        <v-col cols="4" lg="4">
          <slot name="summary">
            <v-sheet-rounded class="d-flex flex-column fill-height pa-6"
              :style="[isLastStep ? 'background-color: #005868;' : '']"
              :class="[isLastStep ? 'pa-6' : '']"
              >
              <template v-if="!isLastStep">
                <slot name="summary.forms">
                  <v-sheet class="ue-stepper-form__preview fill-height">
                    <template
                      v-for="(form, i) in forms"
                      :key="`stepper-summary-item-${i}`"
                      >
                      <slot
                        :name="`summary-form-${i+1}`"
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
                        <v-divider v-if="isPreviewModelFilled(i) && i !== 0" class="mb-6"/>
                        <div v-if="isPreviewModelFilled(i)" class="mb-6">
                          <!-- Step title -->
                          <div class="d-inline-flex align-center text-body-2" style="line-height: 1;">
                            <v-avatar variant="flat" color="primary" class="ue-avatar--border25" style="width: 24px; height: 24px; margin-inline-end: 8px;">{{ i + 1 }}</v-avatar>
                            <span class="font-weight-bold text-info"> {{ $t('fields.step') }} {{ i + 1 }}</span>
                          </div>
                          <!-- Secondary title -->
                          <div class="text-body-1 font-weight-bold text-truncate mb-5">
                              {{ previewTitles[i] }}
                          </div>

                          <!-- Step body -->
                          <template v-for="(val, inputName) in previewModel[i]" :key="`stepper-preview-item-subtitle-${inputName}`">
                            <template v-for="(context, key) in previewModel[i][inputName]" :key="`stepper-preview-item-subtitle-${inputName}-${i}`">
                              <template v-if="Array.isArray(context)">
                                <div class="">
                                  <span class='text-info font-weight-bold' style="margin-inline-end: 8px;">{{ window.__isObject(previewModel[i][inputName]) ? key : context[0] }}:</span>
                                  <span v-for="(text) in ( window.__isObject(previewModel[i][inputName]) ? context : context.slice(1))" :key="`stepper-preview-item-subtitle-${inputName}-${i}-${inputName}-${key}`" style="margin-inline-end: 8px;">
                                    {{ text }}
                                  </span>
                                </div>
                              </template>
                              <v-btn v-else readonly active-color="black" color="black" variant="outlined" style="margin-inline-end: 8px;">{{context}}</v-btn>
                            </template>
                          </template>
                        </div>
                      </slot>
                    </template>
                  </v-sheet>


                  <v-spacer></v-spacer>

                  <v-sheet class="ue-stepper-form__preview-bottom">
                    <v-btn-cta class="v-stepper-form__nextButton"
                      :disabled="$hasRequestInProgress()"
                      @click="nextForm(activeStep-1)"
                      >
                      {{ $t('next').toUpperCase() }}
                    </v-btn-cta>
                  </v-sheet>
                </slot>
              </template>
              <template v-else>
                <slot name="summary.final"
                  v-bind="{
                    model: models,
                    schema: schemas,
                    previewModel: previewModel,
                    completeForm: completeForm,
                  }"
                >
                  <v-theme-provider theme="dark" with-background="" class="ue-stepper-form__summary-final">
                    <ue-title justify="center" color="white" :text="$t('Total Amount').toUpperCase()" type="h5" />
                    <v-divider/>

                    <template v-for="(sample, index) in formattedSummary">
                      <ue-title :text="sample.title" transform="capitalize" type="h6" color="white" padding="x-6" margin="t-6" class="mx-n6 py-3 ue-overlay" />
                      <v-table class="bg-transparent my-3">
                        <tbody>
                          <template v-for="(value, index) in sample.values">
                            <tr class="py-0">
                              <td class="border-0 h-auto py-1 pl-0 text-body-1">{{ value.parentTitle || value.title || 'N/A' }}</td>
                              <td class="border-0 h-auto py-1 text-right pr-0 font-weight-bold text-body-1">{{ value.value || 'N/A' }}</td>
                            </tr>
                          </template>
                        </tbody>
                      </v-table>
                    </template>
                  </v-theme-provider>

                  <v-spacer></v-spacer>

                  <v-theme-provider theme="dark" with-background="$vuetify.theme.dark" class="ue-stepper-form__summary-final">
                    <v-divider></v-divider>

                    <!-- Total -->
                    <v-table class="bg-transparent my-3">
                      <tbody>
                        <tr class="py-0">
                          <td class="border-0 h-auto py-1 pl-0 text-h5">{{ $t('Total').toUpperCase() }}</td>
                          <td class="border-0 h-auto py-1 d-flex justify-end pr-0 font-weight-bold">
                            <ue-text-display class="text-h5" :text="`$2500`" subText="+ VAT" />
                          </td>
                        </tr>
                      </tbody>
                    </v-table>

                    <!-- Description -->
                    <div class="text-caption text-grey mb-6">
                      At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium iusto odio
                    </div>
                  </v-theme-provider>


                  <v-sheet class="pb-0 px-0 ue-stepper-form__preview-bottom" style="background-color: #005868;">
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

import ConfigurableCard from '@/components/labs/ConfigurableCard.vue';

import NotationUtil from '@/utils/notation';

export default {
  components: {
    ConfigurableCard,
  },
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
    preview: {
      type: Array,
      default: []
    },
    currentStep: {
      type: Number,
      default: 1
    },
    cardsNotation: {
      type: String,
      default: 'models.1.pressReleasePackages'
    },
    summaryNotations: {
      type: Array,
      default: () => {
        return {
          // 'pressReleasePackages.package_id.price',
          'pressReleasePackages.package_id.price': {
            'title': 'Paketler',
          }

        }
      }
    },
    previewNotations: {
      type: [Array, Object],
      default: () => {
        return [
          {
            pattern: 'pressReleasePackages.*',
            mapArrayItems: false,
            outputFormat: 'object',  // This will preserve arrays in _value
            nested: true,
          },
          {
            col: 12,
            title: 'Press Release Content',
            nested: true,
            outputFormat: 'object',
            items: [
              {
                pattern: 'content.content-type',
                simpleValue: true  // Only affects this pattern
              },
              [
                'content.date',
                'content.time',
                'content.timezone'
              ]
            ]
          }
        ]
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
    getLocationPrice(location){
      const currencyPackages = location.currencyPackages;
      let packageId = this.models[1].pressReleasePackages[location.id]?.package_id
      packageId = this.$lodash.isString(packageId) ? parseInt(packageId) : packageId

      return this.$lodash.find(currencyPackages, ['id', packageId ])?.prices_show;
    },

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
    },
    summaryCardModels(){
      return __data_get(this, this.cardsNotation, [])
    },
    displayInfo(){
      let data = []
      for(const index in this.schemas){
        // __log(this.schemas[index], this.models[index])
        // data[index] = getDisplayData(this.schemas[index], this.models[index])
        data[index] = this.$getDisplayData(this.schemas[index], this.models[index])
        // __log(data)
        // if(index == 1){
        // }
      }

      return data
    },
    formattedSummary(){
      let formattedSummary = {}
      let data = this.displayInfo
      for(const notation in this.summaryNotations){
        const object = this.summaryNotations[notation];
        const values = NotationUtil.findMatchingNotations(data, notation);
        formattedSummary[notation] = Object.assign({}, object, { values });
      }
      // __log(formattedSummary)
      return formattedSummary
    },
    formattedPreview(){
      return NotationUtil.formattedPreview(this.displayInfo, this.previewNotations)
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
    NotationUtil.test()

    let self = this
    this.forms.forEach((form, index) => {
      let schema = form.schema

      let model = getModel(schema, this.modelValue)

      self.models.push(model)
      self.schemas.push(self.invokeRuleGenerator(schema, model))
      self.valids.push(null)
    })
    this.previewModel = this.preview
    // __log(this.models[0], this.schemas[0])
    // handleMultiFormEvents(this.models, this.schemas, obj.schema, index, this.previewModel)

    // models: this.schemas.map((schema) => getModel())
  }
}
</script>

<style lang="sass">

  .ue-stepper-form
    &.v-sheet.ue-stepper--no-background
      background-color: transparent !important
      color: currentColor !important
      box-shadow: unset
      border-radius: 0

      .v-stepper-header, .ue-stepper-form__body
        background: rgb(var(--v-theme-surface))
        box-shadow: 0px 6px 18px 0px rgba(0, 0, 0, 0.06)
        border-radius: 8px

      .v-stepper-window
        margin: 0

    .ue-avatar--border25, .ue-stepper-item__icon--border25 .v-avatar
      border-radius: 25%

    .ue-stepper-form__preview
      // height: 100% !important

    .v-card-item, .v-card-text
      // padding-left: 12 * $spacer
      // padding-right: 12 * $spacer

    .ue-stepper-form__summary-final
      background-color: $stepper-form-summary-final-background

    .ue-stepper-form__preview-bottom
      display: flex !important
      flex-direction: row-reverse !important
      // padding-left: 12 * $spacer
      // padding-right: 12 * $spacer

    .ue-stepper-form__preview-bottom
      // padding-top: 12 * $spacer
    .ue-stepper-form__preview-bottom
      // padding-bottom: 12 * $spacer



</style>
