<template>
  <v-input
    v-model="input"
  >
    <!-- Loading Overlay -->
    <v-overlay v-model="isLoading" class="align-center justify-center">
      <v-progress-circular color="primary" indeterminate size="64"></v-progress-circular>
    </v-overlay>

    <!-- Snackbar for notifications -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.text }}
    </v-snackbar>

    <!-- File Upload Section -->
    <v-row align="center">
      <v-col cols="12" sm="8">
        <v-file-input
          v-model="fileInput"
          :accept="'.xlsx, .xls, .csv'"
          :label="input.length > 0 ? 'Update file' : 'Add file'"
          @change="handleFileChange"
          hide-details
          clearable
          variant="outlined"
          density="compact"
        />
      </v-col>

      <v-col cols="12" sm="4" class="d-flex">
        <v-btn
          color="success"
          icon="mdi-table"
          class="mr-2"
          v-if="input.length > 0"
          @click="showDialog = true"
          variant="tonal"
        />

        <v-btn
          color="error"
          icon="mdi-delete"
          class="mr-2"
          v-if="input.length > 0"
          @click="clearData"
          variant="tonal"
        />

        <v-btn
          v-if="example_file"
          color="info"
          icon="mdi-download"
          :href="example_file"
          target="_blank"
          variant="tonal"
        />
      </v-col>
    </v-row>

    <!-- Data Table Dialog -->
    <v-dialog v-model="showDialog" fullscreen>
      <v-card>
        <v-toolbar color="primary">
          <v-toolbar-title>List</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon="mdi-close" @click="showDialog = false" />
        </v-toolbar>

        <v-card-text>
          <v-data-table
            v-if="tableData.length"
            :headers="tableHeaders"
            :items="tableData"
            :items-per-page="10"
          >
            <template v-slot:item="{ item }">
              <tr :class="{ 'highlighted-row': item.highlighted === '1' }">
                <td v-for="header in tableHeaders" :key="header.key">
                  {{ item[header.key] }}
                </td>
              </tr>
            </template>
          </v-data-table>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-input>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import * as XLSX from 'xlsx'
import _ from 'lodash'
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

// Props
const props = defineProps({
  ...makeInputProps(),
  sheet_columns: {
    type: Array,
    default: () => [
      { value: 'Yayin_Adi' },
      { value: 'Yayin_Linki' },
      { value: 'highlighted' }
    ]
  },
  example_file: {
    type: String,
    default: ''
  },
  spreadsheetData: {
    type: [Array, Proxy, Object],
    default: () => [

    ]
  }
})

// Emits
const emit = defineEmits([...makeInputEmits])

// Hook
const inputHook = useInput(props, { emit })

// input
const input = computed({
  get: () => inputHook.input.value,
  set: (val) => {
    inputHook.updateModelValue.value(val)
  }
})

const fileInput = ref(null)
const showDialog = ref(false)
const tableData = ref([])
const tableHeaders = ref([])
const isLoading = ref(false)
const snackbar = ref({
  show: false,
  text: '',
  color: 'error'
})

// Initialize from props
onMounted(() => {
  // console.log(input?.value.length, input?.value)
  if (input?.value.length) {
    fileInput.value = [new File([], 'existing-data.xlsx')]
    processTableData(input?.value)

  }
})

// Watch for external changes
watch(input, (newValue) => {
  if (newValue?.length) {
    processTableData()
  } else {
    tableData.value = []
    tableHeaders.value = []
  }
}, { deep: true })

// Process table headers
const processTableHeaders = (data) => {
  if (!data.length) return []
  return Object.keys(data[0]).map(key => ({
    title: key.replace(/_/g, ' '),
    key: key,
    align: 'start',
    sortable: true
  }))
}

// Process table data
const processTableData = (data = null) => {
  if(data){
    tableData.value = _.cloneDeep(data)
    tableHeaders.value = processTableHeaders(input.value)
  }else {
    tableData.value = _.cloneDeep(input.value)
    tableHeaders.value = processTableHeaders(input.value)
  }

}

// Handle file change
const handleFileChange = async (event) => {
  // If no file is selected or the input is cleared
  if (!fileInput.value) {
    clearData()
    return
  }

  // Get the actual file object from the v-file-input value
  const file = fileInput.value

  isLoading.value = true

  try {
    const data = await readFileData(file)
    const processedData = processData(data)
    input.value = processedData
    processTableData()

    snackbar.value = {
      show: true,
      text: 'File processed successfully',
      color: 'success'
    }
  } catch (error) {
    console.error('File processing error:', error)
    snackbar.value = {
      show: true,
      text: error.message,
      color: 'error'
    }
    clearData()
  } finally {
    isLoading.value = false
  }
}

// Process and validate data
const processData = (data) => {
  const hasValidHeaders = Object.keys(data[0]).some(key =>
    !key || typeof key !== 'string' || key.length > 20
  )

  if (hasValidHeaders) {
    return data.map(row => {
      return props.sheet_columns.reduce((acc, col) => {
        acc[col.value] = row[col.value] || ''
        return acc
      }, {})
    })
  }
  return data
}

// File reading logic
const readFileData = (file) => {
  return new Promise((resolve, reject) => {
    if (!(file instanceof Blob)) {
      reject(new Error("Invalid file type. Expected a Blob or File object."))
      return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
      try {
        const workbook = XLSX.read(e.target.result, { type: 'array' })
        const worksheet = workbook.Sheets[workbook.SheetNames[0]]
        // console.log(XLSX.utils.sheet_to_json(worksheet))
        resolve(XLSX.utils.sheet_to_json(worksheet))
      } catch (error) {
        reject(error)
      }
    }
    reader.onerror = reject
    reader.readAsArrayBuffer(file)
  })
}

// Clear data
const clearData = () => {
  fileInput.value = null
  input.value = []
  tableData.value = []
  tableHeaders.value = []
}
</script>

<style scoped>
.highlighted-row {
  background-color: rgb(255, 244, 229) !important;
}
</style>
