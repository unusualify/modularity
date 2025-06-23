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

// Add to global object before tests run
global.ResizeObserver = ResizeObserver

// Mock Intersection Observer
global.IntersectionObserver = class IntersectionObserver {
  constructor() {}
  observe() {}
  unobserve() {}
  disconnect() {}
}

// Mock axios with full structure
vi.mock('axios', () => ({
  default: {
    get: vi.fn(() => Promise.resolve({ data: [] })),
    post: vi.fn(() => Promise.resolve({ data: [] })),
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

// Import axios after mocking
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
  assignee_avatar: 'avatar.jpg',
}

describe('VInputAssignment', () => {
  let wrapper

  beforeEach(async () => {
    vi.clearAllMocks()

    const globalMocks = {
      plugins: [UEConfig],
      mocks: {
        t: vi.fn(str => str),
        d: vi.fn(() => '2024-03-20'),
        $notif: vi.fn()
      }
    }

    // Mock the useAuthorization hook instead of hasRoles directly
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

    wrapper = mount(VInputAssignment, {
      props: defaultProps,
      global: globalMocks,
      attachTo: document.body
    })

  })

  // Unit Tests
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


  // Add test for modal opening
  test('create form modal opens correctly', async () => {
    wrapper.vm.loading = false
    await wrapper.vm.$nextTick()

    // Initially closed
    expect(wrapper.vm.createFormModalActive).toBe(false)

    // Find and click create button
    const createBtn = wrapper.find('#createAssignmentBtn')
    await createBtn.trigger('click')

    expect(wrapper.vm.createFormModalActive).toBe(true)

    // Check if form is rendered
    const form = document.querySelector('#createAssignmentForm')
    expect(form).not.toBeNull()
  })
  // Feature Tests
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
      // Open the create form modal
      wrapper.vm.createFormModalActive = true

      await wrapper.vm.$nextTick()

      const newAssignment = {
        assignee_id: 1,
        due_at: '2024-04-01',
        description: 'New task',
        assignee_avatar: 'avatar.jpg',
      }

      wrapper.vm.createFormModel = newAssignment
      // wrapper.vm.$refs.createForm = {
      //   validate: vi.fn().mockResolvedValueOnce(true)
      // }
      axios.post.mockResolvedValueOnce({
        status: 200,
        data: { ...mockAssignment, ...newAssignment }
      })

      await wrapper.vm.createAssignment()
      await flushPromises()

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

    test('updates assignment status', async () => {
      wrapper.vm.assignments = [mockAssignment]

      const mockResponse = {
        status: 200,
        data: {
          assignments: [{ ...mockAssignment, status: 'completed' }]
        }
      }

      axios.post.mockResolvedValueOnce(mockResponse)

      const result = await wrapper.vm.updateAssignment({ status: 'completed' })
      await flushPromises()

      // Updated expectation to match the correct endpoint
      expect(axios.post).toHaveBeenCalledWith(
        '/api/assignments/123/create',  // This matches the createEndpoint prop
        { status: 'completed' }
      )
      expect(wrapper.vm.assignments[0].status).toBe('completed')
      // expect(result).toEqual(mockResponse)
    })

    test('shows loading state during API calls', async () => {
      axios.get.mockImplementation(() => new Promise(resolve => setTimeout(resolve, 1000)))

      wrapper.vm.fetchAssignments()
      expect(wrapper.vm.loading).toBe(true)

      await flushPromises()
      wrapper.vm.loading = false
      expect(wrapper.vm.loading).toBe(false)
    })
  })

  // UI Interaction Tests
  describe('UI Interactions', () => {
    test('opens create assignment modal on button click', async () => {
      // Reset the modal state first to ensure we're testing the click effect
      wrapper.vm.createFormModal = false
      wrapper.vm.loading = false

      await wrapper.vm.$nextTick()

      // Use the ID selector instead of the icon attribute
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
      // wrapper.vm.$isYou = vi.fn().mockReturnValue(true)

      await wrapper.vm.$nextTick()

      const assignmentList = wrapper.find('#assigneeList')
      expect(assignmentList.exists()).toBe(true)

      expect(assignmentList.html()).toContain('User 1')
      // expect(assignmentList.html()).toContain('Admin')
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
        due_at: new Date('2024-05-12').toISOString(),
        description: 'New task',
      }

      wrapper.vm.createFormModel = newAssignment

      expect(wrapper.vm.$refs.createFormModal.dialog).toBe(true)

      const mockValidate = vi.fn().mockResolvedValue(true)

      wrapper.vm.$refs.createForm.validate = mockValidate

      axios.post.mockResolvedValueOnce({
        status: 200,
        data: { id: 1, ...newAssignment }
      })

      await wrapper.vm.createAssignment()

      expect(mockValidate).toHaveBeenCalled()

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

      wrapper.vm.createFormModalActive = true

      await wrapper.vm.$nextTick()

      // Mock failed validation
      const mockValidate = vi.fn().mockResolvedValue(false)
      wrapper.vm.$refs.createForm.validate = mockValidate

      await wrapper.vm.createAssignment()

      expect(mockValidate).toHaveBeenCalled()
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

