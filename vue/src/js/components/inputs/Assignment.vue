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
          <div class="d-flex gc-4 align-center">

            <!-- Assignee Details -->
            <v-list v-if="(isAssignee || isAuthorized) && lastAssignment"
              id="assigneeList"
              :items="[lastFormattedAssignment]"
              lines="three"
              item-props
              class="pa-0 v-input-assignment__list--assignee"
            >
              <template v-slot:prepend="{ prependAvatar }" v-if="isAssignee">
                <v-menu
                  open-on-hover
                >
                  <template v-slot:activator="{ props }">
                    <v-icon
                      icon="mdi-folder-information-outline"
                      color="warning"
                      size="large"
                      v-bind="props"
                    />
                  </template>
                  <v-card>
                    <v-card-text>
                      <v-list>
                        <v-list-item>
                          {{ lastAssignment.description }}
                        </v-list-item>
                      </v-list>
                    </v-card-text>
                    <v-card-actions v-if="lastAssignment.status !== 'completed'">
                      <ue-modal
                        v-model="completeModal"
                        widthType="md"

                        :descriptionText="$t('Are you sure you want to complete this assignment?')"
                        :confirmText="$t('Yes')"
                        :cancelText="$t('No')"

                        :confirmLoading="updating"
                        :rejectLoading="updating"

                        :confirmCallback="() => {
                          return this.updateAssignmentStatus('completed')
                        }"

                        @confirm="completeModal = false"
                      >
                        <template v-slot:activator="modalActivatorScope">
                          <v-btn
                            variant="flat"
                            color="success"
                            block
                            v-bind="modalActivatorScope.props"
                            >
                            {{ $t('Completed') }}
                          </v-btn>
                        </template>
                      </ue-modal>
                    </v-card-actions>
                  </v-card>
                </v-menu>
              </template>
              <template v-slot:title="{ title }">
                <div v-html="title"></div>
              </template>
              <template v-slot:subtitle="subtitleScope">
                <div v-html="lastFormattedAssignment.subDescription"></div>
              </template>
            </v-list>

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
                    class="flex-grow-0 my-2"
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
                v-if="assignments.length > 0"
                v-model="listAssignmentsModalActive"
                widthType="md"
                transition="scroll-y-reverse-transition"
                scrollable
                height="450"
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
                        class="flex-grow-0 my-2"

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
                <v-list
                  :items="formattedAssignments"
                  lines="three"
                  item-props
                >
                <template v-slot:title="{ title }">
                  <div v-html="title"></div>
                </template>
                <template v-slot:subtitle="{ subtitle }">
                  <div v-html="subtitle"></div>
                </template>
                <template v-slot:append="appendScope" >
                  <ue-dynamic-component-renderer
                    :subject="appendScope.item.appendInnerIcon"
                  />
                </template>
                </v-list>
              </ue-modal>
            </template>

          </div>

          <!-- Create Assignment Modal -->
          <ue-modal
            ref="createFormModal"
            v-model="createFormModalActive"
            widthType="md"

            :persistent="updating"
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
                    <v-combobox
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
                    ></v-combobox>

                    <v-date-input
                      v-model="createFormModel.due_at"
                      :variant="variant"
                      :label="$t('Due Date')"
                      :rules="[
                        requiredRule('classic', 1, 1, 'Pick a due date'),
                        dateRule(),
                        futureDateRule(minimumDaysFutureDateRule, 'days')
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
                    </v-date-input>

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

                      :validate-on="`submit blur`"

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
                      :disabled="!this.input || updating"
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

<script>
import { useInput, makeInputProps, makeInputEmits, useValidation, useFormatter } from '@/hooks'

export default {
  name: 'v-input-assignment',
  emits: [...makeInputEmits],
  components: {

  },
  props: {
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
      default: [],
    },
    // assignments: {
    //   type: Array,
    //   default: null,
    // },

    fetchEndpoint: {
      type: String,
      default: null,
    },
    createEndpoint: {
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
      default: ['superadmin', 'admin'],
    },

    minimumDaysFutureDateRule: {
      type: Number,
      default: 6,
    },
  },
  setup (props, context) {
    const {requiredRule, minRule, futureDateRule, dateRule} = useValidation(props)

    return {
      ...useInput(props, context),
      requiredRule,
      minRule,
      dateRule,
      futureDateRule,
    }
  },
  data: function () {
    return {
      loading: false,
      updating: false,
      assignable_id: this.input,
      assignees: [],
      assignee_options: [],

      assignments: [],

      listAssignmentsModalActive: false,

      createFormModalActive: false,
      createFormModel: {
        assignee_id: null,
        due_at: null,
        description: null,
      },

      completeModal: false,

    }
  },
  computed: {
    isAuthorized() {
      return this.$hasRoles(this.authorizedRoles)
    },
    isAssignee() {
      return this.lastAssignment && this.$isYou(this.lastAssignment.assignee_id)
    },
    lastAssignment() {
      return this.assignments.length > 0 ? this.assignments[0] : null
    },
    formattedAssignments() {
      let formattedAssignments = []

      if(this.assignments.length > 0) {
        formattedAssignments.push({ type: 'subheader', title: this.$t('Assignments')})
      }
      return this.assignments.reduce((acc, assignment, index) => {
        let prependAvatar = assignment.assignee_avatar

        let assignerName = this.$isYou(assignment.assigner_id) ? this.$t('You') : assignment.assigner_name
        let assigneeName = this.$isYou(assignment.assignee_id) ? this.$t('You') : assignment.assignee_name

        let title = `to <span class="text-blue-darken-1">${assigneeName}</span> &mdash; by <span class="text-success">${assignerName}</span>`

        let untilText = `${this.$t('Until')}: <span class="font-weight-bold text-blue-darken-1"> ${this.$d(new Date(assignment.due_at), 'medium')}</span>`
        let fromText = `${this.$t('From')}: <span class="text-warning">${this.$d(new Date(assignment.created_at), 'medium')}</span>`

        let subtitle = `${assignment.description} </br> </br>`

        let subDescription = ""

        let appendInnerIcon = null

        if(assignment.status === 'completed') {
          subDescription += `${this.$t('Completed')}: <span class="text-success">${this.$d(new Date(assignment.completed_at), 'medium')}</span>`
          appendInnerIcon = "<v-icon icon='mdi-check-circle-outline' color='success'/>"
        } else if(assignment.status === 'accepted') {
          subDescription += `${this.$t('Accepted')}: <span class="text-warning">${this.$d(new Date(assignment.accepted_at), 'medium')}</span>`
        } else if(assignment.status === 'cancelled') {
          subDescription += `${this.$t('Cancelled')}: <span class="font-weight-bold text-error">${this.$d(new Date(assignment.updated_at), 'medium')}</span>`
          appendInnerIcon = "<v-icon icon='mdi-close-circle-outline' color='error'/>"
        } else{

          subDescription += `${untilText}`
          appendInnerIcon = "<v-icon icon='mdi-clock-outline' color='info'/>"
        }

        subDescription += ` ${fromText}`

        subtitle += subDescription
        acc.push({
          prependAvatar,
          title,
          subtitle,
          subDescription,
          appendInnerIcon,
          // description: assignment.due_at,
        })

        if(index !== this.assignments.length - 1) {
          acc.push({
            type: 'divider',
            inset: true,
          })
        }
        return acc
      }, formattedAssignments)
    },
    lastFormattedAssignment() {
      return this.formattedAssignments.length > 0 ? this.formattedAssignments[1] : null
    },
  },
  watch: {

  },
  methods: {

    async fetchAssignments() {

      if (this.input) {
        const endpoint = this.fetchEndpoint.replace(':id', this.input);

        this.loading = true

        const self = this

        axios.get(endpoint)
          .then(response => {
            if(response.status === 200) {
              self.assignments = response.data
            }
          }).finally(() => {
            self.loading = false
          })
      }
    },
    async createAssignment() {

      if (this.input) {
        const valid = await this.$refs.createForm.validate()

        if (!valid) {
          return
        }

        const payload = {
          ...this.createFormModel,
          assignee_type: this.assigneeType,
          assignable_id: this.input,
          assignable_type: this.assignableType
        }

        this.updating = true
        const self = this

        this.assignableRequest(
          payload,
          (response) => {
            if(response.status === 200) {
              self.assignments.unshift(response.data)
              self.createFormModel = {
                assignee_id: null,
                due_at: null,
                description: null,
              }
            }
          }, (error) => {
            __log(error)
          }, () => {
            self.updating = false
          }
        )

      }
    },
    async updateAssignmentStatus(newStatus) {
      if (this.input !== null) {

        const payload = {
          status: newStatus
        }

        this.updating = true
        this.loa
        const self = this

        let res = await this.assignableRequest(
          payload,
          (response) => { // successCallback
            if(response.status === 200) {
              this.$notif({
                message: 'Assignment updated successfully',
                ...response.data,
              })

              if(response.data.assignments ) {
                self.assignments = response.data.assignments
              }else{
                self.fetchAssignments()
              }
            }
          }, (error) => { // errorCallback
            __log(error)
          }, () => {
            self.updating = false
            self.completeModal = false
          }
        )

        return res
      }

      return false
    },

    async assignableRequest(payload, successCallback = null, errorCallback = null, finallyCallback = null) {
      const endpoint = this.createEndpoint.replace(':id', this.input);

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
    },
  },
  created() {
    this.fetchAssignments()
  }
}
</script>

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
