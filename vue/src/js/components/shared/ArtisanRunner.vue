<template>
  <v-card
    :elevation="elevation"
    :class="['artisan-runner-widget', cardClass]"
  >
    <v-card-title class="text-h6 d-flex align-center ga-2">
      <v-icon
        icon="mdi-console"
        size="small"
      />
      <span>{{ title }}</span>
    </v-card-title>

    <v-card-subtitle
      v-if="subtitle"
      class="pb-2"
    >
      {{ subtitle }}
    </v-card-subtitle>

    <v-card-text>
      <v-alert
        v-if="runnerDisabled"
        type="warning"
        variant="tonal"
        density="compact"
        border="start"
      >
        {{ $t('messages.artisan_runner_disabled_hint', 'Artisan Runner is turned off or you lack access.') }}
      </v-alert>

      <template v-else>
        <v-autocomplete
          v-model="selectedCommand"
          :items="commands"
          item-title="name"
          item-value="name"
          :loading="loadingCommands"
          :label="$t('messages.artisan_runner_select_command', 'Select artisan command')"
          density="comfortable"
          clearable
          hide-details="auto"
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
          v-if="errorMessage"
          class="text-error text-caption mt-2"
        >
          {{ errorMessage }}
        </div>
      </template>
    </v-card-text>
  </v-card>

  <v-dialog
    v-model="showParamsModal"
    max-width="720"
    scrollable
    persistent
  >
    <v-card>
      <v-card-title class="d-flex align-center ga-2">
        <span>{{ $t('messages.artisan_runner_parameters', 'Parameters') }}</span>
        <v-chip
          v-if="selectedCommand"
          size="small"
          variant="tonal"
        >
          {{ selectedCommand }}
        </v-chip>
        <v-spacer />
        <v-btn
          icon
          variant="text"
          :disabled="running"
          @click="closeParamsModal"
        >
          <v-icon icon="mdi-close" />
        </v-btn>
      </v-card-title>

      <v-card-subtitle
        v-if="definition?.description"
        class="pb-2"
      >
        {{ definition.description }}
      </v-card-subtitle>

      <v-card-text>
        <v-progress-linear
          v-if="loadingDefinition"
          indeterminate
          class="mb-4"
        />

        <template v-else-if="definition">
          <div
            v-if="!(definition.arguments?.length) && !(definition.options?.length)"
            class="text-body-2 text-medium-emphasis mb-4"
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
        </template>

        <v-divider
          v-if="output || running || exitCode !== null"
          class="my-4"
        />

        <div
          v-if="output || running || exitCode !== null"
          class="mb-2 d-flex align-center ga-2"
        >
          <span class="text-subtitle-2">
            {{ $t('messages.artisan_runner_output', 'Command output') }}
          </span>
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
            size="18"
            width="2"
          />
        </div>

        <pre
          v-if="output || running"
          class="artisan-runner-widget-output text-body-2"
        >{{ output || '…' }}</pre>

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

      <v-card-actions class="px-4 pb-4">
        <v-btn
          v-if="running"
          variant="text"
          color="error"
          @click="abortRun"
        >
          {{ $t('messages.cancel', 'Cancel') }}
        </v-btn>
        <v-spacer />
        <v-btn
          variant="text"
          :disabled="running"
          @click="closeParamsModal"
        >
          {{ $t('messages.close', 'Close') }}
        </v-btn>
        <v-btn
          color="primary"
          :loading="running"
          :disabled="!definition || loadingDefinition"
          @click="onRun"
        >
          {{ $t('messages.artisan_runner_run', 'Run command') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, onMounted, useAttrs } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAlert, useArtisanRunner } from '@/hooks'

const { t } = useI18n({ useScope: 'global' })
const { openAlert } = useAlert()

defineOptions({
  name: 'UeArtisanRunner',
  inheritAttrs: false,
})

const props = defineProps({
  title: {
    type: String,
    default: 'Artisan Runner',
  },
  subtitle: {
    type: String,
    default: null,
  },
  elevation: {
    type: [Number, String],
    default: 2,
  },
  runnerDisabled: {
    type: Boolean,
    default: false,
  },
  endpoints: {
    type: Object,
    default: () => ({
      commands: '',
      definition: '',
      run: '',
      answer: '',
    }),
  },
  isSuperadmin: {
    type: Boolean,
    default: false,
  },
})

const attrs = useAttrs()
const cardClass = computed(() => attrs.class)

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
} = useArtisanRunner(props.endpoints)

const showParamsModal = computed({
  get () {
    return Boolean(selectedCommand.value && (definition.value || loadingDefinition.value))
  },
  set (open) {
    if (!open) {
      closeParamsModal()
    }
  },
})

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

async function onCommandSelected (name) {
  if (!name) {
    await loadDefinition(null)
    return
  }
  await loadDefinition(name)
}

function closeParamsModal () {
  if (running.value) {
    abortRun()
  }
  selectedCommand.value = null
  loadDefinition(null)
}

async function onRun () {
  const ok = await runCommand()
  if (!ok && errorMessage.value) {
    openAlert({ message: String(errorMessage.value), variant: 'error' })
    return
  }
  if (exitCode.value === 0) {
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

onMounted(() => {
  if (!props.runnerDisabled) {
    loadCommands()
  }
})
</script>

<style scoped>
.artisan-runner-widget-output {
  margin: 0;
  max-height: 40vh;
  overflow: auto;
  white-space: pre-wrap;
  word-break: break-word;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  background: rgba(var(--v-theme-surface-variant), 0.12);
  border-radius: 8px;
  padding: 12px;
}
</style>
