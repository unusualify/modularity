<script setup>
  import { ref, computed, onMounted, watch } from 'vue'
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

  const createForm = ref(null)
  const createFormModalActive = ref(false)
  const createFormModel = ref({
    assignee_id: null,
    due_at: null,
    description: null,
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

      let title = `to <span class="text-blue-darken-1">${assigneeName}</span> &mdash; by <span class="text-success">${assignerName}</span>`

      let untilText = `${t('Until')}: <span class="font-weight-bold text-blue-darken-1"> ${d(new Date(assignment.due_at), 'medium')}</span>`
      let fromText = `${t('From')}: <span class="">${d(assignment.created_at ? new Date(assignment.created_at) : new Date(), 'medium')}</span>`

      let subtitle = `${assignment.description} </br> </br>`

      let subDescription = ""

      let appendInnerIcon = null

      subDescription = `${assignment.status_interval_description}`
      appendInnerIcon = assignment.status_vuetify_icon

      subDescription += ` ${fromText}`

      subtitle += subDescription
      acc.push({
        prependAvatar,
        assignerName,
        assigneeName,
        title,
        subtitle,
        subDescription,
        appendInnerIcon,

        attachments: assignment.attachments ?? [],
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
      const valid = await createForm.value.validate()

      if (!valid) {
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
              message: 'You have successfully assigned a task!',
              location: 'top',
              variant: 'success',
              ...response.data,
            })

            assignments.value.unshift(response.data)
            createFormModel.value = {
              assignee_id: null,
              due_at: null,
              description: null,
            }
            createFormModalActive.value = false
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
            <v-menu v-if="hasAssignment"
              :close-on-content-click="false"
              location="end"
              Xopen-on-hover
            >
              <template v-slot:activator="{ props }">
                <v-list v-if="(isAssignee || isAuthorized) && lastFormattedAssignment"
                  id="assigneeList"
                  :items="[{title: $t('Assignee'), prependAvatar: lastFormattedAssignment.prependAvatar ?? '', subtitle: lastFormattedAssignment.assigneeName}]"
                  lines="three"
                  item-props
                  class="pa-0 v-input-assignment__list--assignee flex-grow-1 flex-shrink-0"
                  color="primary"
                  v-bind="props"

                  variant="plain"
                >
                  <template v-slot:title="{ title }">
                    <div class="text-primary font-weight-bold" v-html="title"></div>
                  </template>
                  <template v-slot:subtitle="{ subtitle }">
                    <div class="font-weight-medium" v-html="subtitle"></div>
                  </template>
                </v-list>
              </template>
              <v-card min-width="300" max-width="500">

                <!-- Task Summary -->
                <v-list
                  id="assigneeList"
                  :items="[lastFormattedAssignment]"
                  lines="three"
                  item-props
                  class="pa-0 v-input-assignment__list--assignee"
                >
                  <template v-slot:title="{ title }">
                    <div v-html="title"></div>
                  </template>
                  <template v-slot:subtitle="subtitleScope">
                    <div v-html="lastFormattedAssignment.subDescription"></div>
                  </template>
                </v-list>

                <v-divider></v-divider>

                <v-list lines="10">
                  <!-- Last Assignment Description -->
                  <v-list-item
                    :title="$t('Description')"
                    :subtitle="lastAssignment.description"
                    class=""
                  >
                    <template v-slot:prepend="{ isSelected, select }">
                      <v-icon icon="mdi-information-outline"></v-icon>
                    </template>
                  </v-list-item>

                  <v-list-item
                    v-if="isAuthorized && !isAssignee && lastAssignment.attachments && lastAssignment.attachments.length > 0"
                    :title="$t('Files')"
                    subtitle="Files subtitle"
                  >
                    <template v-slot:prepend="{ isSelected, select }">
                      <v-icon icon="mdi-file-outline"></v-icon>
                    </template>
                    <template v-slot:subtitle="{ subtitle }">
                      <ue-filepond-preview :source="lastAssignment.attachments ?? []" show-inline-file-name image-size="24"/>
                    </template>

                  </v-list-item>
                </v-list>

                <v-card-text v-if="isAssignee">
                  <v-input-filepond
                    v-if="filepond"
                    label="Files"
                    ref="inputFilepond"
                    v-bind="invokeRule($lodash.omit(filepond, ['type']))"
                    v-model="attachments"

                    :xmodelValue="attachments"
                    @xupdate:modelValue="$log('update:modelValue', $event)"
                    @loadingFile="attachmentsLoading = true"
                    @loadedFile="attachmentsLoading = false"
                  >
                  </v-input-filepond>
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions v-if="isAssignee">
                  <v-btn
                    variant="tonal"
                    color="success"
                    @click="openCompleteModal"
                  >
                    Complete
                  </v-btn>

                  <v-spacer></v-spacer>
                  <v-btn
                    color="primary"
                    variant="tonal"
                    @click="attachments.length > 0 && updateAssignment({
                      attachments: attachments
                    })"
                    :loading="attachmentsLoading || updating"
                    :disabled="attachments.length < 1"
                  >
                    Save
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-menu>

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
                height="450"
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
                        <div class="w-100" style="word-break: break-word;white-space: pre-wrap;" v-html="subtitle"></div>
                        <ue-filepond-preview class="my-2" v-if="item.attachments && item.attachments.length > 0" :source="item.attachments" show-inline-file-name image-size="24"/>

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
          <ue-modal
            ref="createFormModal"
            v-model="createFormModalActive"
            widthType="md"

            persistent
            transition="scale-transition"
          >
            <v-card>
              <v-card-text >
                <v-form
                  id="createAssignmentForm"
                  ref="createForm"
                  class="d-flex flex-column"
                  @submit.prevent="createAssignment"
                >
                  <div class="d-flex justify-space-between gc-4">
                    <v-select
                      v-model="createFormModel.assignee_id"
                      :items="items"

                      :label="label"
                      :variant="variant"

                      density="compact"
                      item-title="name"
                      item-value="id"
                      :return-object="false"
                      :validate-on="`submit blur`"
                      class="w-50"

                      :rules="[requiredRule('classic', 1, undefined, 'Assignee is required')]"

                      required

                      auto-select-first="exact"
                    ></v-select>

                    <v-input-date
                      v-model="createFormModel.due_at"
                      :variant="variant"
                      :label="$t('Due Date')"
                      :rules="[
                        requiredRule('classic', 1, 1, 'Pick a due date'),
                        dateRule(),
                        futureDateRule(minDueDays, 'days')
                      ]"
                      :validate-on="`submit blur`"

                      class="w-50"

                      density="compact"
                      prepend-icon=""
                      append-inner-icon="$calendar"
                      persistent-placeholder
                      show-adjacent-months
                      show-week
                      required

                      Xmultiple="4"
                      Ymultiple="range"

                    >
                      <!-- <template v-slot:actions="{ save, cancel, isPristine }">
                        sss
                      </template> -->
                    </v-input-date>

                  </div>

                  <div class="d-flex justify-space-between gc-4 mt-2">
                    <v-textarea
                      v-model="createFormModel.description"
                      :variant="variant"
                      :label="$t('Description')"

                      density="compact"

                      class="flex-grow-1"

                      :rules="[
                        requiredRule('classic', 1, 1, 'Description is required'),
                        minRule(10, 'Description must be at least 10 characters')
                      ]"

                      :validate-on="`input blur`"

                    />
                  </div>

                  <v-divider />

                  <div class="d-flex justify-end gc-4">
                    <v-btn-secondary
                      class="mt-4"
                      density="comfortable"
                      variant="plain"
                      :loading="updating"
                      @click="createFormModalActive = false"
                    >
                      {{ $t('Cancel') }}
                    </v-btn-secondary>
                    <v-btn
                      class="mt-4"
                      density="comfortable"
                      type="submit"
                      :loading="updating"
                      :disabled="!input || updating || (createForm && !createForm.isValid)"
                    >
                      {{ $t('Assign') }}
                    </v-btn>
                  </div>
                </v-form>

              </v-card-text>
            </v-card>
          </ue-modal>
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
