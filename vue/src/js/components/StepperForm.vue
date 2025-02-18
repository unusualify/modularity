<template>
  <v-stepper v-model="activeStep" color="prima" :class="['ue-stepper-form','ue-stepper--no-background', 'fill-height  d-flex flex-column']">
    <template v-slot:default="{ prev, next }">
      <StepperHeader
        :forms="forms"
        :active-step="activeStep"
        @step-click="goStep"
      />

      <v-row class="mt-4 flex-fill">
        <!-- left side -->
        <v-col cols="12" lg="8" v-fit-grid>
          <StepperContent
            v-model="models"
            :schemas="schemas"
            @update:schemas="schemas = $event"
            :forms="forms"
            :active-step="activeStep"
            :form-refs="formRefs"
            :is-editing="isEditing"
            @form-input="handleInput"
            @form-valid="updateFormValid"
          >
            <template #preview>
              <StepperPreview
                :formatted-preview="formattedPreview"
                :preview-form-data="previewFormData"
                :last-step-model="lastStepModel"
                :final-form-title="finalFormTitle"
                @final-form-action="handleFinalFormAction"
              />
            </template>
          </StepperContent>
        </v-col>

        <!-- right side -->
        <v-col cols="12" lg="4">
          <StepperSummary
            :is-last-step="isLastStep"
            :forms="forms"
            :active-step="activeStep"
            :models="models"
            :schemas="schemas"
            :preview-model="previewModel"
            :preview-titles="previewTitles"
            :is-preview-model-filled="isPreviewModelFilled"
            @next-form="nextForm"
            @complete-form="completeForm"
          >
            <template v-for="(slot, slotName) in summaryFormScopes" :key="`slot-${i}`" v-slot:[`${slotName}`]="summaryFormScope">
              <slot :name="slotName" v-bind="summaryFormScope">

              </slot>
            </template>

            <template #summary.final="{ onComplete }">
              <slot name="summary.final"
                v-bind="{
                  model: models,
                  schema: schemas,
                  previewModel: previewModel,
                  completeForm: completeForm,
                }"
              >
                <StepperFinalSummary
                  :formatted-summary="formattedSummary"
                  :loading="loading"
                  :is-completed="isCompleted"
                  @complete="onComplete"
                >
                  <template v-slot:total>
                    <slot name='summary.final.total' v-bind="{payload: this.payload}">
                      <ue-text-display class="text-h5 text-white" text="$2500" subText="+ VAT" />
                    </slot>
                  </template>
                  <template v-slotdescription>
                    <slot name="summary.final.description">
                      At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium iusto odio
                    </slot>
                  </template>

                </StepperFinalSummary>
              </slot>
            </template>
          </StepperSummary>
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
  import { map, reduce, find, each, filter, get, isEqual } from 'lodash-es';

  import { getModel, handleEvents, handleMultiFormEvents } from '@/utils/getFormData.js'

  import { useInputHandlers, useValidation } from '@/hooks'
  import api from '@/store/api/form'


  import NotationUtil from '@/utils/notation';
  import notation from '../utils/notation';

  import StepperHeader from './stepper/StepperHeader.vue'
  import StepperContent from './stepper/StepperContent.vue'
  import StepperSummary from './stepper/StepperSummary.vue'
  import StepperPreview from './stepper/StepperPreview.vue'
  import StepperFinalSummary from './stepper/StepperFinalSummary.vue'

  export default {
    name: 'StepperForm',
    components: {
      StepperHeader,
      StepperContent,
      StepperSummary,
      StepperPreview,
      StepperFinalSummary
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
        pendingHandleFunctions: [],
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
        const { on, key, obj, value } = v
        if (on === 'input' && !!key) {
          // if (!this.serverValid) {
          //   this.resetSchemaError(key)
          // }
          // __log(index, key, obj, v)
          // this.handleEvent(obj)
          let availableValue = get(this.models[index], key)

          if(JSON.stringify(availableValue) !== JSON.stringify(value)){
            this.pendingHandleFunctions.push((models, schemas, previewModel) => {
              // __log('run pending function', obj.schema)
              handleMultiFormEvents(models, schemas, obj.schema, index, previewModel)
            })
          }else{
            handleMultiFormEvents(this.models, this.schemas, obj.schema, index, this.previewModel)
          }

          // __log('handleInput', v, this.models,)
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
      },
      summaryFormScopes(){
        return reduce(this.$slots, (acc, slot, slotName) => {
            if(slotName.match(/summary-form-\d+/)){
              acc[slotName] = slot
            }
          return acc
        }, {})
      }
    },
    watch: {
      schemas: {
        handler (value, oldValue) {
          // __log('schemas watch', value, oldValue)
          // __log(value[0].wrap_location, oldValue[0].wrap_location)
          // __log('stepperForm schemas watch', value, this.schemas, !isEqual(value, this.schemas))
          if(!isEqual(value, this.schemas)){
            // __log('schemas watch', value, this.schemas)
            this.schemas = value
          }
        },
        deep: true
      },
      models: {
        handler (value, oldValue) {

          if(this.pendingHandleFunctions.length > 0){
            this.pendingHandleFunctions.forEach((fn) => {
              if(typeof fn === 'function'){
                fn(this.models, this.schemas, this.previewModel)
              }
            })
            this.pendingHandleFunctions = []
          }
          // __log(value[0].wrap_location, oldValue[0].wrap_location)
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
