// test/components/v-input-assignment.test.js
import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import UEConfig from '../../src/js/plugins/UEConfig'
import VInputAssignment from '../../src/js/components/inputs/Assignment.vue'

// Mock ResizeObserver
class ResizeObserver {
  observe() {}
  unobserve() {}
  disconnect() {}
}

global.ResizeObserver = ResizeObserver

global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  observe() {}
  unobserve() {}
  disconnect() {}
}

global.__log = vi.fn()

vi.mock('axios', () => ({
  default: {
    get: vi.fn(() => Promise.resolve({ status: 200, data: [] })),
    post: vi.fn(() => Promise.resolve({ status: 200, data: [] })),
    defaults: {
      headers: {
        common: {}
      }
    },
    interceptors: {
      request: {
        use: vi.fn()
      },
      response: {
        use: vi.fn()
      }
    }
  }
}))

vi.mock('@/hooks', async (importOriginal) => {
  const actual = await importOriginal()
  return {
    ...actual,
    useAuthorization: () => ({
      hasRoles: vi.fn().mockReturnValue(true),
      isYou: vi.fn().mockReturnValue(false)
    })
  }
})

import axios from 'axios'

const defaultProps = {
  modelValue: '123',
  items: [
    { id: 1, name: 'User 1' },
    { id: 2, name: 'User 2' }
  ],
  fetchEndpoint: '/api/assignments/:id',
  saveEndpoint: '/api/assignments/:id/create',
  assignableType: 'Task',
  assigneeType: 'User',
  authorizedRoles: ['admin']
}

const assignmentModalValidateForm = vi.fn().mockResolvedValue({ valid: true })

const AssignmentModalStub = {
  name: 'AssignmentModal',
  props: {
    modelValue: { type: Boolean, default: false },
    form: { type: Object, default: () => ({}) },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    variant: { type: String, default: 'outlined' },
    users: { type: Array, default: () => [] },
    minDueDays: { type: Number, default: 0 },
    filepond: { type: Object, default: null },
  },
  emits: ['update:modelValue', 'update:form', 'submit'],
  template: '<form id="createAssignmentForm" v-show="modelValue" @submit.prevent="$emit(\'submit\')"></form>',
  setup(_, { expose }) {
    expose({ validateForm: assignmentModalValidateForm })
  },
}

function mountAssignment(props = {}, options = {}) {
  return mount(VInputAssignment, {
    props: { ...defaultProps, ...props },
    attachTo: document.body,
    global: {
      plugins: [UEConfig],
      mocks: {
        t: vi.fn(str => str),
        d: vi.fn(() => '2024-03-20'),
        $notif: vi.fn()
      },
      stubs: {
        AssignmentModal: AssignmentModalStub,
        'v-input-filepond': true,
        'ue-filepond-preview': true,
        'v-input-date': true,
        'ue-dynamic-component-renderer': true,
        ...(options.global?.stubs ?? {}),
      },
      ...(options.global ?? {}),
    },
    ...options,
  })
}

const mockAssignment = {
  id: 1,
  assignee_id: 1,
  assignee_name: 'User 1',
  assignee_avatar: 'avatar.jpg',
  assigner_id: 2,
  assigner_name: 'Admin',
  description: 'Test assignment',
  due_at: '2024-04-01T00:00:00Z',
  created_at: '2024-03-20T00:00:00Z',
  status: 'pending',
}

describe('VInputAssignment', () => {
  let wrapper

  beforeEach(async () => {
    vi.clearAllMocks()
    assignmentModalValidateForm.mockResolvedValue({ valid: true })
    wrapper = mountAssignment()
  })

  describe('Unit Tests', () => {
    test('renders correctly with default props', () => {
      expect(wrapper.exists()).toBe(true)
      expect(wrapper.find('.v-input-assignment').exists()).toBe(true)
    })

    test('computes isAuthorized correctly', () => {
      expect(wrapper.vm.isAuthorized).toBe(true)
    })

    test('computes lastAssignment correctly', () => {
      wrapper.vm.assignments = [mockAssignment]
      expect(wrapper.vm.lastAssignment).toEqual(mockAssignment)

      wrapper.vm.assignments = []
      expect(wrapper.vm.lastAssignment).toBeNull()
    })
  })

  test('create form modal opens correctly', async () => {
    wrapper.vm.loading = false
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.createFormModalActive).toBe(false)

    const createBtn = wrapper.find('#createAssignmentBtn')
    await createBtn.trigger('click')

    expect(wrapper.vm.createFormModalActive).toBe(true)

    const form = document.querySelector('#createAssignmentForm')
    expect(form).not.toBeNull()
  })

  describe('Feature Tests', () => {
    test('fetches assignments on creation', async () => {
      axios.get.mockResolvedValueOnce({
        status: 200,
        data: [mockAssignment]
      })

      await wrapper.vm.fetchAssignments()
      await flushPromises()

      expect(axios.get).toHaveBeenCalledWith('/api/assignments/123')
      expect(wrapper.vm.assignments).toEqual([mockAssignment])
    })

    test('creates new assignment', async () => {
      wrapper.vm.loading = false
      await wrapper.vm.$nextTick()

      const newAssignment = {
        assignee_id: 1,
        due_at: new Date(new Date().getTime() + 24 * 60 * 60 * 1000).toISOString(),
        description: 'New task Description',
        assignee_avatar: 'avatar.jpg',
      }

      wrapper.vm.createFormModel = newAssignment

      axios.post.mockResolvedValueOnce({
        status: 200,
        data: { ...mockAssignment, ...newAssignment }
      })

      await wrapper.vm.createAssignment()
      await flushPromises()

      expect(assignmentModalValidateForm).toHaveBeenCalled()
      expect(axios.post).toHaveBeenCalledWith(
        '/api/assignments/123/create',
        expect.objectContaining({
          ...newAssignment,
          assignable_id: '123',
          assignable_type: 'Task',
          assignee_type: 'User'
        })
      )
    })

    test('updateAssignment calls axios.post and updates assignments on success', async () => {
      wrapper.vm.assignments = [mockAssignment]

      const updatedAssignment = { ...mockAssignment, status: 'completed' }
      const mockResponse = {
        status: 200,
        data: {
          assignments: [updatedAssignment]
        }
      }

      axios.post.mockResolvedValueOnce(mockResponse)

      await wrapper.vm.updateAssignment({ status: 'completed' })
      await flushPromises()

      expect(axios.post).toHaveBeenCalledWith(
        '/api/assignments/123/create',
        { status: 'completed' }
      )
      const assignments = wrapper.vm.assignments
      expect(Array.isArray(assignments)).toBe(true)
      expect(assignments[0].status).toBe('completed')
    })

    test('shows loading state during API calls', async () => {
      let resolveGet
      const getPromise = new Promise(resolve => { resolveGet = resolve })
      axios.get.mockImplementationOnce(() => getPromise)

      wrapper.vm.fetchAssignments()
      expect(wrapper.vm.loading).toBe(true)

      resolveGet({ status: 200, data: [] })
      await flushPromises()
      expect(wrapper.vm.loading).toBe(false)
    })
  })

  describe('UI Interactions', () => {
    test('opens create assignment modal on button click', async () => {
      wrapper.vm.createFormModalActive = false
      wrapper.vm.loading = false

      await wrapper.vm.$nextTick()

      const assignBtn = wrapper.find('#createAssignmentBtn')

      expect(assignBtn.exists()).toBe(true)

      await assignBtn.trigger('click')

      expect(wrapper.vm.createFormModalActive).toBe(true)
    })

    test('shows assignment history on history button click', async () => {
      wrapper.vm.loading = false
      wrapper.vm.assignments = [mockAssignment]

      expect(wrapper.vm.assignments.length).toBe(1)

      await wrapper.vm.$nextTick()

      const historyBtn = wrapper.find('#showHistoryBtn')

      await historyBtn.trigger('click')

      expect(wrapper.vm.listAssignmentsModalActive).toBe(true)
    })

    test('displays assignment info in the list', async () => {
      wrapper.vm.loading = false
      wrapper.vm.assignments = [mockAssignment]

      await wrapper.vm.$nextTick()

      const assignmentList = wrapper.find('#assigneeList')
      expect(assignmentList.exists()).toBe(true)

      expect(assignmentList.html()).toContain('User 1')
    })
  })

  describe('Form validation', () => {
    test('createAssignment validates form before submission', async () => {
      wrapper.vm.loading = false

      await wrapper.vm.$nextTick()

      wrapper.vm.createFormModalActive = true

      await wrapper.vm.$nextTick()

      const newAssignment = {
        assignee_id: 1,
        due_at: new Date(new Date().getTime() + 24 * 60 * 60 * 1000).toISOString(),
        description: 'New task',
      }

      wrapper.vm.createFormModel = newAssignment

      axios.post.mockResolvedValueOnce({
        status: 200,
        data: { id: 1, ...newAssignment }
      })

      await wrapper.vm.createAssignment()
      await flushPromises()

      expect(assignmentModalValidateForm).toHaveBeenCalled()

      expect(axios.post).toHaveBeenCalledWith(
        '/api/assignments/123/create',
        expect.objectContaining({
          ...newAssignment,
          assignable_id: '123',
          assignable_type: 'Task',
          assignee_type: 'User'
        })
      )
    })

    test('createAssignment does not submit when validation fails', async () => {
      wrapper.vm.loading = false
      await wrapper.vm.$nextTick()

      assignmentModalValidateForm.mockResolvedValueOnce({ valid: false })

      await wrapper.vm.createAssignment()

      expect(assignmentModalValidateForm).toHaveBeenCalled()
      expect(axios.post).not.toHaveBeenCalled()
    })

    test('form is properly mounted with ref', async () => {
      wrapper.vm.loading = false
      await wrapper.vm.$nextTick()

      wrapper.vm.createFormModalActive = true
      await wrapper.vm.$nextTick()

      expect(document.querySelector('#createAssignmentForm')).not.toBeNull()
    })
  })
})
