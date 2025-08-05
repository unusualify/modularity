// test/components/v-input-process.test.js
import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'

// Mock global objects before importing components
global.window = global
global.axios = {
  defaults: {
    headers: {
      common: {}
    }
  },
  get: vi.fn(() => new Promise(resolve => {
    setTimeout(() => {
      resolve({
        data: {
          id: 1,
          status: 'preparing',
          status_label: 'Preparing',
          status_icon: 'mdi-clock-outline',
          status_color: 'warning',
          status_card_color: 'warning',
          status_card_variant: 'outlined',
          next_action_label: 'Submit for Confirmation',
          next_action_color: 'primary',
          processable: {
            id: 101,
            name: 'Test Process',
            description: 'Test description'
          }
        }
      });
    }, 1000); // 1 second delay
  })),
  put: vi.fn(() => new Promise(resolve => {
    setTimeout(() => {
      resolve({
        data: {
          message: 'Process updated successfully',
          variant: 'success'
        }
      });
    }, 1000); // 1 second delay
  }))
}

// Mock __log and other global functions
global.__log = vi.fn()
global.__isObject = (obj) => typeof obj === 'object' && obj !== null
global.__isString = (str) => typeof str === 'string'
global.__isset = (val) => val !== undefined && val !== null

// Mock vue-i18n before importing the component
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => key,
    locale: 'en'
  })
}))

// Mock only useAuthorization from @/hooks
let mockHasRoles = vi.fn(() => true)

vi.mock('@/hooks', async () => {
  const actual = await vi.importActual('@/hooks')
  return {
    ...actual,
    useAuthorization: () => ({
      hasRoles: mockHasRoles
    })
  }
})

// Now import the components
import VInputProcess from '../../src/js/components/inputs/Process.vue'

// Mock store
const mockStore = {
  getters: {
    isSuperAdmin: true,
    userRoles: ['admin']
  },
  commit: vi.fn()
}

// Add this helper function after the mockStore definition
function createMockStore(overrides = {}) {
  return {
    getters: {
      isSuperAdmin: false,
      userRoles: ['admin'],
      userPermissions: {},
      userProfile: { id: 1 },
      ...overrides
    },
    commit: vi.fn(),
    dispatch: vi.fn(),
    state: {}
  }
}

// Update your factory function to accept a custom store
async function factory(props = {}, options = {}, customStore = null) {
  const store = customStore || mockStore

  return await mount(VInputProcess, {
    global: {
      // Add provide to mock both modalService and store injections
      provide: {
        modalService: {
          open: vi.fn(),
          close: vi.fn(),
          state: {
            visible: false,
            component: null,
            props: {},
            emits: {},
            slots: {},
            data: undefined,
            onClose: undefined,
            modalProps: {}
          }
        },
        // Add store injection for useStore() hook
        store: store
      },
      mocks: {
        $store: store, // Keep this for Options API compatibility
        $t: (key) => key,
        $log: vi.fn(),
        $lodash: {
          get: (obj, path, defaultValue) => {
            return path.split('.').reduce((o, p) => (o ? o[p] : defaultValue), obj)
          },
          omit: (obj, keys) => {
            const result = {...obj}
            keys.forEach(key => delete result[key])
            return result
          }
        },
        $hasRoles: (roles) => {
          if (typeof roles === 'string') {
            roles = roles.split(',').map(role => role.trim())
          }
          const userRoles = store.getters.userRoles || []
          return userRoles.some(role => roles.includes(role))
        },
        $isset: (val) => val !== undefined && val !== null
      },
      stubs: {
        'ue-modal': true,
        'ue-form': true,
        'ue-list-section': true,
        'ue-filepond-preview': true,
        'v-btn-secondary': true,
        'v-btn-primary': true,
        'v-card': true,
        'v-card-text': true,
        'v-card-actions': true,
        'v-skeleton-loader': true,
        'v-btn': true,
        'v-chip': true,
        'v-row': true,
        'v-col': true,
        'v-spacer': true,
        'v-divider': true,
        'v-alert': true,
        'v-textarea': true,
        'v-icon': true
      }
    },
    ...options,
    props: {
      fetchEndpoint: '/api/process/:id',
      updateEndpoint: '/api/process/:id',
      ...props
    }
  })
}

// Reset mocks before each test
beforeEach(() => {
  vi.clearAllMocks()
})

describe('VInputProcess tests', () => {
  test('renders the component', async () => {
    const wrapper = await factory({ modelValue: 1 })
    expect(wrapper.exists()).toBe(true)
  })

  test('shows loading state initially when no process is provided', async () => {
    const wrapper = await factory({ modelValue: 1 })

    expect(wrapper.vm.loading).toBe(true)
  })

  test('fetches process data when input ID is provided', async () => {
    // Mock axios.get to resolve immediately with test data
    global.axios.get.mockImplementationOnce(() => Promise.resolve({
      status: 200,
      data: {
        id: 1,
        status: 'preparing',
        status_label: 'Preparing',
        processable: {
          id: 101,
          name: 'Test Process'
        }
      }
    }));

    const wrapper = await factory({ modelValue: 1 });

    // Wait for the next tick to allow the component to process the axios response
    await wrapper.vm.$nextTick();

    // Wait for loading to complete
    await vi.waitUntil(() => wrapper.vm.loading === false);

    // Now check that data is loaded
    expect(global.axios.get).toHaveBeenCalledWith('/api/process/1');
    expect(wrapper.vm.processModel).toBeTruthy();
    expect(wrapper.vm.processableModel).toBeTruthy();
  });

  test('displays process title and status correctly', async () => {
    const processData = {
      id: 1,
      status: 'preparing',
      status_label: 'Preparing',
      status_icon: 'mdi-clock-outline',
      status_color: 'warning',
      processable: {
        id: 101,
        name: 'Test Process'
      }
    }

    const wrapper = await factory({
      modelValue: 1,
      process: processData,
      processableTitle: 'name'
    })

    expect(wrapper.vm.title).toBe('Test Process')
    expect(wrapper.vm.processModel.status_label).toBe('Preparing')
  })

  test('formats schema correctly', async () => {
    const processData = {
      id: 1,
      status: 'preparing',
      processable: {
        id: 101,
        name: 'Test Process',
        description: 'Test description'
      }
    }

    const schema = {
      name: {
        type: 'text',
        label: 'Name'
      },
      description: {
        type: 'textarea',
        label: 'Description'
      }
    }

    const wrapper = await factory({
      modelValue: 1,
      process: processData,
      schema: schema
    })

    expect(wrapper.vm.formSchema).toEqual(schema)
  })

  test('calls updateProcess when triggered', async () => {
    const processData = {
      id: 1,
      status: 'preparing',
      next_action_label: 'Submit for Confirmation',
      next_action_color: 'primary',
      processable: {
        id: 101,
        name: 'Test Process'
      }
    }

    const schema = {
      name: {
        type: 'text',
        name: 'name'
      }
    }

    // Mock axios.put to track the call
    const axiosPutSpy = vi.spyOn(global.axios, 'put').mockResolvedValue({
      status: 200,
      data: {
        message: 'Process updated successfully',
        variant: 'success',
        process_status: 'waiting_for_confirmation'
      }
    })

    const wrapper = await factory({
      modelValue: 1,
      process: processData,
      schema: schema
    })

    // Mock methods and form
    wrapper.vm.UeForm = {
      validModel: true,
      model: {
        id: 101,
        name: 'Updated Test Process'
      },
      validate: vi.fn().mockResolvedValue(true),
      VForm: {
        resetValidation: vi.fn()
      }
    }

    // Call the method directly instead of spying
    await wrapper.vm.updateProcess('waiting_for_confirmation')

    // Verify the axios call was made with correct parameters
    await expect(axiosPutSpy).toHaveBeenCalledWith('/api/process/1', {
      status: 'waiting_for_confirmation',
      reason: ''
    })

    // Verify the updating state was handled
    expect(wrapper.vm.updating).toBe(false)
  })

  test('updates processable when form is submitted', async () => {
    const processData = {
      id: 1,
      status: 'preparing',
      processable: {
        id: 101,
        name: 'Test Process'
      }
    }

    const wrapper = await factory({
      modelValue: 1,
      process: processData
    })

    // Mock UeForm ref
    wrapper.vm.UeForm = {
      validModel: true,
      model: {
        id: 101,
        name: 'Updated Test Process'
      }
    }

    // Mock formModal ref
    // wrapper.vm.$refs.formModal = {
    //   close: vi.fn()
    // }

    // Mock axios.put to resolve immediately
    global.axios.put.mockImplementationOnce(() => Promise.resolve({
      data: {
        message: 'Process updated successfully',
        variant: 'success'
      }
    }))

    // Call the method directly
    await wrapper.vm.updateProcessable()

    // Wait for promises to resolve
    await wrapper.vm.$nextTick()

    expect(global.axios.put).toHaveBeenCalled()
    // expect(wrapper.vm.$refs.formModal.close).toHaveBeenCalled()
  })

  test('handles canAction correctly based on roles', async () => {
    const processData = {
      id: 1,
      status: 'preparing',
      processable: {
        id: 101,
        name: 'Test Process'
      }
    }

    const wrapper = await factory({
      modelValue: 1,
      process: processData,
      actionRoles: {
        preparing: ['admin']
      }
    })

    // Test when user has the role
    mockHasRoles.mockReturnValue(true)
    expect(wrapper.vm.canAction('preparing')).toBe(true)
    expect(mockHasRoles).toHaveBeenCalledWith(['admin'])

    // Test when user doesn't have the role
    mockHasRoles.mockReturnValue(false)
    expect(wrapper.vm.canAction('preparing')).toBe(false)

    // Test when no roles are specified for the status
    expect(wrapper.vm.canAction('confirmed')).toBe(true)
    // hasRoles shouldn't be called when no roles are specified
  })
})

describe('Role-based authorization tests', () => {
  test('hasRoles returns true when user has required role', async () => {
    const store = createMockStore({
      userRoles: ['admin', 'editor']
    })

    const wrapper = await factory({
      modelValue: 1,
      process: {
        id: 1,
        status: 'preparing',
        processable: { id: 101, name: 'Test Process' }
      },
      actionRoles: {
        preparing: ['admin']
      }
    }, {}, store)

    mockHasRoles.mockImplementation((roles) => {
      if(global.__isString(roles)){
        roles = roles.split(',').map(role => role.trim())
      }
      return store.getters.userRoles.some(role => roles.includes(role))
    })

    expect(wrapper.vm.canAction('preparing')).toBe(true)
  })

  test('hasRoles returns false when user lacks required role', async () => {
    const store = createMockStore({
      userRoles: ['viewer']
    })

    const wrapper = await factory({
      modelValue: 1,
      process: {
        id: 1,
        status: 'preparing',
        processable: { id: 101, name: 'Test Process' }
      },
      actionRoles: {
        preparing: ['admin', 'editor']
      }
    }, {}, store)

    mockHasRoles.mockImplementation((roles) => {
      if(global.__isString(roles)){
        roles = roles.split(',').map(role => role.trim())
      }
      return store.getters.userRoles.some(role => roles.includes(role))
    })

    expect(wrapper.vm.canAction('preparing')).toBe(false)
  })

  test('canAction returns true when no roles specified for status', async () => {
    const store = createMockStore({
      userRoles: ['viewer']
    })

    const wrapper = await factory({
      modelValue: 1,
      process: {
        id: 1,
        status: 'preparing',
        processable: { id: 101, name: 'Test Process' }
      },
      actionRoles: {} // No roles specified for any status
    }, {}, store)

    expect(wrapper.vm.canAction('preparing')).toBe(true)
  })

  test('hasRoles works with string roles', async () => {
    const store = createMockStore({
      userRoles: ['admin', 'editor']
    })

    const wrapper = await factory({
      modelValue: 1,
      process: {
        id: 1,
        status: 'preparing',
        processable: { id: 101, name: 'Test Process' }
      },
      actionRoles: {
        preparing: 'admin,editor' // String format
      }
    }, {}, store)

    mockHasRoles.mockImplementation((roles) => {
      if(global.__isString(roles)){
        roles = roles.split(',').map(role => role.trim())
      }
      return store.getters.userRoles.some(role => roles.includes(role))
    })

    expect(wrapper.vm.canAction('preparing')).toBe(true)
  })

  test('processableEditableRoles works correctly', async () => {
    const store = createMockStore({
      userRoles: ['editor']
    })

    const wrapper = await factory({
      modelValue: 1,
      process: {
        id: 1,
        status: 'preparing',
        processable: { id: 101, name: 'Test Process' }
      },
      processableEditableRoles: ['admin', 'editor']
    }, {}, store)

    expect(wrapper.vm.hasProcessableEditing).toBe(true)
  })

  test('processableEditableRoles denies access when user lacks role', async () => {
    const store = createMockStore({
      userRoles: ['viewer']
    })

    mockHasRoles.mockImplementation((roles) => {
      if(global.__isString(roles)){
        roles = roles.split(',').map(role => role.trim())
      }
      return store.getters.userRoles.some(role => roles.includes(role))
    })

    const wrapper = await factory({
      modelValue: 1,
      process: {
        id: 1,
        status: 'preparing',
        processable: { id: 101, name: 'Test Process' }
      },
      processableEditableRoles: ['admin', 'editor']
    }, {}, store)



    expect(wrapper.vm.hasProcessableEditing).toBe(false)
  })

  test('processableEditableRoles supports status-based roles', async () => {
    const store = createMockStore({
      userRoles: ['reviewer']
    })

    mockHasRoles.mockImplementation((roles) => {
      if(global.__isString(roles)){
        roles = roles.split(',').map(role => role.trim())
      }
      return store.getters.userRoles.some(role => roles.includes(role))
    })

    const wrapper = await factory({
      modelValue: 1,
      process: {
        id: 1,
        status: 'reviewing',
        processable: { id: 101, name: 'Test Process' }
      },
      processableEditableRoles: {
        preparing: ['admin'],
        reviewing: ['reviewer'],
        completed: ['admin']
      }
    }, {}, store)

    expect(wrapper.vm.hasProcessableEditing).toBe(true)
  })
})

test('edit button is shown only when user has processableEditableRoles', async () => {
  const store = createMockStore({
    userRoles: ['editor']
  })

  mockHasRoles.mockImplementation((roles) => {
    if(global.__isString(roles)){
      roles = roles.split(',').map(role => role.trim())
    }
    return store.getters.userRoles.some(role => roles.includes(role))
  })

  const wrapper = await factory({
    modelValue: 1,
    process: {
      id: 1,
      status: 'preparing',
      processable: { id: 101, name: 'Test Process' }
    },
    schema: { name: { type: 'text', label: 'Name' } },
    processableEditableRoles: ['editor']
  }, {}, store)

  // The edit button should be available
  expect(wrapper.vm.hasProcessableEditing).toBe(true)
})

test('process actions are disabled when user lacks actionRoles', async () => {
  const store = createMockStore({
    userRoles: ['viewer']
  })

  const wrapper = await factory({
    modelValue: 1,
    process: {
      id: 1,
      status: 'waiting_for_confirmation',
      processable: { id: 101, name: 'Test Process' }
    },
    actionRoles: {
      waiting_for_confirmation: ['admin']
    },
    processEditableRoles: ['admin', 'viewer'] // User can see the section but not act
  }, {}, store)

  expect(wrapper.vm.canAction('waiting_for_confirmation')).toBe(false)
})

