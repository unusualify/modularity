import { describe, expect, test, vi, beforeEach } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../src/js/config/i18n'
import useItemActions, { makeItemActionsProps } from '../../src/js/hooks/useItemActions.js'

const vuetify = createVuetify({ components, directives })

const mockShouldUseInertia = { value: false }
const mockCastObjectAttributes = vi.fn((action) => ({ ...action }))
const mockDynamicModalOpen = vi.fn()
const mockRouterReload = vi.fn()
const formApiPost = vi.fn()
const formApiPut = vi.fn()
const formApiGet = vi.fn()

vi.mock('@/hooks/useConfig.js', () => ({
  default: () => ({ shouldUseInertia: mockShouldUseInertia })
}))

vi.mock('@/hooks/useCastAttributes.js', () => ({
  default: () => ({ castObjectAttributes: (...args) => mockCastObjectAttributes(...args) })
}))

vi.mock('@/hooks/useDynamicModal.js', () => ({
  default: () => ({ open: mockDynamicModalOpen })
}))

vi.mock('@/utils/itemConditions.js', () => ({
  checkItemConditions: vi.fn(() => true)
}))

vi.mock('@inertiajs/vue3', () => ({
  router: { reload: (...args) => mockRouterReload(...args) }
}))

vi.mock('@/store/api/form.js', () => ({
  default: {
    post: (...args) => formApiPost(...args),
    put: (...args) => formApiPut(...args),
    get: (...args) => formApiGet(...args)
  }
}))

vi.mock('@/store/actions', () => ({ default: {} }))

function createStoreStub(overrides = {}) {
  const store = createStore({
    state: {
      config: { isInertia: false },
      ambient: {}
    },
    getters: {
      userProfile: () => ({}),
      ...overrides.getters
    },
    mutations: {
      __setAlert: () => {},
      SET_MODAL: () => {}
    }
  })
  store.commit = vi.fn(store.commit)
  return store
}

const defaultItem = { id: 1, name: 'Item 1' }

const TestComponent = defineComponent({
  props: {
    item: { type: Object, default: null },
    editedItem: { type: Object, default: null },
    isEditing: { type: Boolean, default: false },
    actions: { type: [Array, Object], default: () => [] },
    ...makeItemActionsProps()
  },
  emits: ['actionComplete', 'action-complete'],
  setup(props, context) {
    const item = props.editedItem ?? props.item ?? defaultItem
    const ctx = { ...context, editedItem: item, item }
    return useItemActions(props, ctx)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: { actions: [], ...props }
  })
}

beforeEach(() => {
  vi.clearAllMocks()
  mockCastObjectAttributes.mockImplementation((action) => ({ ...action }))
  mockShouldUseInertia.value = false
  formApiPost.mockImplementation((url, params, success, error) => {
    success?.({ data: { message: 'Success', variant: 'success' } })
  })
  window.open = vi.fn()
})

describe('useItemActions', () => {
  test('makeItemActionsProps returns isEditing and actions', () => {
    const props = makeItemActionsProps()
    expect(props.isEditing).toBeDefined()
    expect(props.isEditing.default).toBe(false)
    expect(props.actions).toBeDefined()
    expect(props.actions.default()).toEqual([])
  })

  test('returns hasActions, hasVisibleActions, allActions, visibleActions, shouldShowAction, handleAction', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(wrapper.vm.hasActions).toBeDefined()
    expect(wrapper.vm.hasVisibleActions).toBeDefined()
    expect(wrapper.vm.allActions).toBeDefined()
    expect(wrapper.vm.visibleActions).toBeDefined()
    expect(typeof wrapper.vm.shouldShowAction).toBe('function')
    expect(typeof wrapper.vm.handleAction).toBe('function')
  })

  test('hasActions false when no actions', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { actions: [] })
    expect(wrapper.vm.hasActions).toBe(false)
    expect(wrapper.vm.visibleActions).toEqual([])
  })

  test('hasActions true when actions provided', async () => {
    const store = createStoreStub()
    const actions = [
      { type: 'blank', endpoint: '/export', label: 'Export', creatable: true }
    ]
    const wrapper = await factory(store, { actions, isEditing: false })
    expect(wrapper.vm.hasActions).toBe(true)
    expect(wrapper.vm.visibleActions.length).toBeGreaterThan(0)
  })

  test('handleAction with no type warns and returns', async () => {
    const consoleSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})
    const store = createStoreStub()
    const wrapper = await factory(store, {
      actions: [{ type: null, endpoint: '/x' }],
      isEditing: false
    })
    const action = { type: null, endpoint: '/x' }
    wrapper.vm.handleAction(action)
    expect(consoleSpy).toHaveBeenCalledWith('Action type not specified:', action)
    consoleSpy.mockRestore()
  })

  test('handleAction type blank opens window', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { actions: [] })
    wrapper.vm.handleAction({
      type: 'blank',
      endpoint: 'https://example.com/export'
    })
    expect(window.open).toHaveBeenCalledWith('https://example.com/export', '_blank')
  })

  test('handleAction type blank with no endpoint logs error', async () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.handleAction({ type: 'blank', endpoint: null })
    expect(consoleSpy).toHaveBeenCalledWith('Endpoint not specified for blank action')
    consoleSpy.mockRestore()
  })

  test('handleAction type download creates link and triggers click', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const createElementSpy = vi.spyOn(document, 'createElement')

    wrapper.vm.handleAction({
      type: 'download',
      endpoint: 'https://example.com/file.pdf'
    })

    expect(createElementSpy).toHaveBeenCalledWith('a')
    createElementSpy.mockRestore()
  })

  test('handleAction type download with no endpoint logs error', async () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.handleAction({ type: 'download', endpoint: null })
    expect(consoleSpy).toHaveBeenCalledWith('Endpoint not specified for download action')
    consoleSpy.mockRestore()
  })

  test('handleAction unknown type warns', async () => {
    const consoleSpy = vi.spyOn(console, 'warn').mockImplementation(() => {})
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.handleAction({ type: 'unknown', endpoint: '/x' })
    expect(consoleSpy).toHaveBeenCalledWith('Unknown action type:', 'unknown')
    consoleSpy.mockRestore()
  })

  test('handleAction type request with no endpoint logs error', async () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.handleAction({ type: 'request', endpoint: null, params: {} })
    expect(consoleSpy).toHaveBeenCalledWith('Endpoint not specified for request action')
    consoleSpy.mockRestore()
  })

  test('handleAction type request calls api post', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.handleAction({
      type: 'request',
      endpoint: '/api/items/1/action',
      params: { key: 'value' }
    })
    expect(formApiPost).toHaveBeenCalledWith(
      '/api/items/1/action',
      { key: 'value' },
      expect.any(Function),
      expect.any(Function)
    )
    expect(wrapper.vm.loading).toBe(false)
  })

  test('handleAction type request sets loading while request is in flight', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    let resolveRequest

    formApiPost.mockImplementation((url, params, success) => {
      expect(wrapper.vm.loading).toBe(true)
      expect(wrapper.vm.isActionLoading({
        type: 'request',
        endpoint: '/api/items/1/action',
      })).toBe(true)
      resolveRequest = () => success({ data: { message: 'Done', variant: 'success' } })
    })

    const action = {
      type: 'request',
      endpoint: '/api/items/1/action',
      params: {},
    }

    wrapper.vm.handleAction(action)
    resolveRequest()

    expect(wrapper.vm.loading).toBe(false)
    expect(wrapper.vm.isActionLoading(action)).toBe(false)
  })

  test('handleAction type request clears loading on error', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const action = {
      type: 'request',
      endpoint: '/api/items/1/action',
      params: {},
    }

    formApiPost.mockImplementation((url, params, success, error) => {
      error({ data: { message: 'Server error' } })
    })

    wrapper.vm.handleAction(action)

    expect(wrapper.vm.loading).toBe(false)
    expect(wrapper.vm.isActionLoading(action)).toBe(false)
  })

  test('handleAction type request opens response modal when configured', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { isEditing: true, item: { id: 1, remote_id: 42 } })
    formApiPut.mockImplementation((url, params, success) => {
      success({ data: { data: { id: 42, name: 'Remote package' } } })
    })

    wrapper.vm.handleAction({
      type: 'request',
      method: 'put',
      endpoint: '/api/items/1/preview-remote',
      params: {},
      responseDisplay: 'fields',
      responseFields: [
        { key: 'name', label: 'Name' },
      ],
      responseModalAttributes: {
        title: 'Preview API record',
        confirmText: 'Close',
      },
    })

    expect(mockDynamicModalOpen).toHaveBeenCalledWith(null, expect.objectContaining({
      modalProps: expect.objectContaining({
        title: 'Preview API record',
        description: expect.stringContaining('Name'),
      })
    }))
    expect(mockDynamicModalOpen.mock.calls[0][1].modalProps.description).toContain('Remote package')
    expect(mockDynamicModalOpen.mock.calls[0][1].modalProps.description).not.toContain('"id": 42')
  })

  test('handleAction type request opens raw response modal when configured', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { isEditing: true, item: { id: 1, remote_id: 42 } })
    formApiPut.mockImplementation((url, params, success) => {
      success({ data: { data: { id: 42, name: 'Remote package' } } })
    })

    wrapper.vm.handleAction({
      type: 'request',
      method: 'put',
      endpoint: '/api/items/1/preview-remote',
      params: {},
      responseDisplay: 'raw',
      responseModalAttributes: {
        title: 'Preview API record',
        confirmText: 'Close',
      },
    })

    expect(mockDynamicModalOpen).toHaveBeenCalledWith(null, expect.objectContaining({
      modalProps: expect.objectContaining({
        description: JSON.stringify({ id: 42, name: 'Remote package' }, null, 2),
      })
    }))
  })

  test('handleAction type request prefers server display payload when present', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { isEditing: true, item: { id: 1, remote_id: 42 } })
    formApiPut.mockImplementation((url, params, success) => {
      success({
        data: {
          data: { id: 42, name: 'Remote package', description: 'Ignored in display' },
          display_mode: 'fields',
          display: [
            { key: 'name', label: 'Package name', value: 'Custom label from server' },
          ],
        }
      })
    })

    wrapper.vm.handleAction({
      type: 'request',
      method: 'put',
      endpoint: '/api/items/1/preview-remote',
      params: {},
      responseModalAttributes: {
        title: 'Preview API record',
        confirmText: 'Close',
      },
    })

    expect(mockDynamicModalOpen.mock.calls[0][1].modalProps.description).toContain('Custom label from server')
    expect(mockDynamicModalOpen.mock.calls[0][1].modalProps.description).toContain('Package name')
  })

  test('handleAction type request with reloadOnSuccess calls success callback', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    formApiPost.mockImplementation((url, params, success) => {
      success({ data: { message: 'Done', variant: 'success' } })
    })

    wrapper.vm.handleAction({
      type: 'request',
      endpoint: '/api/items/1/action',
      params: {},
      reloadOnSuccess: true
    })

    expect(store.commit).toHaveBeenCalledWith('__setAlert', {
      message: 'Done',
      variant: 'success'
    })
    expect(wrapper.emitted('actionComplete')).toBeTruthy()
  })

  test('handleAction type request with reloadOnSuccess and Inertia uses router.reload', async () => {
    mockShouldUseInertia.value = true
    const store = createStoreStub()
    const wrapper = await factory(store)
    formApiPost.mockImplementation((url, params, success) => {
      success({ data: { message: 'Done', variant: 'success' } })
    })

    wrapper.vm.handleAction({
      type: 'request',
      endpoint: '/api/items/1/action',
      params: {},
      reloadOnSuccess: true
    })

    expect(mockRouterReload).toHaveBeenCalledWith({ only: ['formAttributes'] })
  })

  test('handleAction type request emits actionComplete on success', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const action = { type: 'request', endpoint: '/api/action', params: {} }
    formApiPost.mockImplementation((url, params, success) => {
      success({ data: { message: 'OK', variant: 'success' } })
    })

    wrapper.vm.handleAction(action)

    expect(wrapper.emitted('actionComplete')).toBeTruthy()
    expect(wrapper.emitted('actionComplete')[0][0]).toMatchObject({ action })
  })

  test('handleAction type request commits alert on error', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    formApiPost.mockImplementation((url, params, success, error) => {
      error({ data: { message: 'Server error' } })
    })

    wrapper.vm.handleAction({
      type: 'request',
      endpoint: '/api/action',
      params: {}
    })

    expect(store.commit).toHaveBeenCalledWith('__setAlert', {
      message: 'Server error',
      variant: 'error'
    })
  })

  test('handleAction type modal commits SET_MODAL', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const action = {
      type: 'modal',
      endpoint: '/api/modal',
      label: 'Open Form',
      schema: {}
    }

    wrapper.vm.handleAction(action)

    expect(store.commit).toHaveBeenCalledWith('SET_MODAL', expect.objectContaining({
      show: true,
      title: 'Open Form',
      component: 'ue-form'
    }))
  })

  test('handleAction type request with hasConfirmation opens dynamicModal', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.handleAction({
      type: 'request',
      endpoint: '/api/action',
      params: {},
      hasConfirmation: true
    })

    expect(mockDynamicModalOpen).toHaveBeenCalled()
  })

  test('handleAction replaces :id in endpoint with editingItem.id', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      editedItem: { id: 42, name: 'Item 42' }
    })
    wrapper.vm.handleAction({
      type: 'blank',
      endpoint: '/items/:id/export'
    })
    expect(window.open).toHaveBeenCalledWith('/items/42/export', '_blank')
  })

  test('filteredActions hides action when hideOnCondition and validateAction false', async () => {
    const itemConditions = await import('@/utils/itemConditions.js')
    itemConditions.checkItemConditions.mockReturnValue(false)

    const store = createStoreStub()
    const actions = [
      {
        type: 'blank',
        endpoint: '/x',
        creatable: true,
        conditions: [['id', '=', 1]],
        hideOnCondition: true
      }
    ]
    const wrapper = await factory(store, { actions, isEditing: false })

    expect(wrapper.vm.visibleActions.length).toBe(0)
  })

  test('shouldShowAction returns true when no editingItem', async () => {
    const TestNoItem = defineComponent({
      props: { ...makeItemActionsProps() },
      setup(props, context) {
        return useItemActions(props, { ...context, editedItem: null, item: null, actionItem: null })
      },
      template: '<div />'
    })
    const store = createStoreStub()
    const w = mount(TestNoItem, {
      global: { plugins: [vuetify, i18n, store] },
      props: { actions: [{ creatable: false }], isEditing: false }
    })
    expect(w.vm.shouldShowAction({ creatable: false })).toBe(true)
  })

  test('handleAction type request without editingItem calls api post for table toolbar actions', async () => {
    const TestNoItem = defineComponent({
      props: { ...makeItemActionsProps() },
      emits: ['actionComplete'],
      setup(props, context) {
        return useItemActions(props, { ...context, actionItem: null })
      },
      template: '<div />'
    })
    const store = createStoreStub()
    const w = mount(TestNoItem, {
      global: { plugins: [vuetify, i18n, store] },
      props: { actions: [], isEditing: false }
    })

    w.vm.handleAction({
      type: 'request',
      endpoint: '/admin/packages/sync_remote_all',
      params: {},
      hasConfirmation: true,
    })

    expect(mockDynamicModalOpen).toHaveBeenCalled()
  })
})
