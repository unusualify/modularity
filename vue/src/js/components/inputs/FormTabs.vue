<template>
  <v-input
    ref="VInput"
    v-model="models"
    hide-details
    multiple
    :rules="formTabsRules"
    class="v-input-form-tabs"
    >
    <!-- <template v-slot:message="messageScope">
      {{ messageScope.message }}
    </template> -->
    <template v-slot:default="defaultSlot">
      <v-skeleton-loader
        v-if="loading"
        type="table-row-divider, table-row@4"
        height="400px"
        width="100%"
      ></v-skeleton-loader>
      <ue-tabs v-else v-model="activeTab" :items="elements" tab-value="id" tab-title="name">
        <template v-for="(item, i) in elements" :key="`tab-slot-${i}`" v-slot:[`tab.${item.id}`]>
          <v-tab :value="item.id"
            :color="!this.valids[i] && window.__isBoolean(this.valids[i]) ? 'error' : 'primary'"
            :class="[window.__isBoolean(this.valids[i]) && !this.valids[i] ? 'bg-red-lighten-5' : '']"
          >
            <span>{{ item.name }}</span>
            <template v-slot:append>
              <v-icon v-if="window.__isBoolean(valids[i])" :icon="valids[i] ? '$check' : '$close'"></v-icon>
            </template>
          </v-tab>
        </template>
        <template v-slot:windows="{active}">
          <v-window v-model="activeTab">
            <v-window-item v-for="(item, i) in elements" :key="item.id" :value="item.id">
              <ue-form
                :ref="(el) => setFormRef(item.id, el)"
                :key="`form-${item.id}-${JSON.stringify(schemas[item.id])}`"

                vvvvv-model="models[item.id]"
                :modelValue="models[item.id]"
                @update:modelValue="updateModel($event, item.id)"
                @Xupdate:modelValue="$log('update:modelValue', $event, item.id)"

                :schema="schemas[item.id]"

                v-model:valid="valids[i]"

                noDefaultFormPadding
                class="pt-3"
              />
            </v-window-item>
          </v-window>
        </template>
      </ue-tabs>

    </template>
  </v-input>
</template>

<script>
import { toRefs, reactive, ref, computed, watch } from 'vue';
import { cloneDeep, each, filter, startCase, snakeCase, isString } from 'lodash-es';
import { useI18n } from 'vue-i18n';
import { useInput,
  makeInputProps,
  makeInputEmits,
  makeInputInjects,
  useInputHandlers,
  useValidation,
  useCastAttributes
} from '@/hooks'

import { getModel, getSchema } from '@/utils/getFormData.js'

export default {
  name: 'v-input-form-tabs',
  emits: [...makeInputEmits],
  inject: [...makeInputInjects],
  props: {
    ...makeInputProps(),
    modelValue: {
      type: Object,
      default: () => {}
    },
    schema: {
      type: Object,
      default: () => {}
    },
    tabFields: {
      type: Object,
      default: () => {}
    },
    items: {
      type: Object,
      default: () => []
    },
    triggers: {
      type: Object,
      default: () => []
    },
    protectDefiner: {
      type: String,
    },
    protectedInputs: {
      type: Array,
      default: () => ['*']
    }
  },
  setup (props, { emit }) {
    const inputHandlers = useInputHandlers()
    const validations = useValidation(props)
    const { t, te } = useI18n()
    const inputHook = useInput(props, { emit })

    const { matchStandardAttribute, castStandardAttribute, castEvalAttribute, castAttribute, castObjectAttribute } = useCastAttributes()

    const elements = ref(props.items)
    const loading = ref(true)

    const formRefs = reactive(new Map())

    const setFormRef = (id, el) => {
      if (el) {
        formRefs.set(id, el)
      } else {
        formRefs.delete(id)
      }
    }

    const getFormRef = (id) => formRefs.get(id)

    const models = ref(elements.value.reduce((acc, item) => {
      if(!__isset(acc[item.id])){
        acc[item.id] = {
          ...(getModel(props.schema, {})),
          ...(inputHook.input.value?.[item.id] ?? {}),
        }
      }
      return acc
    }, {}))

    const processTriggers = (newVal, updatedTabId, schemas) => {
      let schemasChanged = false
      const modelKeys = Object.keys(newVal)

      modelKeys.forEach(triggerKey => {
        const triggers = filter(props.triggers, trigger => trigger.trigger === triggerKey)

        if(triggers.length < 1) return

        const newValue = newVal[triggerKey]
        const triggerSchema = cloneDeep(schemas[updatedTabId][triggerKey])
        let triggerItem = null

        if (triggerSchema.items && Array.isArray(triggerSchema.items)) {
          triggerItem = triggerSchema.items.find(item => item.id == newValue)
        }

        triggers.forEach(trigger => {
          let targetInput = cloneDeep(schemas[updatedTabId][trigger.target])
          let rulesChanged = false

          each(trigger.actions, (actionValue, actionName) => {
            if (Array.isArray(actionValue)) {
              if (actionName === 'class') {
                let availableValue = targetInput?.class ?? ''

                if (availableValue) {
                  let classes = availableValue.split(' ')
                  let [func, ...newClasses] = actionValue

                  if (func === 'remove') {
                    classes = classes.filter(cls => !newClasses.includes(cls))
                  } else if (func === 'add') {
                    classes = [...classes, ...newClasses]
                  }
                  targetInput.class = classes.join(' ')
                  schemasChanged = true
                }
              }
            } else {
              schemasChanged = true
              if (actionName === 'rules') {
                rulesChanged = true
                actionName = 'raw' + startCase(actionName)
              }
              if (actionName === 'disabled') {
                targetInput['_originalDisabled'] = actionValue
              }

              if (actionName === 'items') {
                targetInput[actionName] = __data_get(triggerItem, actionValue, [])
              } else if (triggerItem) {
                targetInput[actionName] = matchStandardAttribute(actionValue)
                  ? castStandardAttribute(actionValue, triggerItem)
                  : actionValue
              }
            }
          })

          schemas[updatedTabId][trigger.target] = targetInput
          if (rulesChanged) {
            schemas[updatedTabId] = validations.invokeRuleGenerator(schemas[updatedTabId])
          }
        })
      })

      return { schemas, schemasChanged }
    }

    const generateSchema = (item) => {
      const baseSchema = cloneDeep(props.schema)

      let protectInitialValue = props.protectInitialValue
        && props.protectDefiner
        && __isset(props.modelValue[item.id])
        && __isset(props.modelValue[item.id][props.protectDefiner])
        && props.modelValue[item.id][props.protectDefiner] !== null
        && props.modelValue[item.id][props.protectDefiner] !== undefined

      for(const inputName in props.tabFields){
        if(protectInitialValue
          && Array.isArray(props.protectedInputs)
          && props.protectedInputs.length > 0
          && (props.protectedInputs[0] === '*' || props.protectedInputs.includes(inputName))){
          baseSchema[inputName]['protectInitialValue'] = true
        }

        if(__isset(baseSchema[inputName])){
          baseSchema[inputName]['items'] = item[props.tabFields[inputName]]
        }

        each(baseSchema[inputName], (value, key) => {
          if(isString(value)){
            baseSchema[inputName][key] = matchStandardAttribute(value)
              ? castStandardAttribute(value, item)
              : value
          } else {
            baseSchema[inputName][key] = value
          }
        })
      }

      return getSchema(baseSchema, models.value?.[item.id] ?? {})
    }

    const schemas = ref(elements.value.reduce((acc, item, index) => {
      if(!__isset(acc[item.id])){
        acc[item.id] = generateSchema(item)
        // Process initial triggers
        const { schemas: updatedSchemas } = processTriggers(models.value[item.id], item.id, acc)
        acc = updatedSchemas
      }
      return acc
    }, {}))

    const valids = ref(elements.value.reduce((acc, item, i) => {
      acc[i] = null
      return acc
    }, {}))

    if(elements.value.length > 0){
      loading.value = false
    }

    const states = reactive({
      loading,
      elements,
      formRefs,
      activeTab: 1,
      models,
      schemas,
      valids,
    })

    watch(() => props.items, (newVal) => {
      loading.value = true

      let addedItems = newVal.filter(item => !elements.value.find(el => el.id == item.id))
      let removedItems = elements.value.filter(item => !newVal.find(el => el.id == item.id))

      if(addedItems.length > 0){
        addedItems.forEach(item => {
          if(!__isset(models.value[item.id])){
            models.value[item.id] = {
              ...(getModel(props.schema, {})),
              ...(inputHook.input.value?.[item.id] ?? {}),
            }
          }

          if(!__isset(schemas.value[item.id])){
            schemas.value[item.id] = generateSchema(item)
            const { schemas: updatedSchemas } = processTriggers(models.value[item.id], item.id, schemas.value)
            schemas.value = updatedSchemas
          }

          valids.value[item.id] = null
          // setFormRef(item.id, null)
        })
      }

      if(removedItems.length > 0){
        removedItems.forEach(item => {
          delete models.value[item.id]
          delete schemas.value[item.id]
          delete valids.value[item.id]
          setFormRef(item.id, null)
        })
      }

      elements.value = newVal
    })

    watch(() => elements.value, (newVal) => {
      setTimeout(() => {
        if(newVal.length > 0){
          loading.value = false
        }
      }, 1000)
    })

    const updateModel = (newVal, updatedTabId) => {
      let oldModels = cloneDeep(models.value)
      let oldValue = oldModels[updatedTabId]
      let schemasClone = cloneDeep(schemas.value)

      // Process triggers
      const { schemas: updatedSchemas, schemasChanged } = processTriggers(newVal, updatedTabId, schemasClone)

      if(schemasChanged) {
        schemas.value = cloneDeep(updatedSchemas)
      }

      oldModels[updatedTabId] = newVal
      models.value = cloneDeep(oldModels)

      emit('update:modelValue', models.value)
    }

    return {
      ...inputHook,
      ...inputHandlers,
      ...validations,
      ...toRefs(states),
      setFormRef,
      getFormRef,
      updateModel,
      schemas,
    }
  },
  data() {
    return {
      // elements: this.items,

      formTabsRules: [
        (v) =>  {
          let isValid = true

          loopValues:
          for(const id in v){

            let model = v[id]

            for(const name in this.schema){
              let value = model[name]
              let input = this.schema[name]

              // let item = this.elements.find((el) => el.id == id)
              isValid = this.validateInput(input, value)

              if(isValid !== true && this.manualValidation){
                this.activeTab = parseInt(id)
                let formRef = this.getFormRef(this.activeTab)
                if(formRef){
                  formRef.validate()
                  break loopValues;
                }else{
                  console.info('FormTabs validateInput', {
                    id,
                    formRef,
                    formRefValue: this.getFormRef(id)?.value,
                    activeTab: this.activeTab,
                    formRefs: this.formRefs,
                  })
                }
              }

              if(isValid !== true){
                break loopValues;
              }
            }
          }

          return isValid
        },
      ]
    }
  },
  computed: {

  },
  watch: {
    models: {
      handler (value, oldValue) {
        this.validate()

        // this.$emit('update:modelValue', value)
      },
      deep: true
    },
  },
  methods: {
    async validate() {
      // if(this.$refs.VInput){
      //   const result = await this.$refs.VInput.validate()
      // }
      if(this.VInput){
        const result = await this.VInput.validate()
      }
    },
    async validateForm(i) {
      const formRef = this.getFormRef(i)

      const result = await formRef.value[0].validate()

      return result
    },

    init() {}

  },
  created() {
    // this.init()
  }
}
</script>

<style lang="sass">
  .v-input-form-tabs
    .v-input__control
      display: block


</style>

<style lang="scss">

</style>
