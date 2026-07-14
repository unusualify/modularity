import { describe, expect, test, vi, beforeEach } from 'vitest'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useFormBaseLogic from '../../src/js/hooks/form/useFormBaseLogic.js'

const vuetify = createVuetify({ components, directives })

const mockSetSchemaInputField = vi.fn()
const mockOnInputEventFormData = vi.fn()
vi.mock('@/utils/formEvents.js', () => ({
  default: {
    setSchemaInputField: (...args) => mockSetSchemaInputField(...args),
    onInputEventFormData: (...args) => mockOnInputEventFormData(...args)
  }
}))

const mockMapTypeToComponent = vi.fn((type) => `v-${type}`)
vi.mock('@/components/inputs/registry.js', () => ({
  mapTypeToComponent: (...args) => mockMapTypeToComponent(...args)
}))

const simpleSchema = {
  name: { type: 'text', name: 'name', label: 'Name' },
  email: { type: 'text', name: 'email', label: 'Email' }
}

const TestComponent = defineComponent({
  props: {
    id: { type: String, default: 'form-base-123' },
    schema: { type: Object, default: () => ({}) },
    modelValue: { type: Object, default: () => ({}) },
    model: { type: Object, default: null },
    row: { type: Object, default: null },
    noAutoGenerateSchema: { type: Boolean, default: false }
  },
  emits: ['update:modelValue', 'update:schema', 'input'],
  setup(props, { emit }) {
    return useFormBaseLogic(props, { emit }, {})
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify] },
    props: { schema: simpleSchema, modelValue: {}, ...props }
  })
}

beforeEach(() => {
  vi.clearAllMocks()
})

describe('useFormBaseLogic', () => {
  test('returns id, formSchema, flatCombinedArraySorted, valueIntern', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.id).toBeDefined()
    expect(wrapper.vm.formSchema).toBeDefined()
    expect(wrapper.vm.flatCombinedArraySorted).toBeDefined()
    expect(wrapper.vm.valueIntern).toBeDefined()
  })

  test('id uses props.id when provided', async () => {
    const wrapper = await factory({ id: 'custom-form-id' })
    expect(wrapper.vm.id).toBe('custom-form-id')
  })

  test('formSchema returns schema from props', async () => {
    const wrapper = await factory({ schema: simpleSchema })
    expect(wrapper.vm.formSchema).toEqual(simpleSchema)
  })

  test('getRow returns row prop or default', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.getRow).toBeDefined()
  })

  test('mapTypeToComponent delegates to registry', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.mapTypeToComponent('text')
    expect(mockMapTypeToComponent).toHaveBeenCalledWith('text', expect.any(Object))
    expect(result).toBe('v-text')
  })

  test('sanitizeShorthandType via bindSchema returns schema without type', async () => {
    const wrapper = await factory()
    const obj = { key: 'name', value: 'test', schema: { type: 'text', label: 'Name' } }
    const result = wrapper.vm.bindSchema(obj)
    expect(result).not.toHaveProperty('type')
    expect(result).toHaveProperty('label', 'Name')
  })

  test('getKeyTopSlot returns slot name with key', async () => {
    const wrapper = await factory({ id: 'form-1' })
    const obj = { key: 'name', schema: {} }
    const result = wrapper.vm.getKeyTopSlot(obj)
    expect(result).toContain('form-1')
    expect(result).toContain('name')
  })

  test('getFormTopSlot returns slot name', async () => {
    const wrapper = await factory({ id: 'form-1' })
    expect(wrapper.vm.getFormTopSlot()).toContain('form-1')
  })

  test('getFormBottomSlot returns slot name', async () => {
    const wrapper = await factory({ id: 'form-1' })
    expect(wrapper.vm.getFormBottomSlot()).toContain('form-1')
  })

  test('getShorthandTooltip returns object when string', async () => {
    const wrapper = await factory()
    const result = wrapper.vm.getShorthandTooltip('Help text')
    expect(result).toEqual({ location: 'top', text: 'Help text' })
  })

  test('getShorthandTooltip returns as-is when object', async () => {
    const wrapper = await factory()
    const tooltip = { location: 'bottom', text: 'Custom' }
    expect(wrapper.vm.getShorthandTooltip(tooltip)).toEqual(tooltip)
  })

  test('getShorthandTooltipLabel returns string when string', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.getShorthandTooltipLabel('Label')).toBe('Label')
  })

  test('checkExtensionType returns ext for number, range, date, time, color', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.checkExtensionType({ schema: { ext: 'number' } })).toBe('number')
    expect(wrapper.vm.checkExtensionType({ schema: { type: 'text' } })).toBe('text')
  })

  test('suspendClickAppend returns empty for select types', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.suspendClickAppend({ schema: { type: 'select' } })).toBe('')
  })

  test('suspendClickAppend returns click:append for text types', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.suspendClickAppend({ schema: { type: 'text' } })).toBe('click:append')
  })

  test('getImageSource returns src or base+value+tail', async () => {
    const wrapper = await factory()
    const obj = { value: 'image.jpg', schema: { base: '/img/', tail: '' } }
    expect(wrapper.vm.getImageSource(obj)).toBe('/img/image.jpg')
  })

  test('bindOptions returns object when string', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.bindOptions('opt')).toEqual({ value: 'opt', label: 'opt' })
  })

  test('bindOptions returns as-is when object', async () => {
    const wrapper = await factory()
    const opts = { value: 1, label: 'One' }
    expect(wrapper.vm.bindOptions(opts)).toEqual(opts)
  })

  test('rebuildArrays populates flatCombinedArray', async () => {
    const wrapper = await factory({ schema: simpleSchema, modelValue: { name: '', email: '' } })
    wrapper.vm.rebuildArrays(wrapper.vm.valueIntern, wrapper.vm.formSchema)
    expect(wrapper.vm.flatCombinedArray.length).toBeGreaterThan(0)
  })

  test('rebuildArrays throws when model is null', async () => {
    const wrapper = await factory()
    expect(() => wrapper.vm.rebuildArrays(null, {})).toThrow("Property 'model' is null or undefined")
  })

  test('onInput updates value and emits', async () => {
    const wrapper = await factory()
    wrapper.vm.rebuildArrays({ name: '', email: '' }, simpleSchema)
    const obj = wrapper.vm.flatCombinedArraySorted[0]
    if (obj) {
      const result = wrapper.vm.onInput('test', obj)
      expect(wrapper.emitted('update:modelValue')).toBeTruthy()
      expect(result).toHaveProperty('key')
    }
  })

  test('getKeyForArray returns key from schema or generated', async () => {
    const wrapper = await factory()
    const obj = { key: 'items', schema: { key: 'id' } }
    const item = { id: 1 }
    expect(wrapper.vm.getKeyForArray('form-1', obj, item, 0)).toBe(1)
  })

  test('getGridAttributes returns col object', async () => {
    const wrapper = await factory()
    const obj = { key: 'name', schema: {} }
    const attrs = wrapper.vm.getGridAttributes(obj)
    expect(attrs).toHaveProperty('cols')
  })

  test('checkInternGroupType prefixes v- when needed', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.checkInternGroupType({ schema: { typeInt: 'card' } })).toBe('v-card')
    expect(wrapper.vm.checkInternGroupType({ schema: { typeInt: 'v-card' } })).toBe('v-card')
  })
})
