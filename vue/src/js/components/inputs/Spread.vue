<template>
  <div class="meta-editor">
    <!-- Header Row -->
    <v-row no-gutters class="header-row py-2">
      <v-col cols="4">
        <div class="px-3 text-subtitle-1 font-weight-medium">Key</div>
      </v-col>
      <v-col cols="4">
        <div class="px-3 text-subtitle-1 font-weight-medium">Value</div>
      </v-col>
      <v-col cols="2">
        <div class="px-3 text-subtitle-1 font-weight-medium">Type</div>
      </v-col>
      <v-col cols="2">
        <div class="px-3 text-subtitle-1 font-weight-medium">Actions</div>
      </v-col>
    </v-row>

    <!-- Data Rows -->
    <div
      class="data-rows"
      :class="['data-rows', { scrollable }]"
      :height="height">
      <v-row
        v-for="(row, index) in localRows"
        :key="row.key + index"
        no-gutters
        class="data-row py-1"
      >
        <!-- Key Field -->
        <v-col cols="4">
          <div class="px-2">
            <!-- <v-text-field
              v-model="row.key"
              @input="handleKeyChange(index)"
              @blur="validateKeyOnBlur(index)"
              variant="outlined"
              density="compact"
              bg-color="surface"
              :error="row.keyError"
              :error-messages="row.keyErrorMessage"
              hide-details="auto"
              class="meta-field"
            /> -->
            <v-text-field
              v-model="row.tempKey"
              @input="handleTempKeyChange(index)"
              @blur="syncKeyWithTempKey(index)"
              variant="outlined"
              density="compact"
              bg-color="surface"
              :error="row.keyError"
              :error-messages="row.keyErrorMessage"
              hide-details="auto"
              class="meta-field"
            />
          </div>
        </v-col>

        <!-- Value Field -->
        <v-col cols="4">
          <div class="px-2">
            <template v-if="row.currentType === 'boolean'">
              <v-select
                v-model="row.currentValue"
                :items="[
                  { text: 'true', value: true },
                  { text: 'false', value: false }
                ]"
                item-title="text"
                item-value="value"
                variant="outlined"
                density="compact"
                bg-color="surface"
                hide-details
                class="meta-field"
                @input="handleFieldChange(index)"
              />
            </template>
            <template v-else>
              <v-text-field
                v-model="row.currentValue"
                :type="row.currentType === 'number' ? 'number' : 'text'"
                variant="outlined"
                density="compact"
                bg-color="surface"
                hide-details
                class="meta-field"
                @input="handleFieldChange(index)"
              />
            </template>
          </div>
        </v-col>

        <!-- Type Toggle -->
        <v-col cols="2">
          <div class="px-2">
            <v-btn
              class="type-toggle-btn"
              variant="outlined"
              @click="toggleType(index)"
              :color="getTypeColor(row.currentType)"
              density="comfortable"
              block
            >
              {{ row.currentType.toUpperCase() }}
            </v-btn>
          </div>
        </v-col>

        <!-- Delete Button -->
        <v-col cols="2" class="d-flex align-center">
          <div class="px-2">
            <v-btn
              color="error"
              variant="text"
              @click="deleteRow(index)"
              density="compact"
              icon
            >
              <v-icon>mdi-trash-can-outline</v-icon>
            </v-btn>
          </div>
        </v-col>
      </v-row>
    </div>

    <!-- Footer Actions -->
    <v-row no-gutters class="mt-4 px-2">
      <v-col cols="auto" class="mr-2">
        <v-btn
          color="primary"
          variant="text"
          prepend-icon="mdi-plus"
          @click="addNewRow"
        >
          Add row
        </v-btn>
      </v-col>
      <v-col cols="auto">
        <v-btn
          color="primary"
          :disabled="!hasUnsavedChanges || hasReservedKeyError"
          @click="saveAllChanges"
          prepend-icon="mdi-content-save"
        >
          Save All
        </v-btn>
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import { ref, computed, watch, onMounted } from 'vue'
import _ from 'lodash-es'

const props = defineProps({
  ...makeInputProps(),
  reservedKeys: {
    type: [Object, Array, Proxy],
    default: () => ({})
  },
  scrollable: {
    type: Boolean,
    default: false
  },
  height: {
    type: String,
    default: '300px'
  }
})

const emit = defineEmits([...makeInputEmits])

const inputHook = useInput(props, { emit })

const input = computed({
  get: () => inputHook.input.value,
  set: (val) => {
    inputHook.updateModelValue.value(val)
  }
})

const types = ['string', 'number', 'boolean']
const localRows = ref([])
const originalJson = ref({})
const hasUnsavedChanges = ref(false)
const hasReservedKeyError = computed(() => {
  return localRows.value.some(row => row.keyError)
})

const validateKey = (key, index) => {
  const row = localRows.value[index]
  const isReserved = Array.isArray(props.reservedKeys)
    ? props.reservedKeys.includes(key)
    : key in props.reservedKeys

  if (isReserved) {
    row.keyError = true
    row.keyErrorMessage = 'This key is reserved'
    return false
  }

  row.keyError = false
  row.keyErrorMessage = ''
  return true
}

onMounted(() => {
  if (props.modelValue) {
    originalJson.value = _.cloneDeep(props.modelValue)
    initializeRows(props.modelValue)
  }
})

watch(() => props.modelValue, (newValue) => {
  if (!originalJson.value || Object.keys(originalJson.value).length === 0) {
    originalJson.value = _.cloneDeep(newValue || {})
    initializeRows(newValue || {})
  }
}, { deep: true })

const filterReservedKeys = () => {
  // Filter out rows where the key matches any reserved key
  localRows.value = localRows.value.filter(row => {
    // Handle case where reservedKeys is an array
    if (Array.isArray(props.reservedKeys)) {
      return !props.reservedKeys.includes(row.key)
    }
    // Handle case where reservedKeys is an object
    return !(row.key in props.reservedKeys)
  })

  // Update the input value after filtering
  input.value = getCurrentJson()
  updateHasUnsavedChanges()
}


const initializeRows = (obj) => {
  localRows.value = Object.entries(obj || {}).map(([key, value]) => ({
    key,
    tempKey: key, // Add tempKey for smoother updates
    currentValue: value,
    initialValue: value,
    originalValue: value,
    currentType: detectType(value),
    initialType: detectType(value),
    originalType: detectType(value),
    saved: true,
    valueBeforeTypeChange: value,
    keyError: false,
    keyErrorMessage: ''
  }))
  filterReservedKeys()
  updateHasUnsavedChanges()
}

const detectType = (value) => {
  if (typeof value === 'boolean') return 'boolean'
  if (typeof value === 'number') return 'number'
  return 'string'
}

const handleTempKeyChange = (index) => {
  const row = localRows.value[index]
  row.saved = false
  updateHasUnsavedChanges()
}

const syncKeyWithTempKey = (index) => {
  const row = localRows.value[index]
  if (row.tempKey !== row.key) {
    row.key = row.tempKey
    validateKey(row.key, index)
    input.value = getCurrentJson()
  }
}

const handleFieldChange = (index) => {
  const row = localRows.value[index]
  row.saved = false

  if (row.currentType === 'number') {
    row.currentValue = Number(row.currentValue) || 0
  } else if (row.currentType === 'boolean') {
    if (typeof row.currentValue === 'string') {
      row.currentValue = row.currentValue.toLowerCase() === 'true' || row.currentValue === '1'
    }
  }

  if (!row.savedValues) {
    row.savedValues = {
      [row.currentType]: row.currentValue
    }
  } else {
    row.savedValues[row.currentType] = row.currentValue
  }

  const newState = getCurrentJson()
  input.value = newState
  updateHasUnsavedChanges()
}

const toggleType = (index) => {
  const row = localRows.value[index]
  const currentTypeIndex = types.indexOf(row.currentType)
  const nextTypeIndex = (currentTypeIndex + 1) % types.length
  const nextType = types[nextTypeIndex]

  if (row.currentType === row.originalType && !row.valueBeforeTypeChange) {
    row.valueBeforeTypeChange = row.currentValue
  }

  if (nextType === row.originalType) {
    row.currentType = row.originalType
    row.currentValue = row.originalValue
  } else {
    row.currentType = nextType
    row.currentValue = convertValue(
      row.originalValue,
      nextType,
      row.originalType,
      row.originalValue
    )
  }

  row.saved = false
  updateHasUnsavedChanges()
  input.value = getCurrentJson()
}

const convertValue = (value, type, originalType = null, originalValue = null) => {
  if (originalType && type === originalType && originalValue !== null) {
    return originalValue
  }

  switch (type) {
    case 'number':
      return Number(value) || 0
    case 'boolean':
      if (typeof value === 'string') {
        return value.toLowerCase() === 'true' || value === '1'
      }
      if (typeof value === 'number') {
        return value !== 0
      }
      return Boolean(value)
    case 'string':
      if (typeof value === 'boolean') {
        return value ? 'true' : 'false'
      }
      return String(value)
    default:
      return String(value)
  }
}

const deleteRow = (index) => {
  localRows.value.splice(index, 1)
  updateHasUnsavedChanges()
  input.value = getCurrentJson()
}

const addNewRow = () => {
  const newKey = `key_${localRows.value.length + 1}`
  localRows.value.push({
    key: newKey,
    tempKey: newKey, // Initialize tempKey
    currentValue: '',
    initialValue: '',
    currentType: 'string',
    initialType: 'string',
    originalType: 'string',
    saved: false,
    valueBeforeTypeChange: '',
    keyError: false,
    keyErrorMessage: ''
  })
  updateHasUnsavedChanges()
  input.value = getCurrentJson()
}

const updateHasUnsavedChanges = () => {
  const currentState = getCurrentJson()
  hasUnsavedChanges.value = !_.isEqual(currentState, originalJson.value)
}

const getCurrentJson = () => {
  const updatedObject = {}
  localRows.value.forEach(row => {
    if (row.key && row.currentValue !== undefined) {
      let finalValue = row.currentValue

      switch (row.currentType) {
        case 'number':
          finalValue = Number(row.currentValue) || 0
          break
        case 'boolean':
          if (typeof row.currentValue === 'string') {
            finalValue = row.currentValue.toLowerCase() === 'true' || row.currentValue === '1'
          } else if (typeof row.currentValue === 'number') {
            finalValue = row.currentValue !== 0
          } else {
            finalValue = Boolean(row.currentValue)
          }
          break
        case 'string':
          finalValue = String(row.currentValue)
          break
      }

      updatedObject[row.key] = finalValue
    }
  })
  return updatedObject
}

const saveAllChanges = () => {
  const newJson = getCurrentJson()
  originalJson.value = _.cloneDeep(newJson)

  localRows.value.forEach(row => {
    row.initialValue = row.currentValue
    row.initialType = row.currentType
    row.valueBeforeTypeChange = row.currentValue
    row.saved = true
  })

  input.value = newJson
  hasUnsavedChanges.value = false
}

const getTypeColor = (type) => {
  switch (type) {
    case 'number':
      return 'primary'
    case 'boolean':
      return 'success'
    default:
      return 'default'
  }
}
</script>


<style lang="scss">
.meta-editor {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 8px;
  overflow: hidden;
  .header-row {
    background-color: rgba(0, 0, 0, 0.02);
    border-bottom: 1px solid rgba(0, 0, 0, 0.12);
  }
  .scrollable{
    overflow: auto;
  }

  .data-row {
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    &:last-child {
      border-bottom: none;
    }
  }

  .meta-field {
    .v-field__outline {
      --v-field-border-opacity: 0.12;
    }
  }

  .type-toggle-btn {
    text-transform: none;
    width: 100%;
  }
}
</style>
