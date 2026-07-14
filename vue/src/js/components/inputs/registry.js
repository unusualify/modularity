/**
 * Input type to Vuetify component mapping.
 * Use registerInputType() to add custom input types.
 *
 * @example
 * import { registerInputType, mapTypeToComponent } from '@/components/inputs/registry'
 * registerInputType('my-custom', 'VMyCustomInput')
 * const component = mapTypeToComponent('my-custom') // => 'VMyCustomInput'
 *
 * HYDRATE ADAPTER: Hydrates (src/Hydrates/Inputs/*) output schema.type = 'input-{kebab}'.
 * These map to VInput{Studly} (e.g. input-checklist → VInputChecklist). See AGENTS.md.
 */

/** Built-in Vuetify/schema types (FormBase default branch) */
const builtInTypeMap = {
  title: 'InputTitle',
  radio: 'InputRadio',
  text: 'v-text-field',
  password: 'v-text-field',
  email: 'v-text-field',
  tel: 'v-text-field',
  url: 'v-text-field',
  search: 'v-text-field',
  number: 'v-text-field',
  date: 'v-date-picker',
  time: 'v-time-picker',
  color: 'v-color-picker',
  img: 'v-img',
  textarea: 'v-textarea',
  range: 'v-slider',
  file: 'v-file-input',
  switch: 'v-switch',
  checkbox: 'v-checkbox',
  card: 'v-card',
  hidden: 'input'
}

/** Hydrate output types → Vue component names. Matches src/Hydrates/Inputs/* output. */
const hydrateTypeMap = {
  'input-assignment': 'VInputAssignment',
  'input-browser': 'VInputBrowser',
  'input-chat': 'VInputChat',
  'input-checklist': 'VInputChecklist',
  'input-checklist-group': 'VInputChecklistGroup',
  'input-comparison-table': 'VInputComparisonTable',
  'input-date': 'VInputDate',
  'input-editor': 'VInputEditor',
  'input-file': 'VInputFile',
  'input-filepond': 'VInputFilepond',
  'input-filepond-avatar': 'VInputFilepondAvatar',
  'input-form-tabs': 'VInputFormTabs',
  'input-image': 'VInputImage',
  'input-image-gallery': 'VInputImageGallery',
  'input-payment-service': 'VInputPaymentService',
  'input-price': 'VInputPrice',
  'input-process': 'VInputProcess',
  'input-radio-group': 'VInputRadioGroup',
  'input-repeater': 'VInputRepeater',
  'input-remote-api': 'VInputRemoteApi',
  'input-select-scroll': 'VInputSelectScroll',
  'input-slug': 'VInputSlug',
  'input-json-field': 'VInputJsonField',
  'input-layout-blades': 'VInputLayoutBlades',
  'input-spread': 'VInputSpread',
  'input-tag': 'VInputTag',
  'input-tagger': 'VInputTagger'
}

const customTypeMap = {}

/**
 * Register a custom input type.
 * @param {string} type - Schema type (e.g. 'input-price', 'my-custom')
 * @param {string} component - Component name (e.g. 'VInputPrice', 'VMyCustom')
 */
export function registerInputType (type, component) {
  customTypeMap[type] = component
}

/**
 * Resolve schema type to component name.
 * @param {string} type - Schema type
 * @param {Object} [globalComponents] - Optional app-level components to merge (e.g. vueInstance.components)
 * @returns {string} Component name (e.g. 'v-text-field', 'VInputPrice')
 */
export function mapTypeToComponent (type, globalComponents = {}) {
  const allTypes = { ...builtInTypeMap, ...hydrateTypeMap, ...customTypeMap, ...globalComponents }
  return allTypes[type] || `v-${type}`
}

/**
 * Get all registered types (built-in + custom).
 * @returns {Object}
 */
export function getRegisteredTypes () {
  return { ...builtInTypeMap, ...hydrateTypeMap, ...customTypeMap }
}

export { builtInTypeMap, hydrateTypeMap, customTypeMap }
