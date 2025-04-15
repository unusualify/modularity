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

            @form-input="handleInput($event)"
            @form-valid="updateFormValid($event)"
          >
            <template #preview>
              <StepperPreview
                v-if="!lastFormPreviewLoading && isLastStep"
                :formatted-preview="formattedPreview"

                :preview-form-data="lastFormPreview"
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
                  :loading="loading"
                  :is-completed="isCompleted"
                  @complete="onComplete"
                >
                  <template v-if="$slots['summary.final.body']" v-slot:body>
                    <slot name='summary.final.body' v-bind="{
                      models,
                      schemas,
                      lastStepModel,
                      finalFormFields,
                      lastFormPreview,
                    }">

                    </slot>
                  </template>
                  <template v-slot:total>
                    <slot name='summary.final.total' v-bind="{payload: this.payload}">
                      <ue-text-display class="text-h5 text-white" text="$2500" subText="+ VAT" />
                    </slot>
                  </template>
                  <template v-slot:description>
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
        :width-type="'md'"
        :description="modalMessage"
        persistent
      >
        <template v-slot:body.description>
          <v-icon size="64" color="success" class="mb-4">mdi-check-circle-outline</v-icon>
          <h2 class="text-h4 mb-4 text-success">{{ $t('Request Complete') }}</h2>
          <p class="text-subtitle-1 grey--text">{{ $t('Congratulations! Your PR request is complete.') }}</p>
        </template>
        <template v-slot:body.options>
          <v-btn variant="flat" color="success" @click="completed">
            {{ $t('Go Back') }}
          </v-btn>
        </template>
      </ue-modal>

    </template>

  </v-stepper>
</template>

<script>
  import { toRefs, reactive, ref, computed } from 'vue';
  import { map, reduce, find, each, filter, get, isEqual, uniq } from 'lodash-es';

  import { getModel } from '@/utils/getFormData.js'
  import { handleMultiFormEvents } from '@/utils/formEvents'

  import { useInputHandlers, useValidation } from '@/hooks'
  import api from '@/store/api/form'


  import NotationUtil from '@/utils/notation';

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
      finalFormFields: {
        type: Array,
        default: () => {
          return []
        }
      },

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

        lastFormPreview: [],
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
      handleInput (payload) {
        const { event, index } = payload
        const { on, key, obj, value } = event
        if (on === 'input' && !!key) {
          // if (!this.serverValid) {
          //   this.resetSchemaError(key)
          // }
          // __log(index, key, obj, v)
          // this.handleEvent(obj)
          let availableValue = get(this.models[index], key)
          if(JSON.stringify(availableValue) !== JSON.stringify(value)){
            this.pendingHandleFunctions.push((models, schemas, previewModel) => {
              handleMultiFormEvents(models, schemas, obj.schema, index, previewModel)
            })
          }else{
            handleMultiFormEvents(this.models, this.schemas, obj.schema, index, this.previewModel)
          }

        }
      },
      updateFormValid(payload) {
        const { event, index } = payload
        this.valids[index] = event
      },
      goStep(step){
        // all previous steps are valid
        if(this.valids.slice(0, step-1).every(v => v === true)){
          this.activeStep = step
        }
      },
      async nextForm(index) {

        await this.validateForm(index)

        if(this.formRefs[index].value[0].validModel === true){
          this.activeStep += 1
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
        // const data = this.previewFormData[index]
        const data = this.lastFormPreview[index]
        const fieldName = data.fieldName
        const fieldArray = this.lastStepModel[fieldName];

        // Check if the id exists in the array and toggle it
        this.lastStepModel[fieldName] = fieldArray.includes(data.id)
          ? fieldArray.filter((id) => id !== data.id)
          : [...fieldArray, data.id];

        // Force reactivity by creating a new reference
        this.lastStepModel = { ...this.lastStepModel };
      },
      async handlePreviewFormField(formField){

        if(!formField.modelNotation)
          return

        let modelNotation = formField.modelNotation
        let formFieldKey = `preview-form-field-${modelNotation}`
        let cacheFormFieldValuesKey = `preview-form-field-values-${modelNotation}`

        this.lastFormPreviewLoading = true

        let formFieldValues = this.$cacheGet(formFieldKey, {})

        let previousFormFieldValues = reduce(formFieldValues, (acc, value, key) => {
          if(value && value === true){
            acc.push(parseInt(key))
          }
          return acc
        }, [])

        let cachedFormFieldValues = reduce(formFieldValues, (acc, value, key) => {
          acc.push(parseInt(key))
          return acc
        }, [])

        let cachedFormFieldFetched = this.$cacheGet(cacheFormFieldValuesKey, [])

        let modelValue = get(this.models, modelNotation)

        if(modelValue && formField.endpoint){ // if model value is present and endpoint is present fetch new items
          let currentIds = Array.isArray(modelValue)
            ? modelValue
            : [modelValue]

          let newIds = currentIds.filter(id => !cachedFormFieldValues.includes(id))
          let cachedNewIds = currentIds.filter(id => !previousFormFieldValues.includes(id) && cachedFormFieldValues.includes(id))

          let deletedIds = previousFormFieldValues.filter(id => !currentIds.includes(id))
          let isChanged = false

          if(deletedIds.length > 0){ // delete items
            this.lastFormPreview = this.lastFormPreview.filter(item => !deletedIds.includes(item.form_field_id))

            isChanged = true
          }

          if(cachedNewIds.length > 0){ // get cached items acc. to form_field_id
            cachedNewIds.forEach(id => {
              let cachedNewItems = cachedFormFieldFetched.filter(item => item.form_field_id === id && item.form_field_notation === modelNotation)
              cachedNewItems.forEach(item => {
                this.lastFormPreview.push(item)
              })
              formFieldValues[id] = true
            })
            isChanged = true
          }

          if(newIds.length > 0){ // fetch new items

            if(!formField.endpoint){
              return
            }

            let endpoint = window.__addParametersToUrl(formField.endpoint, {ids: newIds})

            let response = await axios.get(endpoint)

            if(response.status === 200){

              let items = response.data

              items.forEach(item => {
                formFieldValues[item.id] = true
              })

              if(formField.notation){
                items = items.map(item => {
                  let value = get(item, formField.notation, [])

                  let formCardFields = formField.cardFields ?? ['name']
                  let fieldName = formField.fieldName || modelNotation.split('.').pop()

                  if(Array.isArray(value)){
                    value = value.map(subItem => {
                      return {
                        ...subItem,
                        isSelected: false,
                        fieldName: fieldName,
                        form_field_notation: modelNotation,
                        form_field_id: item.id,
                        form_card_items: formCardFields.map(cardField => {
                          if(Array.isArray(cardField)){
                            return cardField.map(cardItem => {
                              return subItem[cardItem] ?? 'N/A'
                            })
                          }else{
                            return subItem[cardField] ?? 'N/A'
                          }
                        }),
                      }
                    })
                  } else { // is object
                    value = {
                      ...value,
                      ...{
                        isSelected: false,
                        fieldName: fieldName,
                        form_field_notation: modelNotation,
                        form_field_id: item.id,
                        form_card_items: formCardFields.map(cardField => {
                          if(Array.isArray(cardField)){
                            return cardField.map(cardItem => {
                              return value[cardItem] ?? 'N/A'
                            })
                          }else{
                            return value[cardField] ?? 'N/A'
                          }
                        })
                      }
                    }
                  }
                  return value
                })
              } else {
                items = items.map(item => {
                  return {
                    ...item,
                    form_field_notation: formField.notation,
                    form_field_id: item.id
                  }
                })
              }

              let flattenedItems = reduce(items, (acc, item) => {
                if(Array.isArray(item)){
                  return [...acc, ...item]
                } else {
                  return [...acc, item]
                }
              }, [])

              flattenedItems.forEach(item => {
                this.$cachePush(cacheFormFieldValuesKey, item)
                this.lastFormPreview.push(item)
              })

              if(flattenedItems.length > 0){
                isChanged = true
              }

            }
          }

          if(isChanged){
            this.$cachePut(formFieldKey, formFieldValues)
          }

        } else if(previousFormFieldValues.length > 0){ // clear if model value is null and previous form field values are present
          this.lastFormPreview = this.lastFormPreview.filter(item => previousFormFieldValues.includes(item.form_field_id))
        }

        this.lastFormPreviewLoading = false
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
      formattedPreview(){
        return NotationUtil.formattedPreview(this.displayInfo, this.previewNotations)
      },

      payload(){
        let model = reduce(this.models, function(acc, model, index){
          return {...acc, ...model}
        }, {})

        let lastFormPreview = this.lastFormPreview

        let lastStepModel = reduce(this.lastStepModel, function(acc, value, key){
          if(Array.isArray(value)){
            value = value.reduce((acc, id) => {
              let item = lastFormPreview.find((previewItem) => previewItem.id === id && previewItem.fieldName === key)

              if(item){
                acc.push(id)
              }
              return acc
            }, [])
            acc[key] = value
          }else{
            acc[key] = value
          }
          return acc
        }, {})

        return {
          ...model,
          ...lastStepModel,
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
        handler (newVal, oldVal) {
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
      },
      activeStep: {
        handler (value, oldValue) {

          this.finalFormFields.forEach((formField) => {
            if(formField.afterStep && formField.afterStep === oldValue){
              this.handlePreviewFormField(formField)
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

      this.lastStepModel = reduce(this.finalFormFields, (acc, finalFormField, key) => {
        let fieldName = null

        if(__isObject(finalFormField)){
          fieldName = finalFormField.inputName || finalFormField.fieldName || finalFormField.modelNotation.split('.').pop()
        }else{
          fieldName = finalFormField.split('.').pop()
        }

        if(!__isset(acc[fieldName])){
          acc[fieldName] = []
        }
        return acc
      }, {})

      this.lastStepModel = this.$lodash.mapValues(this.lastStepModel, (data, key) => {
        if(this.modelValue[key]){
          return this.modelValue[key]
        }
        return data
      })

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
