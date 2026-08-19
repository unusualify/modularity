<template>
  <v-card
    :elevation="elevation"
    :class="['system-console-widget', 'd-flex', 'flex-column', 'flex-grow-1', 'min-height-0', cardClass]"
  >
    <v-card-title class="text-title-large d-flex align-center ga-2">
      <v-icon
        icon="mdi-cog-outline"
        size="small"
      />
      <span>{{ title }}</span>
      <v-spacer />
      <v-chip
        size="small"
        :color="maintenanceModeLocal ? 'warning' : 'success'"
        variant="tonal"
      >
        {{ maintenanceModeLocal
          ? $t('messages.system_console_maintenance_on', 'Maintenance ON')
          : $t('messages.system_console_live', 'Live') }}
      </v-chip>
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
        class="mb-4"
      >
        {{ $t('messages.artisan_runner_disabled_hint', 'Artisan Runner is turned off or you lack access.') }}
      </v-alert>

      <template v-else>
        <div class="text-title-small mb-2">
          {{ $t('messages.system_console_maintenance', 'Maintenance mode') }}
        </div>

        <v-select
          v-model="selectedPresetKey"
          :items="uniqueDownPresets"
          item-title="label"
          item-value="key"
          :label="$t('messages.system_console_down_preset', 'Down preset')"
          density="comfortable"
          hide-details="auto"
          class="mb-2"
          :disabled="running || maintenanceModeLocal"
        />

        <div
          v-if="selectedPresetOptionChips.length"
          class="d-flex flex-wrap ga-2 mb-3"
        >
          <v-chip
            v-for="chip in selectedPresetOptionChips"
            :key="chip.key"
            size="small"
            variant="tonal"
            color="default"
          >
            <span class="text-medium-emphasis me-1">{{ chip.label }}:</span>
            <span>{{ chip.value }}</span>
          </v-chip>
        </div>
        <div
          v-else
          class="text-body-small text-medium-emphasis mb-3"
        >
          {{ $t('messages.system_console_down_options_empty', 'No down options resolved for this preset.') }}
        </div>

        <div class="d-flex flex-wrap ga-2 mb-6">
          <v-btn
            color="warning"
            variant="tonal"
            :loading="running && activeAction === 'down'"
            :disabled="running || maintenanceModeLocal || !selectedPresetKey"
            prepend-icon="mdi-weather-night"
            @click="requestDown"
          >
            {{ $t('messages.system_console_enable_maintenance', 'Enable maintenance') }}
          </v-btn>
          <v-btn
            color="success"
            variant="tonal"
            :loading="running && activeAction === 'up'"
            :disabled="running || !maintenanceModeLocal"
            prepend-icon="mdi-weather-sunny"
            @click="runUp"
          >
            {{ $t('messages.system_console_disable_maintenance', 'Bring application up') }}
          </v-btn>
        </div>

        <v-divider class="mb-4" />

        <div class="text-title-small mb-2">
          {{ $t('messages.system_console_cache', 'Cache & optimize') }}
        </div>

        <div class="d-flex flex-wrap ga-2">
          <v-btn
            v-for="cmd in uniqueCacheCommands"
            :key="cmd.command"
            :color="cmd.color || 'primary'"
            :variant="cmd.variant || 'tonal'"
            size="small"
            :prepend-icon="cmd.icon || undefined"
            :loading="running && activeAction === cmd.command"
            :disabled="running"
            @click="requestCacheCommand(cmd)"
          >
            {{ cmd.label }}
          </v-btn>
        </div>

        <div
          v-if="errorMessage"
          class="text-error text-body-small mt-3"
        >
          {{ errorMessage }}
        </div>
      </template>
    </v-card-text>
  </v-card>

  <v-dialog
    v-model="showConfirm"
    max-width="480"
    persistent
  >
    <v-card>
      <v-card-title class="text-title-large">
        {{ $t('messages.confirm', 'Confirm') }}
      </v-card-title>
      <v-card-text>
        {{ confirmMessage }}
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          variant="text"
          @click="showConfirm = false"
        >
          {{ $t('messages.cancel', 'Cancel') }}
        </v-btn>
        <v-btn
          color="primary"
          @click="confirmPendingAction"
        >
          {{ $t('messages.confirm', 'Confirm') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-dialog
    v-model="showOutputModal"
    max-width="720"
    scrollable
  >
    <v-card>
      <v-card-title class="d-flex align-center ga-2">
        <span>{{ $t('messages.artisan_runner_output', 'Command output') }}</span>
        <v-chip
          v-if="selectedCommand"
          size="small"
          variant="tonal"
        >
          {{ selectedCommand }}
        </v-chip>
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
        <pre class="system-console-widget-output text-body-medium">{{ output || '…' }}</pre>

        <v-card
          v-if="currentPrompt"
          class="mt-4"
          variant="tonal"
        >
          <v-card-title class="text-body-large">
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
          @click="showOutputModal = false"
        >
          {{ $t('messages.close', 'Close') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, ref, useAttrs, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAlert, useArtisanRunner } from '@/hooks'

const { t } = useI18n({ useScope: 'global' })
const { openAlert } = useAlert()

defineOptions({
  name: 'UeSystemConsole',
  inheritAttrs: false,
})

const props = defineProps({
  title: {
    type: String,
    default: 'System Console',
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
  maintenanceMode: {
    type: Boolean,
    default: false,
  },
  downPresets: {
    type: Array,
    default: () => [],
  },
  defaultDownPreset: {
    type: String,
    default: '',
  },
  cacheCommands: {
    type: Array,
    default: () => [],
  },
})

const attrs = useAttrs()
const cardClass = computed(() => attrs.class)

const {
  running,
  selectedCommand,
  output,
  exitCode,
  currentPrompt,
  promptAnswer,
  errorMessage,
  runDirect,
  submitPromptAnswer,
  abortRun,
} = useArtisanRunner(props.endpoints)

const maintenanceModeLocal = ref(Boolean(props.maintenanceMode))
watch(() => props.maintenanceMode, (value) => {
  maintenanceModeLocal.value = Boolean(value)
})

/** Dedupe by key/command in case a list prop was recursive-merged twice. */
const uniqueDownPresets = computed(() => {
  const seen = new Set()
  return (props.downPresets || []).filter((preset) => {
    const key = preset?.key
    if (!key || seen.has(key)) {
      return false
    }
    seen.add(key)
    return true
  })
})

const uniqueCacheCommands = computed(() => {
  const seen = new Set()
  return (props.cacheCommands || []).filter((cmd) => {
    const name = cmd?.command
    if (!name || seen.has(name)) {
      return false
    }
    seen.add(name)
    return true
  })
})

const selectedPresetKey = ref(props.defaultDownPreset || props.downPresets?.[0]?.key || '')
watch(() => props.defaultDownPreset, (value) => {
  if (value) {
    selectedPresetKey.value = value
  }
})

watch(uniqueDownPresets, (presets) => {
  if (!presets.some((preset) => preset.key === selectedPresetKey.value)) {
    selectedPresetKey.value = props.defaultDownPreset || presets[0]?.key || ''
  }
}, { immediate: true })

const showOutputModal = ref(false)
const showConfirm = ref(false)
const confirmMessage = ref('')
const pendingAction = ref(null)
const activeAction = ref(null)

const confirmPromptBool = computed({
  get () {
    return ['1', 'true', 'yes', 'y', 'on'].includes(String(promptAnswer.value).toLowerCase().trim())
      || promptAnswer.value === true
  },
  set (value) {
    promptAnswer.value = value ? 'yes' : 'no'
  },
})

function selectedPreset () {
  return uniqueDownPresets.value.find((preset) => preset.key === selectedPresetKey.value) ?? null
}

const selectedPresetOptionChips = computed(() => {
  const options = selectedPreset()?.options || {}
  const labels = {
    render: t('messages.system_console_option_render', 'render'),
    retry: t('messages.system_console_option_retry', 'retry'),
    refresh: t('messages.system_console_option_refresh', 'refresh'),
    secret: t('messages.system_console_option_secret', 'secret'),
  }

  return Object.entries(options).map(([key, value]) => ({
    key,
    label: labels[key] || key,
    value: key === 'secret' ? maskSecret(value) : String(value),
  }))
})

function maskSecret (value) {
  const text = String(value ?? '')
  if (!text) {
    return '—'
  }
  if (text.length <= 4) {
    return '••••'
  }

  return `${'•'.repeat(Math.min(text.length - 4, 8))}${text.slice(-4)}`
}

function requestDown () {
  const preset = selectedPreset()
  if (!preset) {
    return
  }

  pendingAction.value = {
    type: 'down',
    command: 'down',
    options: { ...(preset.options || {}) },
  }
  confirmMessage.value = t(
    'messages.system_console_confirm_down',
    'Put the application into maintenance mode using preset "{preset}"?',
    { preset: preset.label },
  )
  showConfirm.value = true
}

function runUp () {
  executeRun('up', 'up', {}, () => {
    maintenanceModeLocal.value = false
  })
}

function requestCacheCommand (cmd) {
  if (cmd.confirm) {
    pendingAction.value = {
      type: 'cache',
      command: cmd.command,
      options: {},
    }
    confirmMessage.value = t(
      'messages.system_console_confirm_command',
      'Run `{command}`?',
      { command: cmd.command },
    )
    showConfirm.value = true
    return
  }

  executeRun(cmd.command, cmd.command, {})
}

function confirmPendingAction () {
  const action = pendingAction.value
  showConfirm.value = false
  pendingAction.value = null
  if (!action) {
    return
  }

  executeRun(action.type === 'down' ? 'down' : action.command, action.command, action.options || {}, () => {
    if (action.type === 'down') {
      maintenanceModeLocal.value = true
    }
  })
}

async function executeRun (actionKey, command, options, onSuccess) {
  activeAction.value = actionKey
  showOutputModal.value = true

  const ok = await runDirect(command, {}, options)

  activeAction.value = null

  if (!ok && errorMessage.value) {
    openAlert({ message: String(errorMessage.value), variant: 'error' })
    return
  }

  if (exitCode.value === 0) {
    openAlert({ message: t('messages.artisan_runner_success', 'Command finished'), variant: 'success' })
    onSuccess?.()
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
</script>

<style scoped>
.system-console-widget-output {
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
