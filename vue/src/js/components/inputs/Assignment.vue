<script setup>
  import { ref, computed, onMounted, watch, nextTick } from 'vue'
  import { useI18n } from 'vue-i18n'
  import {
    useInput,
    makeInputProps,
    makeInputEmits,
    useValidation,
    useDynamicModal,
    useAuthorization,
    useAlert
  } from '@/hooks'
  import axios from 'axios'

  import AssigneeDetails from '@/components/others/AssigneeDetails.vue'
  import AssignmentModal from '@/components/others/AssignmentModal.vue'

  const props = defineProps({
    ...makeInputProps(),
    variant: {
      type: String,
      default: 'outlined',
    },
    density: {
      type: String,
      default: 'default',
    },
    items: {
      type: Array,
      default: () => [],
    },
    fetchEndpoint: {
      type: String,
      default: null,
    },
    saveEndpoint: {
      type: String,
      default: null,
    },
    assignableType: {
      type: String,
      default: null,
    },
    assigneeType: {
      type: String,
      default: null,
    },
    authorizedRoles: {
      type: Array,
      default: () => ['superadmin', 'admin'],
    },
    minDueDays: {
      type: Number,
      default: 0,
    },
    filepond: {
      type: Object,
      default: null,
    },
    // renders assignment descriptions with ue-well-print (line breaks, links) instead of plain text
    wellPrint: {
      type: Boolean,
      default: false,
    },
  })

  const emit = defineEmits([...makeInputEmits])

  const { input, id , boundProps } = useInput(props, { emit })
  const {requiredRule, minRule, futureDateRule, dateRule, invokeRule} = useValidation(props)
  const { t, d } = useI18n()
  const DynamicModal = useDynamicModal()
  const Authorization = useAuthorization()
  const Alert = useAlert()

  const loading = ref(false)
  const updating = ref(false)
  const assignable_id = ref(props.modelValue)
  const assignees = ref([])
  const assignee_options = ref([])

  const assignments = ref([])

  const listAssignmentsModalActive = ref(false)

  const createFormModal = ref(null)
  const createFormModalActive = ref(false)
  const createFormModel = ref({
    assignee_id: null,
    due_at: null,
    description: null,
    preliminaries: [],
  })
  const completeModal = ref(false)
  const attachments = ref([])
  const attachmentsLoading = ref(false)

  const isAuthorized = computed(() => {
    return Authorization.hasRoles(props.authorizedRoles)
  })
  const lastAssignment = computed(() => {
    return assignments.value.length > 0 ? assignments.value[0] : null
  })
  const hasAssignment = computed(() => {
    return assignments.value.length > 0
  })
  const isAssignee = computed(() => {
    return lastAssignment.value && Authorization.isYou(lastAssignment.value.assignee_id)
  })

  const canView = computed(() => {
    return isAuthorized.value && isAssignee.value
  })

  const formattedAssignments = computed(() => {
    let formatteds = []

    return assignments.value.reduce((acc, assignment, index) => {
      let prependAvatar = assignment.assignee_avatar ?? ''

      let assignerName = Authorization.isYou(assignment.assigner_id) ? t('You') : assignment.assigner_name
      let assigneeName = Authorization.isYou(assignment.assignee_id) ? t('You') : assignment.assignee_name

      // let title = `to <span class="text-blue-darken-1">${assigneeName}</span> &mdash; by <span class="text-success">${assignerName}</span>`
      let title = t('messages.assignment.task-to-assignee-by-assigner', {  assigneeName: `<span class="text-blue-darken-1">${assigneeName}</span>`, assignerName: `<span class="text-success">${assignerName}</span>` })

      let untilText = `${t('Until')}: <span class="font-weight-bold text-blue-darken-1"> ${d(new Date(assignment.due_at), 'medium')}</span>`
      let fromText = `${t('From')}: <span class="">${d(assignment.created_at ? new Date(assignment.created_at) : new Date(), 'medium')}</span>`

      let subDescription = ""

      let appendInnerIcon = null

      subDescription = `${assignment.status_interval_description}`
      appendInnerIcon = assignment.status_vuetify_icon

      subDescription += ` ${fromText}`

      // with wellPrint, description is rendered separately by ue-well-print, so it stays as the raw text the user typed
      let subtitle = props.wellPrint
        ? subDescription
        : `${assignment.description} </br> </br>${subDescription}`

      acc.push({
        prependAvatar,
        assignerName,
        assigneeName,
        title,
        description: assignment.description,
        subtitle,
        subDescription,
        appendInnerIcon,

        attachments: assignment.attachments ?? [],
        preliminaries: assignment.preliminaries ?? [],
      })

      if(index !== assignments.value.length - 1) {
        acc.push({
          type: 'divider',
          inset: true,
        })
      }
      return acc
    }, formatteds)
  })

  const lastFormattedAssignment = computed(() => {
    return formattedAssignments.value.length > 0 ? formattedAssignments.value[0] : null
  })

  watch(() => assignments.value, (newVal) => {
    if(newVal && newVal.length > 0) {
      attachments.value = newVal[0].attachments ?? []
    }
  })

  const saveRequest = async (payload, successCallback = null, errorCallback = null, finallyCallback = null) => {
    const endpoint = props.saveEndpoint.replace(':id', input.value);

    axios.post(endpoint, payload)
      .then(response => {
        if(successCallback) {
          successCallback(response)
        }
      })
      .catch(error => {
        if(errorCallback) {
          errorCallback(error)
        }
      })
      .finally(() => {
        if(finallyCallback) {
          finallyCallback()
        }
      })

    return false
  }

  const fetchAssignments = async () => {
    if (input.value) {
      const endpoint = props.fetchEndpoint.replace(':id', input.value);

      loading.value = true

      axios.get(endpoint)
        .then(response => {
          if(response.status === 200) {
            assignments.value = response.data
          }
        }).finally(() => {
          loading.value = false
        })
    }
  }

  const createAssignment = async () => {
    if (input.value) {
      const valid = await createFormModal.value.validateForm()

      if (!valid || !valid.valid) {
        return
      }

      const payload = {
        ...createFormModel.value,
        assignee_type: props.assigneeType,
        assignable_id: input.value,
        assignable_type: props.assignableType
      }

      updating.value = true

      saveRequest(
        payload,
        (response) => {
          if(response.status === 200) {
            Alert.openAlert({
              message: t('You have successfully assigned a task!'),
              location: 'top',
              variant: 'success',
              ...response.data,
            })

            assignments.value.unshift(response.data)
            createFormModalActive.value = false

            nextTick(() => {
              createFormModel.value = {
                assignee_id: null,
                due_at: null,
                description: null,
              }
            })
          }
        }, (error) => {
          __log(error)
        }, () => {
          updating.value = false
        }
      )
    }
  }

  const updateAssignment = async (payload) => {
    if (input.value !== null) {
      updating.value = true

      let res = await saveRequest(
        payload,
        (response) => { // successCallback
          if(response.status === 200) {
            Alert.openAlert({
              message: 'Assignment updated successfully',
              ...response.data,
            })

            if(response.data.assignments ) {
              assignments.value = response.data.assignments
            }else{
              fetchAssignments()
            }
            DynamicModal.close()
          }
        }, (error) => { // errorCallback
          __log(error)
        }, () => {
          updating.value = false
        }
      )

      return res
    }

    return false
  }

  const openCompleteModal = () => {
    DynamicModal.open(null, {
      'modalProps': {
        'widthType': 'md',
        'description': t('Are you sure you want to complete this task?'),
        'title': t('Complete Task'),
        'confirmText': t('Yes'),
        'cancelText': t('No'),

        'confirmLoading': updating,
        'rejectLoading': updating,
        'confirmCallback': async () => {
          await updateAssignment({
            status: 'completed'
          })
        }
      }
    })
  }

  onMounted(() => {
    fetchAssignments()
  })
</script>

<template>
  <v-input
    v-model="input"
    :variant="boundProps.variant"
    hide-details
    class="v-input-assignment"
    >
    <template v-slot:default="defaultSlot">
      <div class="w-100">
        <v-skeleton-loader v-if="loading"
          type="list-item-two-line"
        />
        <template v-else>
          <div class="d-flex flex-wrap gc-4">

            <!-- Assignee Details -->
            <AssigneeDetails v-if="hasAssignment"
              v-model:attachments="attachments"
              v-model:attachmentsLoading="attachmentsLoading"
              :loading="updating"

              :isAssignee="isAssignee"
              :isAuthorized="isAuthorized"
              :assignment="lastAssignment"
              :formattedAssignment="lastFormattedAssignment"
              :filepond="filepond"
              :wellPrint="wellPrint"
              @click:complete="openCompleteModal"
              @click:save="updateAssignment"
            />

            <template v-if="isAuthorized">
              <!-- Create Assignment -->
              <v-tooltip
                location="top"
              >
                <template v-slot:activator="{ props }">
                  <v-btn
                    id="createAssignmentBtn"
                    icon="mdi-account-reactivate"
                    size="default"
                    rounded
                    color="success"
                    density="compact"
                    class="flex-grow-0 flex-shrink-1"
                    v-bind="props"
                    :disabled="!input || updating"
                    :loading="updating"
                    @click="createFormModalActive = true"
                  />
                </template>
                {{ $t('Assign') }}
              </v-tooltip>

              <!-- List Assignments -->
              <ue-modal
                v-if="hasAssignment"
                v-model="listAssignmentsModalActive"
                widthType="md"
                transition="scroll-y-reverse-transition"
                scrollable
                height="600"
                :title="$t('Task History')"
                has-close-button
                has-title-divider
                no-default-body-padding
                no-actions
              >
                <template v-slot:activator="modalActivatorScope">
                  <v-tooltip
                    location="top"
                  >
                    <template v-slot:activator="tooltipActivatorScope">
                      <v-btn
                        id="showHistoryBtn"
                        icon="mdi-clipboard-list-outline"
                        size="default"
                        rounded
                        color="info"
                        density="compact"
                        class="flex-grow-0 flex-shrink-1"

                        :disabled="!input || updating"
                        :loading="updating"

                        v-bind="{
                          ...modalActivatorScope.props,
                          ...tooltipActivatorScope.props,
                        }"
                      />
                    </template>
                    {{ $t('Show History') }}
                  </v-tooltip>
                </template>
                <template v-slot:body.description>
                  <div>
                    <v-list
                      class="pb-4 flex-1-0"
                      :items="formattedAssignments"
                      lines="ten"
                      item-props
                    >
                      <template v-slot:title="{ title }">
                        <div v-html="title"></div>
                      </template>
                      <template v-slot:subtitle="{ item, subtitle }">
                        <ue-well-print v-if="wellPrint" :text="item.description" class="w-100 mb-2" style="word-break: break-word;" />
                        <div class="w-100" style="word-break: break-word;white-space: pre-wrap;" v-html="subtitle"></div>
                        <v-expansion-panels class="my-2">
                          <v-expansion-panel v-if="item.preliminaries && item.preliminaries.length > 0">
                            <v-expansion-panel-title>{{ $t('fields.preliminary-documents') }}</v-expansion-panel-title>
                            <v-expansion-panel-text>
                              <ue-filepond-preview :source="item.preliminaries" show-inline-file-name image-size="24"/>
                            </v-expansion-panel-text>
                          </v-expansion-panel>
                          <v-expansion-panel v-if="item.attachments && item.attachments.length > 0">
                            <v-expansion-panel-title>{{ $t('Attachments') }}</v-expansion-panel-title>
                            <v-expansion-panel-text>
                              <ue-filepond-preview :source="item.attachments" show-inline-file-name image-size="24"/>
                            </v-expansion-panel-text>
                          </v-expansion-panel>
                        </v-expansion-panels>
                        <!-- <ue-filepond-preview class="my-2" v-if="item.attachments && item.attachments.length > 0" :source="item.attachments" show-inline-file-name image-size="24"/> -->

                      </template>
                      <template v-slot:append="appendScope" >
                        <ue-dynamic-component-renderer
                          :subject="appendScope.item.appendInnerIcon"
                        />
                      </template>
                    </v-list>
                  </div>
                </template>
              </ue-modal>
            </template>

          </div>

          <!-- Create Assignment Modal -->
          <AssignmentModal
            ref="createFormModal"
            v-model="createFormModalActive"
            :form="createFormModel"
            :variant="variant"
            :users="items"
            :minDueDays="minDueDays"
            :filepond="filepond"

            :loading="updating"
            :disabled="!input || updating"

            @submit="createAssignment"
          />

        </template>
      </div>
    </template>
  </v-input>
</template>

<style lang="scss">
  .v-input-assignment {
    min-height: 60px;

    .v-input-assignment__list--assignee {
      .v-list-item {
        padding: 0 !important;
        min-height: 60px;
      }
    }
  }
</style>
