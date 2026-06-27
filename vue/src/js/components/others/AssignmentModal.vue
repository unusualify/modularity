<script setup>
  import { ref, computed } from 'vue'

  import {
    useValidation
  } from '@/hooks'

  const emit = defineEmits(['update:modelValue', 'update:form', 'submit'])

  const props = defineProps({
    modelValue: {
      type: Boolean,
      default: false,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    variant: {
      type: String,
      default: 'outlined',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    filepond: {
      type: Object,
      default: () => ({}),
    },
    form: {
      type: Object,
      default: () => ({}),
    },
    users: {
      type: Array,
      default: () => ([]),
    },
    minDueDays: {
      type: Number,
      default: 0,
    },
  })

  const modalActive = computed({
    get() {
      return props.modelValue
    },
    set(value) {
      emit('update:modelValue', value)
    },
  })

  const formModel = computed({
    get() {
      return props.form ?? {
        assignee_id: null,
        due_at: null,
        description: null,
        preliminaries: [],
      }
    },
    set(value) {
      emit('update:form', value)
    },
  })

  const {requiredRule, minRule, futureDateRule, dateRule, invokeRule} = useValidation(props)

  const createForm = ref(null)

  const validateForm = async () => {
    const isValid = await createForm.value.validate()

    return isValid
  }

  const preliminaryLoading = ref(false)
  const preliminariesFilepond = ref(null)

  defineExpose({
    validateForm,
  })
</script>

<template>
  <!-- Create Assignment Modal -->
  <ue-modal
    v-model="modalActive"
    widthType="md"

    persistent
    transition="scale-transition"
    validate-on="submit lazy"
  >
    <v-card>
      <v-card-text >
        <v-form
          id="createAssignmentForm"
          ref="createForm"
          class="d-flex flex-column"
          @submit.prevent="$emit('submit')"
        >
          <div class="d-flex justify-space-between gc-4">
            <v-select
              v-model="formModel.assignee_id"
              :items="users"
              :label="$t('Assignee')"
              :variant="variant"
              :return-object="false"
              :validate-on="`submit blur`"
              :rules="[requiredRule('classic', 1, undefined, $t('Assignee is required'))]"

              density="compact"
              item-title="name"
              item-value="id"
              class="w-50"
              required
              auto-select-first="exact"
            ></v-select>

            <v-input-date
              v-model="formModel.due_at"
              :variant="variant"
              :label="$t('Due Date')"
              :rules="[
                requiredRule('classic', 1, 1, $t('Pick a due date')),
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
            >
              <!-- <template v-slot:actions="{ save, cancel, isPristine }">
                sss
              </template> -->
            </v-input-date>

          </div>

          <div class="d-flex justify-space-between gc-4 mt-2">
            <v-textarea
              v-model="formModel.description"
              :variant="variant"
              :label="$t('Description')"
              density="compact"
              class="flex-grow-1"
              :rules="[
                requiredRule('classic', 1, 1, $t('Description is required')),
                minRule(10, $t('Description must be at least 10 characters'))
              ]"
              validate-on="input lazy"

            />
          </div>
          <div class="d-flex justify-space-between gc-4 mt-2">
            <v-input-filepond
              v-bind="invokeRule($lodash.omit(filepond, ['type']))"

              ref="preliminariesFilepond"
              v-model="formModel.preliminaries"
              density="compact"
              class="flex-grow-1"
              :variant="variant"
              :label="$t('fields.preliminary-documents')"

              @loadingFile="fileLoading = true"
              @loadedFile="preliminaryLoading = false"
              :disabled="preliminaryLoading"
            >

            <template v-slot:label="{ label }">
              <v-badge
                color="secondary"
                textColor="white"
                icon="mdi-creation"
                location="top left"
                floating
              >
                <span class="text-black">
                  {{ label }}
                </span>
              </v-badge>
            </template>

            </v-input-filepond>
          </div>

          <v-divider />

          <div class="d-flex justify-end gc-4">
            <v-btn-secondary
              class="mt-4"
              density="comfortable"
              variant="plain"
              :loading="loading"
              @click="modalActive = false"
            >
              {{ $t('Cancel') }}
            </v-btn-secondary>
            <v-btn
              class="mt-4"
              density="comfortable"
              type="submit"
              :loading="loading"
              :disabled="disabled || preliminaryLoading"
            >
              {{ $t('Assign') }}
            </v-btn>
          </div>
        </v-form>

      </v-card-text>
    </v-card>
  </ue-modal>
</template>
