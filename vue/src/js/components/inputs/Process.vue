<template>
  <div>
    <ue-modal v-if="formSchema && processableModel"
      ref="formModal"
      v-model="formActive"
      scrollablex
      transition="dialog-bottom-transition"
      width-type="md"
      full

      eager
    >
      <template v-slot:body="formModalBodyScope">
        <v-card class="fill-height d-flex flex-column">
          <ue-form
            ref="UeForm"
            v-model="processableModel"
            :schema="formSchema"

            :title="title"
            :style="formModalBodyScope.isFullActive ? 'height: 90vh !important;' : 'height: 70vh !important;'"

            is-editing
            fill-height
            scrollable
            has-divider
          />

          <v-divider class="mx-6 mt-4"/>

          <v-card-actions class="px-6 flex-grow-0">
            <v-spacer></v-spacer>
            <v-btn-secondary
              :slim="false"
              variant="outlined"
              @click="$refs.formModal.close()"
            >
              {{ $t('Close') }}
            </v-btn-secondary>
            <v-btn-secondary
              v-if="$store.getters.isSuperAdmin"
              :slim="false"
              variant="outlined"
              @click="UeForm.validate()"
            >
              {{ $t('Validate') }}
            </v-btn-secondary>
            <v-btn-primary
              :slim="false"
              variant="elevated"
              @click="updateProcessable"
              :disabled="!formIsValid"
              >
              {{ $t('fields.save') }}
            </v-btn-primary>
          </v-card-actions>

        </v-card>
      </template>
    </ue-modal>

    <v-card
      :color="processModel?.status_card_color ?? color"
      :variant="processModel?.status_card_variant ?? cardVariant"
      class="mb-4 w-100 h-100 d-flex flex-column"
    >

      <v-skeleton-loader v-if="loading" type="table-heading, list-item-three-line" class="mb-4 w-100 h-100" style="min-height: 300px;"></v-skeleton-loader>

      <template v-else-if="processModel">
        <v-card-text class="d-flex flex-column">

          <!-- Processable Title -->
          <v-row no-gutters class="pb-4">
            <v-col cols="12" sm="6" md="6" lg="8" xl="9" class="text-h5 font-weight-medium text-wrap pb-2">
              {{ title }}
            </v-col>
            <v-col cols="12" sm="6" md="6" lg="4" xl="3" class="d-flex justify-sm-end pb-2">
              <v-chip
                :prepend-icon="processModel.status_icon"
                :color="processModel.status_color"
                class="text-subtitle-1"
              >
                {{ processModel.status_label }}
              </v-chip>
            </v-col>
          </v-row>

          <v-spacer class="flex-grow-0"></v-spacer>

          <!-- Edit Button -->
          <v-row v-if="$hasRoles(processableEditableRoles)">
            <v-col cols="12" class="text-center" v-if="formSchema && processableModel">
              <v-btn
                ref="editButton"
                colorx="primary"
                density="compact"
                :variant="bothFormAndProcessableValid ? 'outlined' : 'elevated'"
                :elevation="bothFormAndProcessableValid ? null : 10"
                :color="bothFormAndProcessableValid ? 'success' : onlyOneValid ? 'warning' : 'secondary'"
                :prepend-icon="bothFormAndProcessableValid ? 'mdi-check-circle-outline' :  (onlyOneValid ? 'mdi-progress-question' : 'mdi-gesture-tap')"
                @click="$log('edit', $refs.formModal.open())"
                class="mb-4"
              >
                {{ bothFormAndProcessableValid ? $t('Edit') : onlyOneValid ? $t('Complete') : $t('Fill') }}
              </v-btn>
            </v-col>
          </v-row>

          <!-- Processable Details -->
          <ue-list-section v-if="flattenedProcessableDetails.length > 0"
            :items="flattenedProcessableDetails"
            :item-fields="['title', 'value']"
            :col-classes="['font-weight-medium text-wrap', 'd-flex justify-start']"
            :col-ratios="[5,7]"
          >
          </ue-list-section>

          <!-- Processable Form display -->
          <template v-if="showProcessableDetails && processableModel">
            <ue-list-section
              v-for="formInput in formSchema"
              :key="formInput.name"
              :items="[
                {
                  title: formInput.label ?? $t('Unknown'),
                  value: processableModel?.[formInput.name],
                }
              ]"
              col-element="div"
              :item-fields="['title', 'value']"
              :col-classes="['font-weight-medium text-wrap', 'd-flex justify-start']"
              item-classes="text-body-2"
              :col-ratios="displayColRatio"
              vertical-align-top
            >
              <template v-slot:field.1="slotScope">
                <!-- <div class="d-flex" style=""> -->
                  <ue-filepond-preview
                    v-if="formInput.type === 'input-filepond'"
                    :source="slotScope.value"
                    show-inline-file-name
                    image-size="24"
                    style="width: 155px;"
                  />
                  <template
                    v-else-if="(formInput.type === 'text' && formInput.ext === 'date') || (formInput.type === 'date-input')"
                  >
                    {{ slotScope.value ? $d(slotScope.value, 'numeric') : '' }}
                  </template>
                  <template
                    v-else
                    class="text-subtitle-1"
                  >
                    {{ slotScope.value }}
                  </template>
                <!-- </div> -->
              </template>
            </ue-list-section>
          </template>

          <!-- History -->
          <ue-list-section
            v-if="processModel.last_history && processModel.last_history.reason"
            :items="[
              {
                title: processModel.status_reason_label,
                value: processModel.last_history.reason,
              }
            ]"
            :item-fields="['title', 'value']"
            :col-classes="['font-weight-bold text-wrap', 'd-flex justify-start']"
            :col-ratios="historyColRatio ?? displayColRatio"
          >
          </ue-list-section>

          <template v-if="!(showProcessableDetails && processableModel)">
            <!-- show informational message about contents preparing or updating by vuetify compenents in well formatted-->
            <v-alert
              type="info"
              variant="tonal"
              :color="processModel?.status_card_color ?? color"
              class="my-4"
            >
              {{ informationalMessage }}
            </v-alert>
          </template>
        </v-card-text>

        <v-spacer></v-spacer>

        <v-card-actions class="px-4" v-if="$hasRoles(processEditableRoles)" >
          <v-row>
            <v-col cols="12" class="text-center" v-if="processModel && processModel.status">

              <!-- Preparing -->
              <template v-if="processModel.status === 'preparing' && canAction(processModel.status)">
                <v-btn
                  :color="processModel.next_action_color"
                  variant="elevated"
                  :loading="updating"
                  @click="updateProcess('waiting_for_confirmation')"

                  :disabled="!bothFormAndProcessableValid"
                >
                  {{ processModel.next_action_label }}
                </v-btn>
              </template>

              <!-- Waiting for Confirmation -->
              <template v-else-if="processModel.status.match(/waiting_for_confirmation|waiting_for_reaction/) && canAction(processModel.status)">
                <ue-modal
                  ref="promptModal"
                  v-model="promptModalActive"
                  width-type="sm"
                  transition="dialog-bottom-transition"

                  >
                  <template v-slot:activator="{ props }">
                    <v-btn
                      color="error"
                      variant="outlined"
                      class="mr-2"
                      v-bind="props"
                    >
                      <v-icon left>mdi-close</v-icon>
                    </v-btn>
                  </template>
                  <template v-slot:body="modalBodyScope">
                    <v-card>
                      <v-card-text>
                        <v-textarea v-model="reason" variant="outlined" label="Reason" />
                      </v-card-text>
                      <v-card-actions>
                        <v-btn
                          :slim="false"
                          color="grey"
                          variant="outlined"
                          :disabled="updating"
                          @click="promptModalActive = false"
                        >
                          {{ $t('Close') }}
                        </v-btn>
                        <v-btn
                          color="error"
                          :slim="false"
                          variant="elevated"
                          :disabled="!reason"
                          :loading="updating"
                          @click="confirmPrompt('rejected')"
                        >
                          {{ $t('Reject') }}
                        </v-btn>
                      </v-card-actions>
                    </v-card>
                  </template>
                </ue-modal>
                <v-btn
                  color="success"
                  variant="elevated"
                  :loading="updating"
                  @click="updateProcess('confirmed')"
                >
                  <v-icon left>mdi-check</v-icon>
                </v-btn>
              </template>

              <!-- Rejected -->
              <template v-else-if="processModel.status === 'rejected' && canAction(processModel.status)">
                <v-btn
                  :color="processModel.next_action_color"
                  variant="elevated"
                  :loading="updating"
                  @click="updateProcess('waiting_for_reaction')"
                >
                  {{ processModel.next_action_label }}
                </v-btn>
              </template>

            </v-col>
          </v-row>
        </v-card-actions>

      </template>
    </v-card>

  </div>
</template>

<script>
import { ref, reactive, computed, toRefs } from 'vue'
import _ from 'lodash-es'
import { useI18n } from 'vue-i18n'

import { useInput, makeInputProps, makeInputEmits, useAuthorization } from '@/hooks'
import { ALERT } from '@/store/mutations'


export default {
  name: 'v-input-process',
  emits: [...makeInputEmits],
  components: {

  },
  props: {
    ...makeInputProps(),
    color: {
      type: String,
      default: 'primary',
    },
    cardVariant: {
      type: String,
      default: 'outlined',
    },
    process: {
      type: Object
    },
    processableTitle: {
      type: String,
      default: 'name'
    },
    processableDetails: {
      type: Array,
      default: () => [],
    },
    schema: {
      type: Object,
    },
    formatters: {
      type: Object,
      default: () => {}
    },
    fetchEndpoint: {
      type: String,
      required: true,
    },
    updateEndpoint: {
      type: String,
      required: true,
    },
    processableEditableRoles: {
      type: Array,
      default: () => ['superadmin']
    },
    processEditableRoles: {
      type: Array,
      default: () => ['superadmin']
    },
    processableShowStatuses: {
      type: [Array, Object],
      default: () => ['*']
    },
    actionRoles: {
      type: Object,
      default: () => {}
    },
    displayColRatio: {
      type: Array,
      default: () => [5,7]
    },
    historyColRatio: {
      type: Array,
      default: null
    },
  },
  setup (props, context) {
    const { t } = useI18n()

    const { hasRoles } = useAuthorization()

    const initializeInput = (val) => {
      return val
    }

    const updateModelValue = (val) => {
      context.emit('update:modelValue', val)
    }

    const processModel = ref(props.process)
    const processableModel = ref(props.process?.processable ?? null)
    const processValid = ref(false)
    const processableValid = ref(false)

    const formatSchema = () => {
      let schema = {}
      let processable = _.get(processModel.value, 'processable', {});

      if(props.schema && __isObject(props.schema)){
        schema = props.schema;

        if(props.formatters && __isObject(props.formatters)){
          for(const [inputName, formatter] of Object.entries(props.formatters)){
            if(schema[inputName]){
              for(const [formattedKey, formatterValues] of Object.entries(formatter)){
                let newFormatterValues = formatterValues;
                if(__isString(formatterValues)){
                  newFormatterValues = formatterValues.split(',');
                }

                let [processableKey, formatterFunction] = newFormatterValues;

                if(__isset(processable[processableKey])){
                  let value = processable[processableKey];

                  if(__isset(value[formatterFunction])){
                    schema[inputName][formattedKey] = value[formatterFunction];
                  }
                }
              }
            }
          }
        }
      }

      return schema;
    }

    const loading = ref(props.process ? false : true)
    const updating = ref(false)

    const UeForm = ref(null)
    const formActive = ref(false)
    const formSchema = ref(props.process ? formatSchema() : null)
    const formIsValid = computed(() => {
      return UeForm.value?.validModel ?? null
    })

    const setSchema = () => {
      formSchema.value = formatSchema()
    }

    const reason = ref('')
    const promptModalActive = ref(false)

    const hasProcessableEditing = computed(() => {
      let editingRoles = props.processableEditableRoles

      if(__isString(editingRoles) && editingRoles === '*'){
        return true
      }

      if(Array.isArray(editingRoles) && editingRoles.includes('*')){
        return true
      }

      return hasRoles(editingRoles)
    })

    const canShowProcessableDetails = (model) => {
      let statuses = props.processableShowStatuses

      if(__isString(statuses) && statuses === '*'){
        return true
      }

      if(Array.isArray(statuses) && statuses.includes('*')){
        return true
      }

      if(!model?.status){
        return false
      }

      const activeStatus = model.status

      if(__isObject(statuses)){
        for(const [status, roles] of Object.entries(statuses)){
          if(activeStatus === status){
            if(__isString(roles) && roles === '*'){
              return true
            }

            if(Array.isArray(roles) && roles.includes('*')){
              return true
            }

            if(hasRoles(roles)){
              return true
            }
          }
        }
      } else if(Array.isArray(statuses)){
        if(statuses.includes(model?.status)){
          return true
        }
      }

      return false
    }

    const showProcessableDetails = computed(() => {
      return canShowProcessableDetails(processModel.value)
    })

    const informationalMessage = computed(() => {
      if(processModel.value?.status){
        return processModel.value.status_informational_message
      }

      return t('The contents are being prepared or updated. Please check back later.')
    })

    const states = reactive({
      loading,
      updating,

      UeForm,
      formActive,
      // formSchema,
      formIsValid,

      // processModel,
      processableModel,
      processValid,
      processableValid,

      hasProcessableEditing,
      showProcessableDetails,
      informationalMessage,

      reason,
      promptModalActive,

      title: computed(() => {
        if(processModel.value?.processable){
          return _.get(processModel.value.processable, props.processableTitle, '')
        }

        return null
      }),
      flattenedProcessableDetails: computed(() => {
        const processable = processModel.value?.processable ?? {}

        let details = []

        for(const detail of props.processableDetails){
          if(__isset(detail.field) && __isset(detail.title)) {
            details.push({
              title: t(detail.title),
              value: _.get(processable, detail.field, ''),
            })
          }
        }

        return details
      }),

      bothFormAndProcessableValid: computed(() => {
        return processableValid.value && formIsValid.value
      }),
      onlyOneValid: computed(() => {
        return (processableValid.value && !formIsValid.value) || (!processableValid.value && formIsValid.value)
      }),
    })

    const methods = reactive({
      setSchema
    })

    return {
      ...useInput(props, { ...context, initializeInput, updateModelValue }),
      ...toRefs(states),
      formSchema,
      processModel,
      ...toRefs(methods),
    }
  },
  data: function () {
    return {

    }
  },
  watch: {
    input: {
      handler(val, old) {

      },
      immediate: true
    },
    processableModel: {
      handler(val) {
        if(!!val){
          this.$nextTick(() => {
            // __log('processableModel', val)
          })
        }
      },
      immediate: true
    }
  },
  methods: {
    fetchProcess() {
      if (this.input > 0 && !this.process) {
        const endpoint = this.fetchEndpoint.replace(':id', this.input);

        this.loading = true

        const self = this

        axios.get(endpoint)
          .then(response => {
            self.loading = false
            if(response.status === 200) {

              self.processModel = response.data
              self.processableModel = self.processModel?.processable ?? {}
              self.setSchema()

              self.$nextTick(async () => {
                if(self.UeForm){
                  await self.UeForm.validate()
                  await self.UeForm.VForm.resetValidation()
                  self.processableValid = self.UeForm.validModel
                }
              })
            }
          }).catch(error => {

          }).finally(() => {
            self.loading = false
          })
      }
    },
    async updateProcess(status) {

      if (this.input) {

        if(this.hasProcessableEditing){
          await this.UeForm.validate()
          const isValid = this.UeForm.validModel

          if(!isValid){

            this.$store.commit(ALERT.SET_ALERT, { message: this.$t('Please fill all the required fields'), variant: 'error', location: 'top', timeout: 2000 })

            return
          }
        }

        this.updating = true
        const endpoint = this.updateEndpoint.replace(':id', this.input);

        const data = {
          status: status,
          reason: this.reason
        }

        const self = this

        axios.put(endpoint, data).then(response => {
          if(response.status === 200) {
            self.promptModalActive = false
            self.reason = ''

            self.$store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
            self.fetchProcess()
          }
        }).finally(() => {
          self.updating = false
        })
      }
    },
    async updateProcessable() {

      if(this.input){

        let payload = {
          // ...this.processableModel,
          ...(this.$lodash.omit(this.UeForm.model, ['id']))
        }

        const endpoint = this.updateEndpoint.replace(':id', this.input);

        const self = this

        axios.put(endpoint, payload).then(response => {
          if(response.data.variant === 'success'){
            self.$store.commit(ALERT.SET_ALERT, { message: response.data.message, variant: response.data.variant })
            if(self.$refs.formModal && typeof self.$refs.formModal.close === 'function'){
              self.$refs.formModal.close()
            }
            self.fetchProcess()
          }
        })
      }


    },
    canAction(status) {
      return (!this.$isset(this.actionRoles[status]) || this.$hasRoles(this.actionRoles[status]))
    },
    confirmPrompt(status) {
      // this.promptModalActive = false
      this.updateProcess(status)
    }
  },
  created() {
    this.fetchProcess()
  }
}
</script>

<style lang="sass">
  .v-input-process
    .v-input__control
      height: 100%
</style>

<style lang="scss">

</style>
