import { describe, expect, test, vi, beforeEach } from 'vitest'
import { createStore } from 'vuex'
import { defineComponent, ref, reactive } from 'vue'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import i18n from '../../../src/js/config/i18n'
import useTableItemActions, { makeTableItemActionsProps } from '../../../src/js/hooks/table/useTableItemActions.js'

const vuetify = createVuetify({ components, directives })

const mockCan = vi.fn(() => true)
const mockOpen = vi.fn()
const mockCastObjectAttributes = vi.fn((obj) => ({ ...obj }))
const mockGenerateButtonProps = vi.fn(() => ({}))

vi.mock('@/hooks/useAuthorization.js', () => ({
  default: () => ({ can: (...args) => mockCan(...args) })
}))

vi.mock('@/hooks/useDynamicModal.js', () => ({
  default: () => ({ open: (...args) => mockOpen(...args) })
}))

vi.mock('@/hooks/useCastAttributes.js', () => ({
  default: () => ({ castObjectAttributes: (...args) => mockCastObjectAttributes(...args) })
}))

vi.mock('@/hooks/utils/useGenerate.js', () => ({
  default: () => ({ generateButtonProps: (...args) => mockGenerateButtonProps(...args) })
}))

vi.mock('@/utils/itemConditions.js', () => ({
  checkItemConditions: vi.fn(() => true)
}))


const { mockDatatableDelete, mockDatatableForceDelete, mockDatatableRestore, mockFormApiPut } = vi.hoisted(() => ({
  mockDatatableDelete: vi.fn(),
  mockDatatableForceDelete: vi.fn(),
  mockDatatableRestore: vi.fn(),
  mockFormApiPut: vi.fn()
}))

vi.mock('@/store/api/form.js', () => ({
  default: { put: (...args) => mockFormApiPut(...args) }
}))

vi.mock('@/store/api/datatable.js', () => ({
  default: {
    delete: (...args) => mockDatatableDelete(...args),
    forceDelete: (...args) => mockDatatableForceDelete(...args),
    restore: (...args) => mockDatatableRestore(...args),
    bulkDelete: vi.fn(),
    bulkPublish: vi.fn()
  }
}))

beforeEach(() => {
  vi.clearAllMocks()
  mockCan.mockReturnValue(true)
  window.open = vi.fn()
})

function createStoreStub() {
  return createStore({
    state: {
      ambient: { isHot: false, appName: '', appEnv: '' },
      config: { isInertia: false, isRequestInProgress: false },
    },
    getters: { userProfile: () => ({}) },
    mutations: {},
  })
}

const createTableItem = (opts = {}) => {
  const editedItem = ref(opts.initialEditedItem ?? { id: 1 })
  const isSoftDeletableItem = ref(opts.isSoftDeletable ?? false)
  return {
    editedItem,
    isSoftDeletableItem,
    setEditedItem: vi.fn((item) => { editedItem.value = item }),
    isDeleted: (item) => !!(item?.deleted_at),
    isSoftDeletable: (item) => !!(item?.deleted_at)
  }
}

const createTableNames = () => ({
  permissionName: ref('posts'),
  transNamePlural: ref('Posts'),
  deleteDialogTitle: ref('Delete'),
  deleteDialogDescription: ref('Confirm delete')
})

const createTableForms = () => ({
  openForm: vi.fn(),
  customFormModalActive: ref(false),
  customFormSchema: ref({}),
  customFormModel: ref({}),
  customFormAttributes: ref({}),
  customFormModalAttributes: ref({})
})

const createLoadItems = () => vi.fn()

let tableFormsRef = null
let tableItemRef = null
const TestComponent = defineComponent({
  props: {
    endpoints: { type: Object, default: () => ({ index: '/api/posts', edit: '/api/posts/:id/edit', destroy: '/api/posts/:id', forceDelete: '/api/posts/:id/force-delete', update: '/api/posts/:id' }) },
    editOnModal: { type: Boolean, default: true },
    rowActions: { type: Array, default: () => [] },
    ...makeTableItemActionsProps()
  },
  setup(props, context) {
    const TableItem = createTableItem()
    tableItemRef = TableItem
    const TableNames = createTableNames()
    const TableForms = createTableForms()
    tableFormsRef = TableForms
    const loadItems = createLoadItems()
    const ctx = {
      ...context,
      TableItem,
      TableNames,
      TableForms,
      loadItems
    }
    return useTableItemActions(props, ctx)
  },
  template: '<div />'
})

async function factory(store, props = {}) {
  return mount(TestComponent, {
    global: { plugins: [vuetify, i18n, store] },
    props: {
      endpoints: {
        index: '/api/posts',
        edit: '/api/posts/:id/edit',
        destroy: '/api/posts/:id',
        forceDelete: '/api/posts/:id/force-delete',
        update: '/api/posts/:id'
      },
      ...props
    }
  })
}

describe('useTableItemActions', () => {
  test('makeTableItemActionsProps returns rowActions, rowActionsType', () => {
    const props = makeTableItemActionsProps()
    expect(props.rowActions).toBeDefined()
    expect(props.rowActionsType).toBeDefined()
    expect(props.rowActionsType.default).toBe('inline')
  })

  test('returns itemAction, itemHasAction, actionEvents, actionShowingType, visibleRowActions', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    expect(typeof wrapper.vm.itemAction).toBe('function')
    expect(typeof wrapper.vm.itemHasAction).toBe('function')
    expect(wrapper.vm.actionEvents).toBeDefined()
    expect(wrapper.vm.actionEvents.reset).toBeDefined()
    expect(wrapper.vm.actionShowingType).toBeDefined()
    expect(wrapper.vm.visibleRowActions).toBeDefined()
  })

  test('itemHasAction returns true for allowed action', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const result = wrapper.vm.itemHasAction({ id: 1 }, { name: 'edit' })
    expect(result).toBe(true)
  })

  test('itemAction edit opens form when editOnModal', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { editOnModal: true })
    wrapper.vm.itemAction({ id: 1, name: 'Test' }, 'edit')
    expect(tableFormsRef.openForm).toHaveBeenCalled()
  })

  test('itemAction link opens window with item href', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.itemAction({ id: 1, href: 'https://example.com/item/1' }, 'link')
    expect(window.open).toHaveBeenCalledWith('https://example.com/item/1', '_blank')
  })

  test('itemAction duplicate opens form with item without id', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const item = { id: 1, name: 'Copy' }
    wrapper.vm.itemAction(item, 'duplicate')
    expect(tableItemRef.setEditedItem).toHaveBeenCalledWith(expect.objectContaining({ name: 'Copy' }))
    expect(tableItemRef.setEditedItem.mock.calls[0][0]).not.toHaveProperty('id')
    expect(tableFormsRef.openForm).toHaveBeenCalled()
  })

  test('actionEvents.reset clears event and payload', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.actionEvents.event = 'test'
    wrapper.vm.actionEvents.payload = { x: 1 }
    wrapper.vm.actionEvents.reset()
    expect(wrapper.vm.actionEvents.event).toBeNull()
    expect(wrapper.vm.actionEvents.payload).toBeNull()
  })

  test('visibleRowActions returns array from rowActions', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      rowActions: [
        { name: 'edit', icon: 'mdi-pencil' },
        { name: 'delete', icon: 'mdi-delete' }
      ]
    })
    expect(wrapper.vm.visibleRowActions).toBeDefined()
    expect(Array.isArray(wrapper.vm.visibleRowActions)).toBe(true)
  })

  test('itemHasAction returns false for forceDelete when item not deleted', async () => {
    mockCan.mockReturnValue(true)
    const store = createStoreStub()
    const wrapper = await factory(store)
    const result = wrapper.vm.itemHasAction({ id: 1 }, { name: 'forceDelete' })
    expect(result).toBe(false)
  })

  test('itemHasAction returns true for restore when item is deleted', async () => {
    mockCan.mockReturnValue(true)
    const store = createStoreStub()
    const wrapper = await factory(store)
    const result = wrapper.vm.itemHasAction({ id: 1, deleted_at: '2024-01-01' }, { name: 'restore' })
    expect(result).toBe(true)
  })

  test('itemAction delete sets dialog event', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    tableItemRef.editedItem.value = { id: 1 }
    tableItemRef.isSoftDeletableItem.value = false
    wrapper.vm.itemAction({ id: 1, name: 'Test' }, 'delete')
    expect(wrapper.vm.actionEvents.event).toBe('dialog')
    expect(wrapper.vm.actionEvents.payload.type).toBe('delete')
    expect(typeof wrapper.vm.actionEvents.payload.callback).toBe('function')
  })

  test('itemAction restore sets process event', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.itemAction({ id: 1, name: 'Test' }, 'restore')
    expect(wrapper.vm.actionEvents.event).toBe('process')
    expect(wrapper.vm.actionEvents.payload.type).toBe('restore')
    expect(tableItemRef.setEditedItem).toHaveBeenCalledWith({ id: 1, name: 'Test' })
  })

  test('itemAction bulkDelete sets dialog event', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.itemAction(null, { name: 'bulkDelete' })
    expect(wrapper.vm.actionEvents.event).toBe('dialog')
    expect(wrapper.vm.actionEvents.payload.type).toBe('bulkDelete')
  })

  test('itemAction default action with url opens window', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.itemAction({ id: 5 }, { name: 'export', url: '/api/items/:id/export' })
    expect(window.open).toHaveBeenCalledWith('/api/items/5/export', '_self')
  })

  test('itemAction default action with hasDialog sets dialog event', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.itemAction({ id: 5 }, { name: 'export', url: '/api/items/:id/export', hasDialog: true })
    expect(wrapper.vm.actionEvents.event).toBe('dialog')
    expect(wrapper.vm.actionEvents.payload.callback).toBeDefined()
  })

  test('itemAction default action with modalService opens DynamicModal', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.itemAction({ id: 1 }, { name: 'custom', modalService: { component: 'MyModal', title: 'Test' } })
    expect(mockOpen).toHaveBeenCalled()
  })

  test('itemAction edit without editOnModal opens window', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { editOnModal: false })
    wrapper.vm.itemAction({ id: 42, name: 'Test' }, 'edit')
    expect(window.open).toHaveBeenCalledWith('/api/posts/42/edit', '_blank')
  })

  test('itemAction edit without editOnModal and no edit endpoint logs error', async () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const store = createStoreStub()
    const wrapper = await factory(store, { editOnModal: false, endpoints: { index: '/api/posts' } })
    wrapper.vm.itemAction({ id: 42 }, 'edit')
    expect(consoleSpy).toHaveBeenCalledWith('No edit endpoint found in endpoints of props')
    consoleSpy.mockRestore()
  })

  test('itemAction link with endpoints.show when item has no href', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      endpoints: { index: '/api/posts', show: '/api/posts/:id' }
    })
    wrapper.vm.itemAction({ id: 10 }, 'link')
    expect(window.open).toHaveBeenCalledWith('/api/posts/10', '_blank')
  })

  test('itemAction link with invalid target defaults to _blank', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    wrapper.vm.itemAction({ id: 1, href: '/item/1', target: 'invalid' }, 'link')
    expect(window.open).toHaveBeenCalledWith('/item/1', '_blank')
  })

  test('itemAction show with show:true sets showData event', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const item = { id: 1, name: 'Item' }
    wrapper.vm.itemAction(item, { name: 'show', show: true })
    expect(wrapper.vm.actionEvents.event).toBe('showData')
    expect(wrapper.vm.actionEvents.payload.data).toEqual(item)
  })

  test('itemAction show with only filters data', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const item = { id: 1, name: 'Item', email: 'a@b.com' }
    wrapper.vm.itemAction(item, { name: 'show', show: true, only: ['name'] })
    expect(wrapper.vm.actionEvents.event).toBe('showData')
    expect(wrapper.vm.actionEvents.payload.data).toEqual({ name: 'Item' })
  })

  test('itemAction with form opens custom form modal', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const item = { id: 1 }
    wrapper.vm.itemAction(item, {
      name: 'custom',
      form: { attributes: { schema: { name: {} }, modelValue: {} } }
    })
    expect(tableFormsRef.customFormModalActive.value).toBe(true)
    expect(wrapper.vm.actionEvents.event).toBe('showCustomForm')
  })

  test('itemAction switch sets process event', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store)
    const item = { id: 1, published: false }
    wrapper.vm.itemAction(item, 'switch', true, 'published')
    expect(wrapper.vm.actionEvents.event).toBe('process')
    expect(wrapper.vm.actionEvents.payload.type).toBe('publish')
    expect(item.published).toBe(true)
  })

  test('actionShowingType returns dropdown when rowActionsType is dropdown', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, { rowActionsType: 'dropdown', rowActions: [{ name: 'edit' }] })
    expect(wrapper.vm.actionShowingType).toBe('dropdown')
  })

  test('visibleRowActions with action is v-btn uses v-btn component', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      rowActions: [{ name: 'edit', is: 'v-btn', icon: 'mdi-pencil' }]
    })
    const action = wrapper.vm.visibleRowActions[0]
    expect(action.is).toBe('v-btn')
    expect(action.hasTooltip).toBe(false)
  })

  test('visibleRowActions with href logs error', async () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
    const store = createStoreStub()
    const wrapper = await factory(store, {
      rowActions: [{ name: 'edit', href: '/invalid' }]
    })
    expect(wrapper.vm.visibleRowActions).toEqual([])
    expect(consoleSpy).toHaveBeenCalledWith('href is not supported in row actions', expect.any(Object))
    consoleSpy.mockRestore()
  })

  test('visibleRowActions with object rowActions converts to array', async () => {
    const store = createStoreStub()
    const wrapper = await factory(store, {
      rowActions: { primary: { name: 'edit' }, secondary: { name: 'delete' } }
    })
    expect(wrapper.vm.visibleRowActions.length).toBe(2)
  })
})
