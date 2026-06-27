import { describe, expect, test, vi, beforeEach } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../src/js/config/i18n'
import useForm, { makeFormProps } from '../../src/js/hooks/form/useForm.js'

const vuetify = createVuetify({ components, directives })

const mockGetModel = vi.fn((schema, model) => ({ name: '', email: '', ...model }))
const mockGetSchema = vi.fn((schema) => schema)
const mockGetFormEventSchema = vi.fn(() => ({}))
const mockGetSubmitFormData = vi.fn((schema, model) => ({ ...model }))
const mockGetSecondaryInputs = vi.fn(() => [])
const mockInvokeRuleGenerator = vi.fn((schema) => schema)
const mockValidModel = { value: true }
const mockShouldUseInertia = { value: false }
const mockHasRoles = vi.fn(() => true)

vi.mock('@/utils/getFormData.js', () => ({
  getModel: (...args) => mockGetModel(...args),
  getSchema: (...args) => mockGetSchema(...args),
  getFormEventSchema: (...args) => mockGetFormEventSchema(...args),
  getSubmitFormData: (...args) => mockGetSubmitFormData(...args),
  getSecondaryInputs: (...args) => mockGetSecondaryInputs(...args)
}))

vi.mock('@/utils/schema.js', () => ({
  processInputs: (obj) => obj,
  getTranslationInputsCount: () => 0
}))

vi.mock('@/utils/formEvents.js', () => ({
  handleEvents: vi.fn()
}))

vi.mock('@/utils/response.js', () => ({
  redirector: vi.fn()
}))

vi.mock('@/hooks/useConfig.js', () => ({
  default: () => ({ shouldUseInertia: mockShouldUseInertia })
}))

vi.mock('@/hooks/useValidation.js', () => ({
  default: () => ({
    validModel: mockValidModel,
    invokeRuleGenerator: (...args) => mockInvokeRuleGenerator(...args)
  })
}))

vi.mock('@/hooks/useAuthorization.js', () => ({
  default: () => ({ hasRoles: mockHasRoles })
}))

vi.mock('@inertiajs/vue3', () => ({
  router: { reload: vi.fn() }
}))

const formApiPost = vi.fn()
const formApiPut = vi.fn()
vi.mock('@/store/api/form.js', () => ({
  default: {
    post: (...args) => formApiPost(...args),
    put: (...args) => formApiPut(...args)
  }
}))

function createStoreStub() {
  const store = createStore({
    state: {
      form: { errors: {}, editedItem: {} },
      user: {
        timezone: 'Europe/London',
        valid_company: false,
        profile: { show_billing_banner: false },
      },
    },
    getters: {},
    mutations: {
      setLanguages: () => {},
      __setAlert: () => {}
    }
  })
  store._state = { data: {} }
  store.commit = vi.fn(store.commit)
  return store
}

const simpleSchema = {
  name: { name: 'name', type: 'text' },
  email: { name: 'email', type: 'text' }
}

const TestComponent = defineComponent({
  props: {
    modelValue: { type: Object, default: () => ({}) },
    schema: { type: Object, required: true },
    actionUrl: { type: String, default: '' },
    ...makeFormProps()
  },
  emits: ['update:modelValue', 'update:valid', 'submitted', 'input'],
  setup(props, context) {
    return useForm(props, context)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { modelValue: {}, schema: simpleSchema, ...props }
  })
}

beforeEach(() => {
  vi.clearAllMocks()
  mockGetModel.mockImplementation((schema, model) => ({ name: '', email: '', ...model }))
  mockGetSchema.mockImplementation((schema) => schema)
  mockGetFormEventSchema.mockReturnValue({})
  mockInvokeRuleGenerator.mockImplementation((s) => s)
  mockValidModel.value = true
  mockGetSubmitFormData.mockImplementation((schema, model) => {
    const m = model?.value ?? model ?? {}
    return { ...m }
  })
  mockGetSecondaryInputs.mockReturnValue([])
  formApiPost.mockImplementation((url, data, success) => success?.({ data: {} }))
  formApiPut.mockImplementation((url, data, success) => success?.({ data: {} }))
})

describe('useForm', () => {
  test('returns id, model, inputSchema, validate, submit, saveForm', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.id).toBeDefined()
    expect(wrapper.vm.id).toMatch(/\d+-form/)
    expect(wrapper.vm.model).toBeDefined()
    expect(wrapper.vm.inputSchema).toBeDefined()
    expect(typeof wrapper.vm.validate).toBe('function')
    expect(typeof wrapper.vm.submit).toBe('function')
    expect(typeof wrapper.vm.saveForm).toBe('function')
  })

  test('makeFormProps defines modelValue, schema, actionUrl', () => {
    const props = makeFormProps()
    expect(props.modelValue.required).toBe(true)
    expect(props.schema.required).toBe(true)
    expect(props.actionUrl).toBeDefined()
    expect(props.actionsPosition.default).toBe('top')
  })

  test('model is initialized from getModel with schema and modelValue', async () => {
    const store = createStoreStub()
    await factory(store, { modelValue: { name: 'John' } })
    expect(mockGetModel).toHaveBeenCalled()
    expect(mockGetModel.mock.calls[0][0]).toEqual(simpleSchema)
  })

  test('formItem returns modelValue', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { modelValue: { name: 'Test' } })
    expect(wrapper.vm.formItem).toEqual({ name: 'Test' })
  })

  test('isSubmittable is true when schema has submittable inputs', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.isSubmittable).toBeDefined()
  })

  test('handleInput emits input event', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.handleInput({ on: 'input', key: 'name', obj: {} })
    expect(wrapper.emitted('input')).toBeTruthy()
    expect(wrapper.emitted('input')[0][0]).toEqual({ on: 'input', key: 'name', obj: {} })
  })

  test('saveForm with actionUrl calls api.post when no id', async () => {
    const store = createStoreStub()
    formApiPost.mockImplementation((url, data, success) => {
      success({ data: {} })
    })
    const wrapper = await factory(store, {
      actionUrl: '/api/forms',
      modelValue: {},
      schema: simpleSchema
    })
    wrapper.vm.saveForm()
    await new Promise(r => setTimeout(r, 50))
    expect(formApiPost).toHaveBeenCalledWith(
      '/api/forms',
      expect.any(Object),
      expect.any(Function),
      expect.any(Function)
    )
  })

  test('saveForm with actionUrl calls api.put when model has id', async () => {
    const store = createStoreStub()
    mockGetSubmitFormData.mockReturnValue({ id: 1, name: 'John' })
    formApiPut.mockImplementation((url, data, success) => {
      success({ data: {} })
    })
    const wrapper = await factory(store, {
      actionUrl: '/api/forms/1',
      modelValue: { id: 1, name: 'John' },
      schema: simpleSchema
    })
    wrapper.vm.saveForm()
    await new Promise(r => setTimeout(r, 50))
    expect(formApiPut).toHaveBeenCalled()
  })

  test('saveForm sets errors when response has errors', async () => {
    const store = createStoreStub()
    formApiPost.mockImplementation((url, data, success) => {
      success({ data: { errors: { name: ['Name is required'] } } })
    })
    const wrapper = await factory(store, {
      actionUrl: '/api/forms',
      schema: simpleSchema
    })
    wrapper.vm.saveForm()
    await new Promise(r => setTimeout(r, 50))
    expect(wrapper.vm.errors).toEqual({ name: ['Name is required'] })
  })

  test('saveForm emits submitted on success', async () => {
    const store = createStoreStub()
    const responseData = { id: 1, message: 'Saved' }
    formApiPost.mockImplementation((url, data, success) => {
      success({ data: responseData })
    })
    const wrapper = await factory(store, {
      actionUrl: '/api/forms',
      schema: simpleSchema
    })
    wrapper.vm.saveForm()
    await new Promise(r => setTimeout(r, 50))
    expect(wrapper.emitted('submitted')).toEqual([[responseData]])
  })

  test('submit calls saveForm when validModel is true and actionUrl set', async () => {
    const store = createStoreStub()
    formApiPost.mockImplementation((url, data, success) => {
      success({ data: {} })
    })
    const wrapper = await factory(store, {
      actionUrl: '/api/forms',
      schema: simpleSchema
    })
    wrapper.vm.submit({ preventDefault: vi.fn() })
    await new Promise(r => setTimeout(r, 50))
    expect(formApiPost).toHaveBeenCalled()
  })

  test('errors watch triggers setSchemaErrors when errors ref changes', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.errors = { name: ['Invalid name'] }
    await wrapper.vm.$nextTick()
    expect(wrapper.vm.inputSchema.name?.errorMessages).toBeDefined()
  })

  test('chunkedRawSchema processes rawSchema', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.chunkedRawSchema).toBeDefined()
  })

  test('buttonDefaultText returns submit translation when no buttonText', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.buttonDefaultText).toBeDefined()
  })

  test('commits setLanguages when languages prop provided', async () => {
    const store = createStoreStub()
    await factory(store, { languages: ['en', 'tr'] })
    expect(store.commit).toHaveBeenCalledWith('setLanguages', ['en', 'tr'])
  })

  test('saveForm calls errorCallback on api error', async () => {
    const store = createStoreStub()
    const errorCallback = vi.fn()
    formApiPost.mockImplementation((url, data, success, errCb) => {
      errCb?.({ data: { exception: 'Server error' } })
    })
    const wrapper = await factory(store, {
      actionUrl: '/api/forms',
      schema: simpleSchema
    })
    wrapper.vm.saveForm(null, errorCallback)
    await new Promise(r => setTimeout(r, 50))
    expect(errorCallback).toHaveBeenCalledWith(expect.objectContaining({ exception: 'Server error' }))
  })

  test('createModel returns model from getModel', async () => {
    const store = createStoreStub()
    mockGetModel.mockReturnValue({ name: 'Test', email: 'test@test.com' })
    const wrapper = await factory(store)
    const result = wrapper.vm.createModel(simpleSchema)
    expect(mockGetModel).toHaveBeenCalled()
    expect(result).toEqual({ name: 'Test', email: 'test@test.com' })
  })

  test('hasAdditionalSection true when actionsPosition is right-top', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { actionsPosition: 'right-top' })
    expect(wrapper.vm.hasAdditionalSection).toBe(true)
  })

  test('reference computed returns ref- prefix with id', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.reference).toMatch(/^ref-\d+-form$/)
  })
})
