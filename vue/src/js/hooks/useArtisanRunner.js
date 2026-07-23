import { computed, ref } from 'vue'
import axios from 'axios'

/**
 * ArtisanRunner panel API: catalog, definition, SSE run stream, interactive answers.
 *
 * @param {{ commands: string, definition: string, run: string, answer: string }} endpoints
 */
export default function useArtisanRunner (endpoints) {
  const commands = ref([])
  const loadingCommands = ref(false)
  const loadingDefinition = ref(false)
  const running = ref(false)
  const selectedCommand = ref(null)
  const definition = ref(null)
  const formArguments = ref({})
  const formOptions = ref({})
  const output = ref('')
  const exitCode = ref(null)
  const runId = ref(null)
  const currentPrompt = ref(null)
  const promptAnswer = ref('')
  const errorMessage = ref(null)

  let abortController = null

  const definitionUrl = computed(() => {
    if (!endpoints?.definition || !selectedCommand.value) {
      return ''
    }
    return endpoints.definition.replace('__NAME__', encodeURIComponent(selectedCommand.value))
  })

  function answerUrl (id) {
    if (!endpoints?.answer) {
      return ''
    }
    return endpoints.answer.replace('__RUN_ID__', encodeURIComponent(id))
  }

  async function loadCommands () {
    if (!endpoints?.commands) {
      return
    }
    loadingCommands.value = true
    errorMessage.value = null
    try {
      const { data } = await axios.get(endpoints.commands)
      commands.value = Array.isArray(data?.commands) ? data.commands : []
    } catch (e) {
      errorMessage.value = e.response?.data?.message ?? e.message ?? 'Failed to load commands'
      commands.value = []
    } finally {
      loadingCommands.value = false
    }
  }

  async function loadDefinition (name) {
    selectedCommand.value = name
    definition.value = null
    formArguments.value = {}
    formOptions.value = {}
    if (!name || !endpoints?.definition) {
      return
    }
    loadingDefinition.value = true
    errorMessage.value = null
    try {
      const url = endpoints.definition.replace('__NAME__', encodeURIComponent(name))
      const { data } = await axios.get(url)
      definition.value = data
      const args = {}
      for (const arg of data.arguments || []) {
        args[arg.name] = arg.default ?? (arg.is_array ? '' : '')
      }
      formArguments.value = args
      const opts = {}
      for (const opt of data.options || []) {
        if (opt.field === 'switch') {
          opts[opt.name] = !!opt.default
        } else {
          opts[opt.name] = opt.default ?? ''
        }
      }
      formOptions.value = opts
    } catch (e) {
      errorMessage.value = e.response?.data?.message ?? e.message ?? 'Failed to load definition'
    } finally {
      loadingDefinition.value = false
    }
  }

  function validateRequired () {
    if (!definition.value) {
      return 'Select a command first.'
    }
    for (const arg of definition.value.arguments || []) {
      if (!arg.required) {
        continue
      }
      const value = formArguments.value[arg.name]
      if (value === null || value === undefined || String(value).trim() === '') {
        return `Argument "${arg.name}" is required.`
      }
    }
    for (const opt of definition.value.options || []) {
      if (!opt.required) {
        continue
      }
      const value = formOptions.value[opt.name]
      if (value === null || value === undefined || String(value).trim() === '') {
        return `Option "--${opt.name}" is required.`
      }
    }
    return null
  }

  function parseSseChunk (buffer, onEvent) {
    const parts = buffer.split('\n\n')
    const rest = parts.pop() ?? ''
    for (const part of parts) {
      if (!part.trim()) {
        continue
      }
      let event = 'message'
      const dataLines = []
      for (const line of part.split('\n')) {
        if (line.startsWith('event:')) {
          event = line.slice(6).trim()
        } else if (line.startsWith('data:')) {
          dataLines.push(line.slice(5).trim())
        }
      }
      const raw = dataLines.join('\n')
      let payload = raw
      try {
        payload = JSON.parse(raw)
      } catch {
        // keep raw string
      }
      onEvent(event, payload)
    }
    return rest
  }

  /**
   * Run a known command with explicit args/options (skips definition form validation).
   * Used by SystemConsole and other fixed-action UIs.
   *
   * @param {string} command
   * @param {Record<string, unknown>} [args]
   * @param {Record<string, unknown>} [opts]
   */
  async function runDirect (command, args = {}, opts = {}) {
    if (!command || !endpoints?.run) {
      return false
    }

    selectedCommand.value = command
    formArguments.value = { ...args }
    formOptions.value = { ...opts }
    definition.value = {
      name: command,
      description: '',
      arguments: [],
      options: [],
    }

    return runCommand({ skipValidation: true })
  }

  /**
   * @param {{ skipValidation?: boolean }} [opts]
   */
  async function runCommand (opts = {}) {
    const skipValidation = Boolean(opts?.skipValidation)
    if (!skipValidation) {
      const validationError = validateRequired()
      if (validationError) {
        errorMessage.value = validationError
        return false
      }
    }
    if (!endpoints?.run || !selectedCommand.value) {
      return false
    }

    running.value = true
    output.value = ''
    exitCode.value = null
    runId.value = null
    currentPrompt.value = null
    promptAnswer.value = ''
    errorMessage.value = null
    abortController = new AbortController()

    try {
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      const response = await fetch(endpoints.run, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'text/event-stream',
          'X-Requested-With': 'XMLHttpRequest',
          ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
        },
        credentials: 'same-origin',
        signal: abortController.signal,
        body: JSON.stringify({
          command: selectedCommand.value,
          arguments: formArguments.value,
          options: formOptions.value,
        }),
      })

      if (!response.ok) {
        let message = `Run failed (${response.status})`
        try {
          const json = await response.json()
          message = json.message || message
        } catch {
          // ignore
        }
        errorMessage.value = message
        running.value = false
        return false
      }

      const reader = response.body?.getReader()
      if (!reader) {
        errorMessage.value = 'Streaming is not supported in this browser.'
        running.value = false
        return false
      }

      const decoder = new TextDecoder()
      let buffer = ''

      while (true) {
        const { done, value } = await reader.read()
        if (done) {
          break
        }
        buffer += decoder.decode(value, { stream: true })
        buffer = parseSseChunk(buffer, (event, payload) => {
          if (event === 'started') {
            runId.value = payload?.runId ?? null
          } else if (event === 'output') {
            output.value += payload?.chunk ?? ''
          } else if (event === 'prompt') {
            currentPrompt.value = payload
            promptAnswer.value = payload?.default != null ? String(payload.default) : ''
          } else if (event === 'error') {
            errorMessage.value = payload?.message ?? 'Command error'
            output.value += `\n[error] ${payload?.message ?? ''}\n`
          } else if (event === 'done') {
            exitCode.value = payload?.exitCode ?? null
            currentPrompt.value = null
          }
        })
      }
    } catch (e) {
      if (e?.name !== 'AbortError') {
        errorMessage.value = e.message ?? 'Run failed'
      }
    } finally {
      running.value = false
      abortController = null
    }

    return true
  }

  async function submitPromptAnswer () {
    if (!runId.value || !currentPrompt.value?.id) {
      return
    }
    const answeredPromptId = currentPrompt.value.id
    const url = answerUrl(runId.value)
    let answer = promptAnswer.value
    if (currentPrompt.value.type === 'confirm') {
      answer = ['1', 'true', 'yes', 'y', 'on'].includes(String(answer).toLowerCase().trim())
    }
    await axios.post(url, {
      prompt_id: answeredPromptId,
      answer,
    })
    // A follow-up prompt may arrive over SSE while we awaited the answer POST.
    // Only clear if we are still showing the prompt we just answered.
    if (currentPrompt.value?.id === answeredPromptId) {
      currentPrompt.value = null
      promptAnswer.value = ''
    }
  }

  function abortRun () {
    abortController?.abort()
  }

  return {
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
    runId,
    currentPrompt,
    promptAnswer,
    errorMessage,
    definitionUrl,
    loadCommands,
    loadDefinition,
    validateRequired,
    runCommand,
    runDirect,
    submitPromptAnswer,
    abortRun,
  }
}
