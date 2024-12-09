<template>
  <v-input
    ref="VInput"
    v-model="models"
    hide-details
    multiple
    :rules="tabGroupRules"
    class="v-input-tab-group"
    >
    <!-- <template v-slot:message="messageScope">
      {{ messageScope.message }}
    </template> -->
    <template v-slot:default="defaultSlot">
      <ue-tabs v-model="activeTab" :items="items" tab-value="id" tab-title="name">
        <template v-for="(item, i) in items" :key="`tab-slot-${i}`" v-slot:[`tab.${item.id}`]>
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
                :ref="formRefs[item.id]"
                :key="`form-${item.id}-${JSON.stringify(schemas[item.id])}`"
                v-modelx="models[item.id]"
                :modelValue="models[item.id]"
                @update:modelValue="updateModel($event, item.id)"
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
import { toRefs, reactive, ref, computed } from 'vue';
import { cloneDeep, each, filter, startCase } from 'lodash-es';

import { useInput,
  makeInputProps,
  makeInputEmits,
  makeInputInjects,
  useInputHandlers,
  useValidation
} from '@/hooks'

import { getModel, getSchema } from '@/utils/getFormData.js'

// __log([...makeInputInjects])
export default {
  name: 'v-input-tab-group',
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
    }
  },
  setup (props, context) {
    const inputHandlers = useInputHandlers()
    const validations = useValidation(props)

    const state = reactive({
      activeTab: 1,
      models: {},
      schemas: {},
      valids: {},
    })

    const formRefs = computed(() => props.items.reduce((acc,item,i) => {
      acc[item.id] = ref(null)

      return acc
    }, {}))

    return {
      ...useInput(props, context),
      ...inputHandlers,
      ...validations,
      ...toRefs(state),
      formRefs,
    }
  },
  data: function () {
    return {
      elements: this.items,

      tabGroupRules: [
        (v) =>  {
          let isValid = true

          let inValidGroupIndex
          let inValidInputName

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
                let formRef = this.formRefs[id]
                // __log(formRef.value, formRef)
                formRef.value[0].validate()
                break loopValues;
              }

              if(isValid !== true){
                break loopValues;
              }

              // if(Array.isArray(value)){
              //   if(value.length < 1){
              //     isValid = false
              //     message = `Please select one of items of ${name} field for ${item.name} tab`
              //   }
              // }else {
              //   if(value == ''){
              //     isValid = false
              //     message = `Please fill ${name} field for ${item.name} tab`
              //   }
              // }

              // if(!isValid){
              //   inValidGroupIndex = id
              //   inValidInputName = name
              //   break
              // }

            }
          }

          // if(!v.package)
          //   return ''
          return isValid
        },
      ]
    }
  },
  computed: {

  },
  watch: {
    activeTab: {
      handler (value, oldValue) {
        __log('activeTab', value)
      }
    },
    models: {
      handler (value, oldValue) {
        this.validate()

        // this.$emit('update:modelValue', value)
      },
      deep: true
    },
    valids: {
      handler (value, oldValue) {

      },
      deep: true
    },
  },
  methods: {
    async validate() {
      if(this.$refs.VInput){
        const result = await this.$refs.VInput.validate()
      }
    },
    async validateForm(i) {
      const formRef = this.formRefs[i]

      const result = await formRef.value[0].validate()

      return result
    },

    updateModel(val, index) {
      let oldModels = cloneDeep(this.models)
      let oldValue = oldModels[index]

      // Find changed keys by comparing old and new values
      const changedKeys = Object.keys(val).filter(key =>
        __isset(oldValue[key]) && JSON.stringify(val[key]) !== JSON.stringify(oldValue[key])
      )

      const changedValues = changedKeys.reduce((acc, key) => {
        acc[key] = {
          old: oldValue[key],
          new: val[key]
        }
        return acc
      }, {})

      let schemas = cloneDeep(this.schemas)
      let schemasChanged = false

      each(changedKeys, (triggerKey) => {
        let triggers = filter(this.triggers, (trigger) => trigger.trigger == triggerKey)
        let newValue = changedValues[triggerKey].new
        let triggerSchema = cloneDeep(this.schemas[index][triggerKey])
        let triggerItem = null

        if(triggerSchema.items && Array.isArray(triggerSchema.items)){
          triggerItem = triggerSchema.items.find((item) => item.id == newValue)
        }

        each(triggers, (trigger) => {
          let targetInput = cloneDeep(this.schemas[index][trigger.target])
          let rulesChanged = false

          each(trigger.actions, (actionValue, actionName) => {
            schemasChanged = true
            let key = actionName
            if(actionName == 'rules'){
              rulesChanged = true
              actionName = 'raw' + startCase(actionName)
            }
            if(actionName == 'disabled'){
              targetInput['_originalDisabled'] = actionValue
            }

            if(triggerItem){
              targetInput[actionName] = this.$castValueMatch(actionValue, triggerItem)
            }
          })
          schemas[index][trigger.target] = targetInput
          if(rulesChanged){
            schemas[index] = this.invokeRuleGenerator(schemas[index])
          }
        })
      })

      if(schemasChanged){
        this.schemas = cloneDeep(schemas)
      }

      // __log('Changed keys:', changedKeys, oldValue, val)

      oldModels[index] = val
      this.models = cloneDeep(oldModels)
      // __log('models', this.models)
      this.$emit('update:modelValue', this.models)
    }

  },
  created() {

    this.models = this.elements.reduce((acc, item) => {
      if(!__isset(acc[item.id])){
        acc[item.id] = this.input?.[item.id] ?? {}
      }

      return acc
    }, {})

    this.schemas = this.elements.reduce((acc, item, index) => {
      if(!__isset(acc[item.id])){
        const baseSchema = cloneDeep(this.schema)
        for(const inputName in this.tabFields){
          if(__isset(baseSchema[inputName])){
            baseSchema[inputName]['items'] = item[this.tabFields[inputName]]
          }
          // #TODO: __cast_value_match in init.js
          each(baseSchema[inputName], (value, key) => {
            baseSchema[inputName][key] = this.$castValueMatch(value, this.elements[index])
          })
        }
        // acc[item.id] = this.invokeRuleGenerator(getSchema(baseSchema, this.models?.[item.id] ?? {}))
        acc[item.id] = getSchema(baseSchema, this.models?.[item.id] ?? {})
      }

      return acc
    }, {})

    this.valids = this.elements.reduce((acc, item,i ) => {
      acc[i] = null

      return acc
    }, {})
  }
}
</script>

<style lang="sass">
  .v-input-tab-group
    .v-input__control
      display: block


</style>

<style lang="scss">

</style>
