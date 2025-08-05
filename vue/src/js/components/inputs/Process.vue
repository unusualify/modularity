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
      :color="status.card_color"
      :variant="status.card_variant"
      class="mb-4 w-100 h-100 d-flex flex-column"
    >
      <v-skeleton-loader v-if="loading" type="table-heading, list-item-three-line" class="mb-4 w-100 h-100" style="min-height: 300px;"></v-skeleton-loader>

      <template v-else-if="processModel">
        <v-card-text class="d-flex flex-column">

          <!-- Processable Title -->
          <v-row no-gutters class="pb-4">
            <v-col cols="12" sm="6" md="6" lg="8" xl="9" class="text-h5 font-weight-medium text-wrap">
              {{ title }}
            </v-col>
            <v-col cols="12" sm="6" md="6" lg="4" xl="3" class="d-flex justify-sm-end">
              <v-chip
                :prepend-icon="status.icon"
                :color="status.color"
                class="text-subtitle-1"
              >
                {{ status.label }}
              </v-chip>
            </v-col>
          </v-row>

          <v-spacer class="flex-grow-0"></v-spacer>

          <!-- Edit Button -->
          <v-row v-if="hasProcessableEditing">
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

        <v-card-actions v-if="$hasRoles(processEditableRoles)" class="px-4">
          <v-row>
            <v-col v-if="processModel && processModel.status" cols="12" class="text-center">
              <!-- Waiting for Confirmation -->
              <template v-if="processModel.status.match(/waiting_for_confirmation|waiting_for_reaction/) && canAction(processModel.status)">
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
                          @click="updateProcess('rejected')"
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
                  @click="confirmUpdateProcess('confirmed')"
                >
                  <v-icon left>mdi-check</v-icon>
                </v-btn>
              </template>

              <!-- Process Status Actions -->
              <template v-else-if="canAction(processModel.status)">
                <v-btn
                  :color="status.next_action_color"
                  variant="elevated"
                  :loading="updating"
                  :disabled="processModel.status === 'preparing' && !bothFormAndProcessableValid"
                  @click="confirmUpdateProcess(
                    processModel.status === 'preparing' ? 'waiting_for_confirmation' : 'waiting_for_reaction'
                  )"
                >
                  {{ status.next_action_label }}
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
import { ref, reactive, computed, toRefs, nextTick, onMounted } from 'vue'
import _ from 'lodash-es'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

import { useInput, makeInputProps, makeInputEmits, useAuthorization, useDynamicModal } from '@/hooks'
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
      type: [Array, Object],
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
    statusConfiguration: {
      type: Object,
      default: () => {},
      value: () => {
        return {
          preparing: {
            title: 'Preparing',
            icon: 'mdi-progress-clock',
            color: 'secondary',
            card_color: 'grey',
            card_variant: 'outlined',
            next_action_label: 'Preparing',
            next_action_color: 'secondary',
            dialog_title: 'Preparing',
            dialog_message: 'The contents are being prepared or updated. Please check back later.',
            response_message: 'The contents are being prepared or updated. Please check back later.',
          }
        }
      },
    },
    responseLocation: {
      type: String,
      default: 'top',
    },
  },
  setup (props, context) {
    const { t } = useI18n()
    const store = useStore()
    const { hasRoles } = useAuthorization()
    const DynamicModal = useDynamicModal()

    const initializeInput = (val) => {
      return val
    }

    const updateModelValue = (val) => {
      context.emit('update:modelValue', val)
    }

    const Input = useInput(props, { ...context, initializeInput, updateModelValue })

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

    const openDialog = (title, message, callback) => {
      DynamicModal.open(null, {
        'modalProps': {
          'widthType': 'md',
          'description': message,
          'title': title,
          'confirmText': t('Yes'),
          'cancelText': t('No'),
          'confirmCallback': async () => {
            await callback()
          }
        }
      })
    }

    const fetchProcess = () => {

      if (Input.input.value > 0 && !props.process) {
        const endpoint = props.fetchEndpoint.replace(':id', Input.input.value);

        loading.value = true

        axios.get(endpoint)
          .then(response => {
            loading.value = false
            if(response.status === 200) {

              processModel.value = response.data
              processableModel.value = processModel.value?.processable ?? {}
              setSchema()

              nextTick().then(async () => {
                if(UeForm.value){
                  await UeForm.value.validate()
                  await UeForm.value.VForm.resetValidation()
                  processableValid.value = UeForm.value.validModel
                }
              })
            }
          }).catch(error => {

          }).finally(() => {
            loading.value = false
          })
      }
    }

    const updateProcess = async (status) => {
      if (Input.input.value) {

        if(hasProcessableEditing.value){
          await UeForm.value.validate()
          const isValid = UeForm.value.validModel

          if(!isValid){

            store.commit(ALERT.SET_ALERT, { message: t('Please fill all the required fields'), variant: 'error', location: 'top', timeout: 2000 })

            return
          }
        }

        updating.value = true
        const endpoint = props.updateEndpoint.replace(':id', Input.input.value);

        const data = {
          status: status,
          reason: reason.value,
        }

        axios.put(endpoint, data).then(response => {
          if(response.status === 200) {
            promptModalActive.value = false
            reason.value = ''
            let newStatus = response.data.process_status

            let message = props.statusConfiguration?.[newStatus]?.response_message ?? response.data.message

            store.commit(ALERT.SET_ALERT, {
              message: message,
              variant: response.data.variant,
              location: props.responseLocation
            })

            fetchProcess()
          }
        }).finally(() => {
          updating.value = false
        })
      }
    }

    const confirmUpdateProcess = async (status) => {
      let title = t('Are you sure you want to update the process?')
      let message = t('This action cannot be undone.')

      if(props.statusConfiguration && props.statusConfiguration[status] && props.statusConfiguration[status].dialog_title){
        title = props.statusConfiguration[status].dialog_title
      }else if(processModel.value?.status_dialog_titles && processModel.value.status_dialog_titles[status]){
        title = processModel.value.status_dialog_titles[status]
      }

      if(props.statusConfiguration && props.statusConfiguration[status] && props.statusConfiguration[status].dialog_message){
        message = props.statusConfiguration[status].dialog_message
      }else if(processModel.value?.status_dialog_messages && processModel.value.status_dialog_messages[status]){
        message = processModel.value.status_dialog_messages[status]
      }

      openDialog(title, message, () => updateProcess(status))
    }

    const canAction = (status) => {
      return (!props.actionRoles[status] || hasRoles(props.actionRoles[status]))
    }

    const getProcessableEditableRoles = () => {
      let processableEditableRoles = props.processableEditableRoles

      if(__isString(processableEditableRoles) && processableEditableRoles === '*'){
        return ['*']
      }

      if(Array.isArray(processableEditableRoles) && processableEditableRoles.includes('*')){
        return ['*']
      }

      if(Array.isArray(processableEditableRoles)){
        return processableEditableRoles
      }

      if(__isObject(processableEditableRoles)){
        return processableEditableRoles[processModel.value?.status] ?? []
      }

      return []
    }

    const hasProcessableEditing = computed(() => {
      let editingRoles = getProcessableEditableRoles()

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

      status: computed(() => {
        const status = {
          label: '',
          icon: '',
          color: '',
          card_color: '',
          card_variant: 'outlined',
          next_action_label: '',
          next_action_color: '',
          dialog_title: '',
          dialog_message: '',
          response_message: null,
        }

        if(!processModel.value){
          return status
        }

        let process = processModel.value

        status.label = props.statusConfiguration?.[process.status]?.title ?? process.status_label
        status.icon = props.statusConfiguration?.[process.status]?.icon ?? process.status_icon
        status.color = props.statusConfiguration?.[process.status]?.color ?? process.status_color
        status.card_color = props.statusConfiguration?.[process.status]?.card_color ?? process.status_card_color ?? props.color
        status.card_variant = props.statusConfiguration?.[process.status]?.card_variant ?? process.status_card_variant ?? props.cardVariant
        status.next_action_label = props.statusConfiguration?.[process.status]?.next_action_label ?? process.next_action_label
        status.next_action_color = props.statusConfiguration?.[process.status]?.next_action_color ?? process.next_action_color
        status.dialog_title = props.statusConfiguration?.[process.status]?.dialog_title ?? process.status_dialog_title
        status.dialog_message = props.statusConfiguration?.[process.status]?.dialog_message ?? process.status_dialog_message

        status.response_message = props.statusConfiguration?.[process.status]?.response_message

        return status
      }),
    })

    const methods = reactive({
      setSchema,
      canAction,
      fetchProcess,
      updateProcess,
      confirmUpdateProcess,
    })

    onMounted(() => {
      fetchProcess()
    })

    return {
      ...Input,
      ...toRefs(states),
      formSchema,
      processModel,
      ...toRefs(methods),
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
  },
}
</script>

<style lang="sass">
  .v-input-process
    .v-input__control
      height: 100%
</style>

<style lang="scss">

</style>
