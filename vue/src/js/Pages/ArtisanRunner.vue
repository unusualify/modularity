<template>
  <div class="pa-4">
    <v-breadcrumbs
      v-if="breadcrumbItems.length"
      class="px-0 pt-0 pb-2"
      density="compact"
    >
      <template v-for="(item, index) in breadcrumbItems" :key="index">
        <v-breadcrumbs-item
          :disabled="!!item.disabled"
          :class="{ 'text-primary cursor-pointer': isBreadcrumbClickable(item) }"
          @click="onBreadcrumbClick(item, $event)"
        >
          {{ item.title }}
        </v-breadcrumbs-item>
      </template>
    </v-breadcrumbs>

    <v-alert
      v-if="runnerDisabled"
      type="warning"
      variant="tonal"
      class="mb-6"
      border="start"
    >
      {{ $t('messages.artisan_runner_disabled_hint', 'Artisan Runner is turned off or you lack access. Set MODULAROUS_ARTISAN_RUNNER_ENABLED=true and ensure your role is in allowed_roles.') }}
    </v-alert>

    <template v-else>
      <v-alert
        type="info"
        variant="tonal"
        class="mb-6"
        border="start"
      >
        {{ introText }}
      </v-alert>

      <v-alert
        v-if="errorMessage"
        type="error"
        variant="tonal"
        class="mb-4"
        border="start"
        closable
        @click:close="errorMessage = null"
      >
        {{ errorMessage }}
      </v-alert>

      <v-row>
        <v-col
          cols="12"
          md="5"
        >
          <v-card class="mb-4">
            <v-card-title class="text-h6">
              {{ $t('messages.artisan_runner_command', 'Command') }}
            </v-card-title>
            <v-card-text>
              <v-autocomplete
                v-model="selectedCommand"
                :items="commands"
                item-title="name"
                item-value="name"
                :loading="loadingCommands"
                :label="$t('messages.artisan_runner_select_command', 'Select artisan command')"
                density="comfortable"
                clearable
                auto-select-first
                @update:model-value="onCommandSelected"
              >
                <template #item="{ props: itemProps, item }">
                  <v-list-item
                    v-bind="itemProps"
                    :subtitle="item.raw.description"
                  />
                </template>
              </v-autocomplete>
              <div
                v-if="definition?.description"
                class="text-body-2 text-medium-emphasis mt-2"
              >
                {{ definition.description }}
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col
          cols="12"
          md="7"
        >
          <v-card
            v-if="definition"
            class="mb-4"
          >
            <v-card-title class="text-h6">
              {{ $t('messages.artisan_runner_parameters', 'Parameters') }}
            </v-card-title>
            <v-card-text>
              <div
                v-if="!(definition.arguments?.length) && !(definition.options?.length)"
                class="text-body-2 text-medium-emphasis"
              >
                {{ $t('messages.artisan_runner_no_params', 'This command has no arguments or options.') }}
              </div>

              <template
                v-for="arg in definition.arguments || []"
                :key="`arg-${arg.name}`"
              >
                <v-textarea
                  v-model="formArguments[arg.name]"
                  class="mb-3"
                  :label="argLabel(arg)"
                  :hint="arg.description"
                  persistent-hint
                  :rules="arg.required ? [requiredRule] : []"
                  rows="2"
                  auto-grow
                />
              </template>

              <template
                v-for="opt in definition.options || []"
                :key="`opt-${opt.name}`"
              >
                <v-switch
                  v-if="opt.field === 'switch'"
                  v-model="formOptions[opt.name]"
                  class="mb-2"
                  color="primary"
                  density="compact"
                  :label="optLabel(opt)"
                  :hint="opt.description"
                  persistent-hint
                />
                <v-textarea
                  v-else
                  v-model="formOptions[opt.name]"
                  class="mb-3"
                  :label="optLabel(opt)"
                  :hint="opt.description"
                  persistent-hint
                  :rules="opt.required ? [requiredRule] : []"
                  rows="2"
                  auto-grow
                />
              </template>
            </v-card-text>
            <v-card-actions class="px-4 pb-4">
              <v-btn
                color="primary"
                :loading="running"
                :disabled="!selectedCommand || loadingDefinition"
                @click="confirmRun = true"
              >
                {{ $t('messages.artisan_runner_run', 'Run command') }}
              </v-btn>
              <v-btn
                v-if="running"
                variant="text"
                color="error"
                @click="abortRun"
              >
                {{ $t('messages.cancel', 'Cancel') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </template>

    <v-dialog
      v-model="confirmRun"
      max-width="480"
    >
      <v-card>
        <v-card-title>
          {{ $t('messages.artisan_runner_confirm_title', 'Run artisan command?') }}
        </v-card-title>
        <v-card-text>
          {{ $t('messages.artisan_runner_confirm_body', 'This executes the selected command on the server. Output will stream into a modal.') }}
          <div
            v-if="selectedCommand"
            class="mt-2 font-weight-medium"
          >
            php artisan {{ selectedCommand }}
          </div>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="confirmRun = false"
          >
            {{ $t('messages.cancel', 'Cancel') }}
          </v-btn>
          <v-btn
            color="warning"
            @click="onConfirmRun"
          >
            {{ $t('messages.confirm', 'Confirm') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog
      v-model="showOutputModal"
      max-width="900"
      scrollable
      persistent
    >
      <v-card>
        <v-card-title class="d-flex align-center ga-2">
          <span>{{ $t('messages.artisan_runner_output', 'Command output') }}</span>
          <v-chip
            v-if="exitCode !== null"
            size="small"
            :color="exitCode === 0 ? 'success' : 'error'"
          >
            exit {{ exitCode }}
          </v-chip>
          <v-progress-circular
            v-if="running"
            indeterminate
            size="20"
            width="2"
            class="ms-2"
          />
          <v-spacer />
          <v-btn
            icon
            variant="text"
            :disabled="running"
            @click="showOutputModal = false"
          >
            <v-icon icon="mdi-close" />
          </v-btn>
        </v-card-title>
        <v-card-text>
          <pre class="artisan-runner-output text-body-2">{{ output || '…' }}</pre>

          <v-card
            v-if="currentPrompt"
            class="mt-4"
            variant="tonal"
          >
            <v-card-title class="text-subtitle-1">
              {{ $t('messages.artisan_runner_prompt', 'Command prompt') }}
            </v-card-title>
            <v-card-text>
              <div class="mb-3">
                {{ currentPrompt.question }}
              </div>
              <template v-if="currentPrompt.type === 'confirm'">
                <v-switch
                  v-model="confirmPromptBool"
                  color="primary"
                  :label="$t('messages.artisan_runner_confirm_yes', 'Yes')"
                  density="compact"
                />
              </template>
              <template v-else-if="currentPrompt.type === 'choice'">
                <v-select
                  v-model="promptAnswer"
                  :items="currentPrompt.choices || []"
                  :label="$t('messages.artisan_runner_choice', 'Choice')"
                  density="comfortable"
                />
              </template>
              <template v-else>
                <v-text-field
                  v-model="promptAnswer"
                  :label="$t('messages.artisan_runner_answer', 'Answer')"
                  density="comfortable"
                />
              </template>
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn
                color="primary"
                @click="onSubmitPrompt"
              >
                {{ $t('messages.submit', 'Submit') }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            :disabled="running"
            @click="showOutputModal = false"
          >
            {{ $t('messages.close', 'Close') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import MainLayout from '@/Pages/Layouts/MainLayout.vue'
import { useAlert, useArtisanRunner } from '@/hooks'

const { t, te } = useI18n({ useScope: 'global' })
const { openAlert } = useAlert()
const inertiaPage = usePage()

defineOptions({
  name: 'ArtisanRunner',
  layout: (layoutH, page) => layoutH(MainLayout, () => page),
})

const props = defineProps({
  runnerDisabled: {
    type: Boolean,
    default: false,
  },
  runnerEndpoints: {
    type: Object,
    required: true,
  },
  isSuperadmin: {
    type: Boolean,
    default: false,
  },
})

const breadcrumbItems = computed(() => {
  const raw = inertiaPage.props.mainConfiguration?.navigation?.breadcrumbs
  return Array.isArray(raw) ? raw : []
})

function isBreadcrumbClickable (item) {
  return Boolean(item?.href) && !item?.disabled
}

function onBreadcrumbClick (item, e) {
  if (!isBreadcrumbClickable(item)) {
    return
  }
  e?.preventDefault()
  router.visit(item.href, { preserveScroll: true })
}

const introText = computed(() =>
  te('messages.artisan_runner_intro')
    ? t('messages.artisan_runner_intro')
    : (props.isSuperadmin
      ? 'Run allowlisted or any artisan command (superadmin). Required arguments are validated before execution; output streams live.'
      : 'Run allowlisted artisan commands. Required arguments are validated before execution; output streams live.')
)

const {
  commands,
  loadingCommands,
  loadingDefinition,
  running,
  selectedCommand,
  definition,
  formArguments,
  formOptions,
  output,
  exitCode,
  currentPrompt,
  promptAnswer,
  errorMessage,
  loadCommands,
  loadDefinition,
  runCommand,
  submitPromptAnswer,
  abortRun,
} = useArtisanRunner(props.runnerEndpoints)

const confirmRun = ref(false)
const showOutputModal = ref(false)

const confirmPromptBool = computed({
  get () {
    return ['1', 'true', 'yes', 'y', 'on'].includes(String(promptAnswer.value).toLowerCase().trim())
      || promptAnswer.value === true
  },
  set (value) {
    promptAnswer.value = value ? 'yes' : 'no'
  },
})

function requiredRule (value) {
  return (value !== null && value !== undefined && String(value).trim() !== '')
    || t('messages.required', 'Required')
}

function argLabel (arg) {
  return `${arg.name}${arg.required ? ' *' : ''}${arg.is_array ? ' (list)' : ''}`
}

function optLabel (opt) {
  const base = `--${opt.name}`
  if (opt.field === 'switch') {
    return base
  }
  return `${base}${opt.required ? ' *' : ''}${opt.is_array ? ' (list)' : ''}`
}

function onCommandSelected (name) {
  if (name) {
    loadDefinition(name)
  } else {
    loadDefinition(null)
  }
}

async function onConfirmRun () {
  confirmRun.value = false
  showOutputModal.value = true
  const ok = await runCommand()
  if (!ok && errorMessage.value) {
    openAlert({ message: String(errorMessage.value), variant: 'error' })
  } else if (exitCode.value === 0) {
    openAlert({ message: t('messages.artisan_runner_success', 'Command finished'), variant: 'success' })
  } else if (exitCode.value !== null) {
    openAlert({ message: t('messages.artisan_runner_failed', 'Command finished with errors'), variant: 'error' })
  }
}

async function onSubmitPrompt () {
  try {
    await submitPromptAnswer()
  } catch (e) {
    openAlert({
      message: e.response?.data?.message ?? e.message ?? 'Failed to submit answer',
      variant: 'error',
    })
  }
}

watch(exitCode, (code) => {
  if (code === null || running.value) {
    return
  }
})

onMounted(() => {
  if (!props.runnerDisabled) {
    loadCommands()
  }
})
</script>

<style scoped>
.artisan-runner-output {
  margin: 0;
  max-height: 50vh;
  overflow: auto;
  white-space: pre-wrap;
  word-break: break-word;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  background: rgba(var(--v-theme-surface-variant), 0.12);
  border-radius: 8px;
  padding: 12px;
}
</style>
