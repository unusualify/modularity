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

async function factory(props = {}, options = {}) {
  return await mount(VInputProcess, {
    global: {
      mocks: {
        $store: mockStore,
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
        $hasRoles: () => true,
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
        'v-divider': true
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

    const wrapper = await factory({
      modelValue: 1,
      process: processData
    })

    // Mock methods
    wrapper.vm.canAction = () => true
    const updateProcessSpy = vi.spyOn(wrapper.vm, 'updateProcess')

    // Call the method directly since we're stubbing the button
    wrapper.vm.updateProcess('waiting_for_confirmation')

    expect(updateProcessSpy).toHaveBeenCalledWith('waiting_for_confirmation')
    expect(global.axios.put).toHaveBeenCalled()
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

    // console.log('wrapper.vm.canAction',
    //   wrapper.vm.canAction('preparing'),
    //   wrapper.vm.actionRoles,
    //   wrapper.vm.$store.getters.userRoles
    // )
    // Test when user has the role
    wrapper.vm.$hasRoles = () => true
    expect(wrapper.vm.canAction('preparing')).toBe(true)

    // Test when user doesn't have the role
    wrapper.vm.$hasRoles = () => false
    expect(wrapper.vm.canAction('preparing')).toBe(false)

    // Test when no roles are specified for the status
    expect(wrapper.vm.canAction('confirmed')).toBe(true)
  })
})

