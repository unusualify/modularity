<template>
  <v-stepper v-model="activeStep" color="prima" :class="['ue-stepper-form','ue-stepper--no-background', 'fill-height  d-flex flex-column']">
    <template v-slot:default="{ prev, next }">
      <v-stepper-header class="rounded elevation-2">
        <template v-for="(form, i) in forms" :key="`stepper-item-${i}`">
          <v-stepper-item
            :complete="activeStep > i+1"
            :title="form.title"
            :value="i+1"
            color="primary"
            :step="`Step {{ n }}`"
            complete-icon="$complete"
            class="ue-stepper-item__icon--border25"
            >
            <template v-slot:title="titleScope">
              <div @click="goStep(i+1)" style="cursor: pointer">
                <span :class="[ (titleScope.hasCompleted || titleScope.step == activeStep) ? 'text-primary font-weight-bold' : '' ]">{{ titleScope.title }}</span>
              </div>
            </template>
          </v-stepper-item>
          <!-- <v-divider
            v-if="i+1 !== forms.length"
          ></v-divider> -->
        </template>
        <v-stepper-item
            :complete="activeStep > forms.length"
            title="Preview & Summary"
            :value="forms.length+1"
            color="primary"
            :step="`Step Summary`"
            complete-icon="$complete"
            class="ue-stepper-item__icon--border25"
            >
            <template v-slot:title="titleScope">
              <span :class="[ (titleScope.hasCompleted || titleScope.step == activeStep) ? 'text-primary font-weight-bold' : '' ]">{{ titleScope.title }}</span>
            </template>
        </v-stepper-item>
      </v-stepper-header>

      <v-row class="mt-4 flex-fill">
        <!-- left side -->
        <v-col cols="12" lg="8" v-fit-grid>
          <v-sheet-rounded class="ue-stepper-form__body d-flex w-100 flex-column justify-space-between elevation-2">
            <v-stepper-window class="fill-height overflow-y-auto px-6" style="max-height: 80vh;">
              <v-stepper-window-item
                v-for="(form, i) in forms"
                :key="`content-${i}`"
                :value="i+1"
                class=""
              >
                <ue-form
                  v-if="activeStep > i"
                  :ref="formRefs[i]"
                  :id="`stepper-form-${i+1}`"
                  :isEditing="isEditing"
                  v-model="models[i]"
                  v-model:schema="schemas[i]"
                  @input="handleInput($event, i)"
                  @update:valid="updateFormValid($event, i)"
                  noDefaultFormPadding
                />
              </v-stepper-window-item>
              <v-stepper-window-item
                :value="forms.length +1"
                class="pt-3"
              >
                <slot name="preview">
                  <v-sheet class="">
                    <ue-title weight="medium" color="black" padding="a-0">{{ $t('Preview & Summary').toUpperCase() }}</ue-title>
                    <v-row>
                      <template v-for="(context, index) in formattedPreview" :key="`summary-${index}`">
                        <v-col cols="12" :md="context.col || 6" v-fit-grid>
                          <ue-configurable-card v-bind="context" elevation="2" class="my-2"/>
                        </v-col>
                      </template>

                      <!-- <v-col cols="12">
                        <ue-configurable-card class="my-3" elevation="3"
                          align-center-columns-x
                          justify-center-columns-x
                          v-bind="getPreviewConfigurableData()"
                        />
                      </v-col> -->

                      <v-col cols="12">
                        <v-sheet class="px-4">
                          <!-- <div class="text-body-1 text-primary font-weight-bold" margin>{{ finalFormTitle }}</div> -->
                           <!-- {{ $log(previewFormData) }} -->
                          <ue-title type="body-1" color="primary" font-weight="bold" padding="a-0" margin="y-4">{{ finalFormTitle }}</ue-title>
                          <template v-for="(data, index) in previewFormData" :key="`final-form-data-${index}`">
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
                                  // icon: 'mdi-plus',
                                  // color: 'primary',
                                  // class: 'rounded-circle',
                                  onClick: () => {
                                    // console.log('clicked', lastStepModel);
                                  }
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
                              <template
                                #[`segment.1`]="segmentScope"
                                >
                                <div class="text-body-2 font-weight-medium mb-1">{{ segmentScope.data[0] }}</div>
                                <div class="text-caption">{{ segmentScope.data[1] }}</div>
                              </template>
                              <template
                                #[`segment.2`]="segmentScope"
                                >
                                <div class="text-body-2 font-weight-medium py-auto">{{ segmentScope.data }}</div>
                              </template>
                              <template
                                #[`segment.actions`]="segmentScope"
                                >

                                <div class="d-flex fill-height justify-space-evenly align-content-md-center">
                                  <v-divider v-if="$vuetify.display.lgAndUp ? true : false" vertical></v-divider>
                                  <v-btn
                                    class="mx-1 rounded-circle"
                                    :min-width="segmentScope.actionProps.actionIconMinHeight"
                                    :min-height="segmentScope.actionProps.actionIconMinHeight"
                                    :size="segmentScope.actionProps.actionIconSize"
                                    :icon="lastStepModel[data.fieldName].includes(data.id) ? 'mdi-minus' : 'mdi-plus'"
                                    :color="lastStepModel[data.fieldName].includes(data.id) ? 'grey' : 'primary'"
                                    @click="handleFinalFormAction(index)"
                                  />
                                </div>
                              </template>
                            </ue-configurable-card>
                          </template>
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
                            <!-- <ue-configurable-card
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
                        </v-sheet>

                      </v-col>
                    </v-row>
                  </v-sheet>
                </slot>
              </v-stepper-window-item>
            </v-stepper-window>

            <v-stepper-actions
              v-if="false"
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
          </v-sheet-rounded>
        </v-col>

        <!-- right side -->
        <v-col cols="12" lg="4">
          <slot name="summary">
            <v-sheet-rounded class="d-flex flex-column fill-height pa-6 elevation-2"
              :style="[isLastStep ? '' : '']"
              :class="[isLastStep ? 'pa-6 bg-primary-darken-2' : '']"
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
                            <span class="font-weight-bold text-primary"> {{ $t('fields.step') }} {{ i + 1 }}</span>
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
                                  <span class='text-primary font-weight-bold' style="margin-inline-end: 8px;">{{ window.__isObject(previewModel[i][inputName]) ? key : context[0] }}:</span>
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
                    <v-btn-secondary class="v-stepper-form__nextButton"
                      density="comfortable"
                      :disabled="$hasRequestInProgress()"
                      @click="nextForm(activeStep-1)"
                      >
                      {{ $t('next').toUpperCase() }}
                    </v-btn-secondary>
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
                  <v-sheet class="bg-primary-darken-2">
                    <ue-title justify="center" color="white" :text="$t('Total Amount').toUpperCase()" type="h5" />
                    <v-divider/>

                    <template v-for="(sample, index) in formattedSummary">
                      <ue-title :text="sample.title" transform="capitalize" type="h6" color="white" padding="x-6" margin="t-6" class="mx-n6 py-3 bg-primary-darken-1" />
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
                  </v-sheet>

                  <v-spacer></v-spacer>

                  <v-divider></v-divider>

                  <!-- Total -->
                  <v-table class="bg-transparent my-3">
                    <tbody>
                      <tr class="py-0">
                        <td class="border-0 h-auto py-1 pl-0 text-h5">{{ $t('Total').toUpperCase() }}</td>
                        <td class="border-0 h-auto py-1 d-flex justify-end pr-0 font-weight-bold">
                          <slot name="final.total" v-bind="{payload: this.payload}">
                            <ue-text-display class="text-h5" :text="`$2500`" subText="+ VAT" />
                          </slot>
                        </td>
                      </tr>
                    </tbody>
                  </v-table>

                  <!-- Description -->
                  <div class="text-caption text-grey mb-6">
                    <slot name="final.description">
                      At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium iusto odio
                    </slot>
                  </div>

                  <v-sheet class="pb-0 px-0 ue-stepper-form__preview-bottom" style="background-color: #005868;">
                    <v-btn-secondary class="v-stepper-form__nextButton"
                      block
                      :disabled="loading || isCompleted"
                      :loading="loading"
                      @click="completeForm"
                      >
                      {{ $t('Complete').toUpperCase() }}
                    </v-btn-secondary>
                  </v-sheet>
                  <!-- <v-sheet class="v-stepper-form-preview fill-height rounded-0" style="background-color: #005868;">
                  </v-sheet> -->
                </slot>
              </template>
            </v-sheet-rounded>
          </slot>
        </v-col>

      </v-row>

      <ue-modal
        ref="modal"
        v-model="modalActive"
        :width-type="'lg'"
        cancel-text="$t('Cancel')"
        confirm-text="$t('Cancel')"
        :description-text="modalMessage"
      >
        <template v-slot:body.options>
          <v-btn variant="outlined" color="primary" @click="completed">
            {{ $t('Ok') }}
          </v-btn>
        </template>
      </ue-modal>

    </template>

  </v-stepper>
</template>

<script>
  import { toRefs, reactive, ref, computed } from 'vue';
  import { map, reduce, find, each } from 'lodash-es';

  import { getModel } from '@/utils/getFormData.js'
  import { handleMultiFormEvents } from '@/utils/formEvents'

  import { useInputHandlers, useValidation } from '@/hooks'
  import api from '@/store/api/form'


  import NotationUtil from '@/utils/notation';

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
      redirectUrl: {
        type: String,
        default: null
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
          return {}
        }
      },
      previewNotations: {
        type: [Array, Object],
        default: () => {
          return []
        }
      },
      isEditing: {
        type: Boolean,
        default: false
      },
      finalFormTitle: {
        type: String,
        default: null
      },
      finalFormNotations: {
        type: Object,
        default: () => {
          return {
            '0.PackageCountry': {
              pattern: '0.wrap_location.schema.PackageCountry.items.*.package_addons',
              inputName: 'pressReleasePackageAddons',
            },
            '0.PackageRegion': {
              pattern: '0.wrap_location.schema.PackageRegion.items.*.package_addons',
              inputName: 'pressReleasePackageAddons',
            },
          }
        }
      },
      finalCardShowFields: {
        type: Array,
        default: () => {
          return [
            ['name', 'description'],
            ['basePrice_show'],
          ]
        }
      }

    },
    setup (props, context) {
      const inputHandlers = useInputHandlers()
      const validations = useValidation(props)

      const stepperActionRef = ref(null)
      const loading = ref(false)

      const state = reactive({
        stepperActionRef,
        loading,
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
        modalActive: false,
        modalMessage: '',
        isCompleted: false,

        schemas: [],
        models: [],
        valids: [],
        lastStepModel: {},

        previewModel: [],
      }
    },
    methods: {
      completed(){
        this.modalActive = false
        if(this.redirectUrl){
          window.location.href = this.redirectUrl
        }
      },
      completeForm (){
        const method = this.payload?.id ? 'put' :'post'
        const self = this

        // __log(JSON.stringify(this.schemas, null, 2))
        // __log(JSON.stringify(this.models, null, 2))
        // __log(JSON.stringify(this.displayInfo, null, 2))
        // __log(JSON.stringify(this.previewModel, null, 2))
        // __log(formData)

        this.loading = true
        // __log(this.payload)
        // return
        api[method](this.actionUrl, this.payload, function (response) {
          self.loading = false
          self.isCompleted = true
          self.modalMessage = response.data.message
          self.modalActive = true
          // redirector(response.data)

          // if (callback && typeof callback === 'function') callback(response.data)
        }, function (response) {
          self.loading = false
          __log(response)
          // if (errorCallback && typeof errorCallback === 'function') errorCallback(response.data)
        })
        return
        // const formData = getSubmitFormData(this.rawSchema, this.model, this.$store._state.data)
        // const method = Object.prototype.hasOwnProperty.call(formData, 'id') ? 'put' : 'post'
        // const self = this

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
      goStep(step){
        // all previous steps are valid
        if(this.valids.slice(0, step-1).every(v => v === true)){
          this.activeStep = step
          // __log('goStep', step, this.valids)
        }
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

        if(this.formRefs[index].value[0].validModel === true){
          this.activeStep += 1
          // callback()
        }else if(index < this.forms.length) {
          await this.validateForm(index)
        }
      },
      async validateForm(i) {
        const formRef = this.formRefs[i]
        formRef.value[0].manualValidation = true

        const result = await formRef.value[0].validate()
        formRef.value[0].manualValidation = false

        return result
      },
      handleFinalFormAction(index) {
        const data = this.previewFormData[index]
        const fieldArray = this.lastStepModel[data.fieldName];

        // Check if the id exists in the array and toggle it
        this.lastStepModel[data.fieldName] = fieldArray.includes(data.id)
          ? fieldArray.filter((id) => id !== data.id)
          : [...fieldArray, data.id];

        // Force reactivity by creating a new reference
        this.lastStepModel = { ...this.lastStepModel };
      }
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
          data[index] = this.$getDisplayData(this.schemas[index], this.models[index])
        }
        return data
      },
      formattedSummary(){
        let formatteds = NotationUtil.formattedSummary(this.displayInfo, this.summaryNotations)
        let previewFormData = this.previewFormData
        let lastStepModel = this.lastStepModel

        const lastStepSelections = reduce(lastStepModel, function(acc, data, key){
          let _data = Array.isArray(data) ? data : [data]

          each(_data, (id) => {
            const selected = find(previewFormData, (item) => item.id === id && item.fieldName === key)
            if(selected){
              acc.push(selected)
            }
          })
          return acc
        }, [])

        if(lastStepSelections.length > 0){
          formatteds['lastStepSelections'] = {
            title: this.finalFormTitle,
            values: map(lastStepSelections, (data) => {
              return {
                title: data.name || data.title || 'N/A',
                value: data.basePrice_show || 'N/A',
              }
            }),
          }
        }

        return formatteds
      },
      formattedPreview(){
        return NotationUtil.formattedPreview(this.displayInfo, this.previewNotations)
      },
      previewFormData (){
        let data = []
        for(const modelKey in this.finalFormNotations){
          let _value = __data_get(this.models, modelKey)

          if(!_value)
            continue

          let _notation = this.finalFormNotations[modelKey]
          let fieldName = null
          let notation = null

          if(__isObject(_notation)){
            notation = _notation.pattern
            fieldName = _notation.inputName || _notation.fieldName || notation.split('.').pop()
          }else{
            notation = _notation
            fieldName = notation.split('.').pop()
          }

          __log(_notation, notation, fieldName)
          if(notation){
            notation = __wildcard_change(notation, _value)
            let dataSet = __data_get(this.schemas, notation, null)

            if(dataSet){
              const pushRecursively = (item, fieldName) => {
                if(Array.isArray(item)){
                  for(const subItem of item){
                    pushRecursively(subItem, fieldName)
                  }
                } else if(typeof item === 'object' && item !== null){
                  data.push({...item, fieldName, isSelected: false})
                }
              }
              pushRecursively(dataSet, fieldName)
            }
          }
        }
        return data
      },
      payload(){
        let model = reduce(this.models, function(acc, model, index){
          return {...acc, ...model}
        }, {})

        return {
          ...model,
          ...this.lastStepModel,
        }
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
      // NotationUtil.test()

      let self = this
      this.forms.forEach((form, index) => {
        let schema = form.schema

        let model = getModel(schema, this.modelValue)

        self.models.push(model)
        self.schemas.push(self.invokeRuleGenerator(schema, model))
        self.valids.push(null)
      })
      this.previewModel = this.preview

      this.lastStepModel = reduce(this.finalFormNotations, (acc, notation, key) => {
        let fieldName = null

        if(__isObject(notation)){
          fieldName = notation.inputName || notation.fieldName || notation.pattern.split('.').pop()
        }else{
          fieldName = notation.split('.').pop()
        }

        if(!__isset(acc[fieldName])){
          acc[fieldName] = []
        }
        return acc
      }, {})
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
        // box-shadow: 0px 6px 18px 0px rgba(0, 0, 0, 0.06)
        // border-radius: 8px

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
      // background-color: $stepper-form-summary-final-background

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
