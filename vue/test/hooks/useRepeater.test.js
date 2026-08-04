import { describe, expect, test, vi, beforeEach } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../src/js/config/i18n'
import useRepeater, { makeRepeaterProps } from '../../src/js/hooks/useRepeater.js'

const vuetify = createVuetify({ components, directives })

const mockGetModel = vi.fn((schema, item) => ({}))
const mockInvokeRuleGenerator = vi.fn((schema) => schema)

vi.mock('@/utils/getFormData.js', () => ({
  getModel: (...args) => mockGetModel(...args)
}))

vi.mock('@/hooks/useValidation.js', () => ({
  default: () => ({
    invokeRuleGenerator: (...args) => mockInvokeRuleGenerator(...args)
  })
}))

beforeEach(() => {
  vi.clearAllMocks()
  mockGetModel.mockImplementation((schema, item = null) => {
    const keys = Object.keys(schema || {})
    return keys.reduce((acc, k) => {
      const name = schema[k]?.name ?? k
      acc[name] = item?.[name] ?? schema[k]?.default ?? ''
      return acc
    }, {})
  })
  mockInvokeRuleGenerator.mockImplementation((s) => s)
  if (!window.__headline) {
    window.__headline = (s) => (s || '').replace(/_/g, ' ')
  }
})

function createStoreStub() {
  const store = createStore({
    state: {},
    getters: {},
    mutations: {
      __setAlert: () => {}
    }
  })
  store.commit = vi.fn(store.commit)
  return store
}

const simpleSchema = {
  name: { name: 'name', type: 'text', label: 'Name' },
  email: { name: 'email', type: 'text', label: 'Email' }
}

const TestComponent = defineComponent({
  props: {
    modelValue: { type: Array, default: () => [] },
    schema: { type: Object, default: () => ({}) },
    draggable: { type: Boolean, default: false },
    orderKey: { type: String, default: 'position' },
    ...makeRepeaterProps()
  },
  emits: ['update:modelValue'],
  setup(props, context) {
    return useRepeater(props, context)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { schema: simpleSchema, ...props }
  })
}

describe('useRepeater', () => {
  test('makeRepeaterProps returns schema, modelValue, max, min', () => {
    const props = makeRepeaterProps()
    expect(props.schema).toBeDefined()
    expect(props.modelValue).toBeDefined()
    expect(props.max).toBeDefined()
    expect(props.min).toBeDefined()
    expect(props.max.default).toBe(-1)
    expect(props.min.default).toBe(-1)
  })

  test('returns repeaterModels, repeaterSchemas, totalRepeats, hasRepeaterModels', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: simpleSchema })
    expect(wrapper.vm.repeaterModels).toBeDefined()
    expect(wrapper.vm.repeaterSchemas).toBeDefined()
    expect(wrapper.vm.totalRepeats).toBeDefined()
    expect(wrapper.vm.hasRepeaterModels).toBeDefined()
    expect(wrapper.vm.addRepeaterBlock).toBeDefined()
    expect(wrapper.vm.deleteRepeaterBlock).toBeDefined()
    expect(wrapper.vm.duplicateRepeaterBlock).toBeDefined()
  })

  test('hasRepeaterModels false when empty schema', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: {} })
    expect(wrapper.vm.hasRepeaterModels).toBe(false)
    expect(wrapper.vm.totalRepeats).toBe(0)
  })

  test('addRepeaterBlock adds new block when addible', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: simpleSchema, max: 5 })
    const initialCount = wrapper.vm.totalRepeats
    wrapper.vm.addRepeaterBlock()
    expect(wrapper.vm.totalRepeats).toBe(initialCount + 1)
  })

  test('deleteRepeaterBlock removes block when deletable', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a', email: 'a@x.com' }, { name: 'b', email: 'b@x.com' }],
      min: 0
    })
    const initialCount = wrapper.vm.totalRepeats
    if (initialCount > 0) {
      wrapper.vm.deleteRepeaterBlock(0)
      expect(wrapper.vm.totalRepeats).toBe(initialCount - 1)
    }
  })

  test('deleteRepeaterBlock commits alert when not deletable', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }],
      min: 1
    })
    wrapper.vm.deleteRepeaterBlock(0)
    expect(store.commit).toHaveBeenCalledWith('__setAlert', expect.objectContaining({
      variant: 'warning',
      message: expect.stringContaining('at least')
    }))
  })

  test('addRepeaterBlock commits alert when max reached', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }, { name: 'b' }],
      max: 2
    })
    wrapper.vm.addRepeaterBlock()
    expect(store.commit).toHaveBeenCalledWith('__setAlert', expect.objectContaining({
      variant: 'warning',
      message: expect.stringContaining('at much')
    }))
  })

  test('headers computed from schema', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { schema: simpleSchema })
    expect(wrapper.vm.headers).toBeDefined()
    expect(wrapper.vm.headers.length).toBeGreaterThan(0)
  })

  test('addButtonContent includes singularLabel when hasButtonLabel', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      addButtonText: 'Add',
      hasButtonLabel: true,
      singularLabel: 'Item'
    })
    expect(wrapper.vm.addButtonContent).toContain('Item')
  })

  test('duplicateRepeaterBlock duplicates block when addible', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a', email: 'a@x.com' }],
      max: 5
    })
    const initialCount = wrapper.vm.totalRepeats
    wrapper.vm.duplicateRepeaterBlock(0)
    expect(wrapper.vm.totalRepeats).toBe(initialCount + 1)
  })

  test('duplicateRepeaterBlock commits alert when max reached', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }, { name: 'b' }],
      max: 2
    })
    wrapper.vm.duplicateRepeaterBlock(0)
    expect(store.commit).toHaveBeenCalledWith('__setAlert', expect.objectContaining({
      variant: 'warning'
    }))
  })

  test('onUpdateRepeaterModel updates model at index', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }]
    })
    const id = wrapper.vm.id
    const value = { [`repeater${id}[0][name]`]: 'updated' }
    wrapper.vm.onUpdateRepeaterModel(value, 0)
    expect(wrapper.vm.repeaterModels[0][`repeater${id}[0][name]`]).toBe('updated')
    expect(wrapper.vm.repeaterModels[0].id).toBe(0)
  })

  test('repeaterSchemas generated for each model', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a' }]
    })
    expect(wrapper.vm.repeaterSchemas).toBeDefined()
    expect(wrapper.vm.repeaterSchemas.length).toBe(wrapper.vm.totalRepeats)
  })

  test('does not emit update when draggable payload only differs by missing id/undefined', async () => {
    const store = createStoreStub()
    const zigzagSchema = {
      title: { name: 'title', type: 'text', label: 'Title' },
      description: { name: 'description', type: 'textarea', label: 'Description' },
      image_position: { name: 'image_position', type: 'select', label: 'Image Position', default: 'right' }
    }
    const modelValue = [
      {
        title: 'Announce Company News',
        description: '<p>Sharing corporate news</p>',
        image_position: 'right',
        order: '0',
        position: 0
      },
      {
        title: 'Key Press Release',
        description: '<p>B2Press delivers</p>',
        image_position: 'right',
        order: '1',
        position: 1
      }
    ]

    const wrapper = await factory(store, {
      schema: zigzagSchema,
      modelValue,
      draggable: true,
      orderKey: 'position',
      autoIdGenerator: false
    })

    await wrapper.vm.$nextTick()

    const emitted = wrapper.emitted('update:modelValue') ?? []
    for (const [payload] of emitted) {
      expect(payload).toEqual(modelValue)
      payload.forEach((item) => {
        expect(Object.prototype.hasOwnProperty.call(item, 'id')).toBe(false)
        expect(item.position).not.toBeUndefined()
        expect(Object.prototype.hasOwnProperty.call(item, 'order')).toBe(true)
      })
    }

    // Mutating a schema field should keep passthrough order/position
    const id = wrapper.vm.id
    wrapper.vm.onUpdateRepeaterModel({
      [`repeater${id}[0][title]`]: 'Updated title',
      [`repeater${id}[0][description]`]: modelValue[0].description,
      [`repeater${id}[0][image_position]`]: modelValue[0].image_position
    }, 0)

    await wrapper.vm.$nextTick()

    const lastPayload = wrapper.emitted('update:modelValue')?.at(-1)?.[0]
    expect(lastPayload[0].title).toBe('Updated title')
    expect(lastPayload[0].position).toBe(0)
    expect(lastPayload[0].order).toBe('0')
    expect(Object.prototype.hasOwnProperty.call(lastPayload[0], 'id')).toBe(false)
  })

  test('exposes stable dom key when autoIdGenerator is false', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      schema: simpleSchema,
      modelValue: [{ name: 'a', email: 'a@x.com' }],
      autoIdGenerator: false
    })

    expect(wrapper.vm.getRepeaterDomKey(wrapper.vm.repeaterModels[0], 0)).toEqual(expect.any(String))
    expect(typeof wrapper.vm.draggableItemKey).toBe('function')
    expect(wrapper.vm.draggableItemKey(wrapper.vm.repeaterModels[0])).toEqual(expect.any(String))
  })
})
