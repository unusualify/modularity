import { describe, expect, test, vi } from 'vitest'
import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import useFormBase from '../../src/js/hooks/form/useFormBase.js'

const vuetify = createVuetify({ components, directives })

vi.mock('@/utils/formEvents.js', () => ({
  default: {
    setSchemaInputField: vi.fn(),
    onInputEventFormData: vi.fn()
  }
}))

vi.mock('@/components/inputs/registry.js', () => ({
  mapTypeToComponent: (type) => `v-${type}`
}))

const simpleSchema = {
  name: { type: 'text', name: 'name' },
  email: { type: 'text', name: 'email' }
}

const TestComponent = defineComponent({
  props: {
    id: { type: String, default: 'form-base' },
    schema: { type: Object, default: () => ({}) },
    modelValue: { type: Object, default: () => ({}) }
  },
  emits: ['update:modelValue', 'update:schema', 'input'],
  setup(props, context) {
    return useFormBase(props, context)
  },
  template: '<div />'
})

async function factory(props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify] },
    props: { schema: simpleSchema, modelValue: {}, ...props }
  })
}

describe('useFormBase', () => {
  test('returns id, formSchema, flatCombinedArraySorted (delegates to useFormBaseLogic)', async () => {
    const wrapper = await factory()
    expect(wrapper.vm.id).toBeDefined()
    expect(wrapper.vm.formSchema).toBeDefined()
    expect(wrapper.vm.flatCombinedArraySorted).toBeDefined()
  })

  test('works with context.emit', async () => {
    const wrapper = await factory()
    wrapper.vm.rebuildArrays({ name: '', email: '' }, simpleSchema)
    const obj = wrapper.vm.flatCombinedArraySorted[0]
    if (obj) {
      wrapper.vm.onInput('test', obj)
      expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    }
  })

  test('handles missing context gracefully', async () => {
    const wrapper = mount(defineComponent({
      props: { schema: { type: Object }, modelValue: { type: Object } },
      setup(props) {
        return useFormBase(props, null)
      },
      template: '<div />'
    }), {
      global: { plugins: [vuetify] },
      props: { schema: simpleSchema, modelValue: {} }
    })
    expect(wrapper.vm.id).toBeDefined()
  })
})
