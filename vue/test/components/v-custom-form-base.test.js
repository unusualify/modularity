/**
 * Unit tests for CustomFormBase.vue (VCustomFormBase)
 * Run these before refactoring/splitting to ensure behavior is preserved.
 */
import { describe, expect, test, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// Mock formEvents to avoid store and cascade select dependencies
vi.mock('@/utils/formEvents', () => ({
  default: {
    setSchemaInputField: vi.fn(),
    onInputEventFormData: vi.fn()
  }
}))

import CustomFormBase from '../../src/js/components/others/CustomFormBase.vue'

const vuetify = createVuetify({ components, directives })

// Minimal non-empty model/schema so rebuildArrays does not warn on mount.
const fixtureModel = { _fixture: null }
const fixtureSchema = { _fixture: { type: 'hidden', hidden: true } }

async function factory(props = {}, options = {}) {
  return mount(CustomFormBase, {
    global: {
      plugins: [vuetify],
      stubs: {
        'ue-recursive-stuff': true,
        'ue-dynamic-component-renderer': true,
        'v-custom-form-base': CustomFormBase,
        'v-input-locale': true,
        'ue-title': { template: '<span />' }
      },
      directives: {
        resize: { mounted: () => {}, unmounted: () => {} },
        intersect: { mounted: () => {}, unmounted: () => {} },
        touch: { mounted: () => {}, unmounted: () => {} },
        'click-outside': { mounted: () => {}, unmounted: () => {} },
        mask: { mounted: () => {}, unmounted: () => {} }
      }
    },
    attachTo: document.body,
    ...options,
    props: {
      modelValue: fixtureModel,
      schema: fixtureSchema,
      ...props
    }
  })
}

describe('VCustomFormBase tests', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('mapTypeToComponent', () => {
    test('maps title to InputTitle', async () => {
      const wrapper = await factory({ id: 'test', schema: { name: { type: 'title' } }, modelValue: {} })
      expect(wrapper.vm.mapTypeToComponent('title')).toBe('InputTitle')
    })

    test('maps radio to InputRadio', async () => {
      const wrapper = await factory({ id: 'test' })
      expect(wrapper.vm.mapTypeToComponent('radio')).toBe('InputRadio')
    })

    test('maps text to v-text-field', async () => {
      const wrapper = await factory({ id: 'test' })
      expect(wrapper.vm.mapTypeToComponent('text')).toBe('v-text-field')
    })

    test('maps unknown type to v-{type}', async () => {
      const wrapper = await factory({ id: 'test' })
      expect(wrapper.vm.mapTypeToComponent('custom-type')).toBe('v-custom-type')
    })
  })

  describe('bindOptions', () => {
    test('converts string to { value, label }', async () => {
      const wrapper = await factory({ id: 'test' })
      expect(wrapper.vm.bindOptions('foo')).toEqual({ value: 'foo', label: 'foo' })
    })

    test('returns object as-is', async () => {
      const wrapper = await factory({ id: 'test' })
      const opt = { value: 1, label: 'One' }
      expect(wrapper.vm.bindOptions(opt)).toBe(opt)
    })
  })

  describe('bindSchema', () => {
    test('omits type, col, order, offset, ext, event from schema', async () => {
      const wrapper = await factory({ id: 'test' })
      const obj = {
        key: 'name',
        schema: {
          type: 'text',
          label: 'Name',
          col: 6,
          order: 1
        }
      }
      const result = wrapper.vm.bindSchema(obj)
      expect(result).not.toHaveProperty('type')
      expect(result).not.toHaveProperty('col')
      expect(result).not.toHaveProperty('order')
      expect(result).toHaveProperty('label', 'Name')
    })

    test('converts falseValue 0 to string "0"', async () => {
      const wrapper = await factory({ id: 'test' })
      const obj = {
        key: 'flag',
        schema: { type: 'checkbox', falseValue: 0, label: 'Flag' }
      }
      wrapper.vm.bindSchema(obj)
      expect(obj.schema.falseValue).toBe('0')
    })
  })

  describe('isDateTimeColorTypeAndExtensionText', () => {
    test('returns true for date type with ext text', async () => {
      const wrapper = await factory({ id: 'test' })
      expect(wrapper.vm.isDateTimeColorTypeAndExtensionText({
        schema: { type: 'date', ext: 'text' }
      })).toBe(true)
    })

    test('returns false for date without ext', async () => {
      const wrapper = await factory({ id: 'test' })
      expect(wrapper.vm.isDateTimeColorTypeAndExtensionText({
        schema: { type: 'date' }
      })).toBe(false)
    })
  })

  describe('getKeyForArray', () => {
    test('uses key property when schema has key', async () => {
      const wrapper = await factory({ id: 'test' })
      const obj = { key: 'items', schema: { key: 'id' } }
      const item = { id: 42, name: 'Item' }
      expect(wrapper.vm.getKeyForArray('form-1', obj, item, 0)).toBe(42)
    })

    test('uses index when no key in schema', async () => {
      const wrapper = await factory({ id: 'test' })
      const obj = { key: 'items', schema: {} }
      const item = { name: 'Item' }
      expect(wrapper.vm.getKeyForArray('form-1', obj, item, 2)).toBe('form-1-items-2')
    })
  })

  describe('getShorthandTooltip', () => {
    test('converts string to { location: "top", text }', async () => {
      const wrapper = await factory({ id: 'test' })
      expect(wrapper.vm.getShorthandTooltip('My tooltip')).toEqual({
        location: 'top',
        text: 'My tooltip'
      })
    })

    test('returns object as-is', async () => {
      const wrapper = await factory({ id: 'test' })
      const tooltip = { location: 'bottom', text: 'Custom' }
      expect(wrapper.vm.getShorthandTooltip(tooltip)).toBe(tooltip)
    })
  })

  describe('checkInternGroupType', () => {
    test('returns v-card by default for wrap/group', async () => {
      const wrapper = await factory({ id: 'test' })
      const obj = { schema: { type: 'wrap' } }
      expect(wrapper.vm.checkInternGroupType(obj)).toBe('v-card')
    })

    test('prepends v- when typeInt does not start with v- or ue-', async () => {
      const wrapper = await factory({ id: 'test' })
      const obj = { schema: { type: 'wrap', typeInt: 'card' } }
      expect(wrapper.vm.checkInternGroupType(obj)).toBe('v-card')
    })

    test('returns typeInt as-is when it starts with v-', async () => {
      const wrapper = await factory({ id: 'test' })
      const obj = { schema: { type: 'wrap', typeInt: 'v-sheet' } }
      expect(wrapper.vm.checkInternGroupType(obj)).toBe('v-sheet')
    })
  })

  describe('slot name helpers', () => {
    test('getFormTopSlot returns slot-top-{id}', async () => {
      const wrapper = await factory({ id: 'my-form' })
      expect(wrapper.vm.getFormTopSlot()).toBe('slot-top-my-form')
    })

    test('getKeyInjectSlot returns correct format', async () => {
      const wrapper = await factory({ id: 'form-1' })
      const obj = { key: 'address.city' }
      expect(wrapper.vm.getKeyInjectSlot(obj, 'label')).toContain('slot-inject')
      expect(wrapper.vm.getKeyInjectSlot(obj, 'label')).toContain('form-1')
      expect(wrapper.vm.getKeyInjectSlot(obj, 'label')).toContain('address-city')
    })
  })

  describe('rendering with schema', () => {
    test('renders with title schema type', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: { heading: { type: 'title', title: 'Section Title' } },
        modelValue: {}
      })
      await wrapper.vm.$nextTick()
      expect(wrapper.exists()).toBe(true)
      expect(wrapper.vm.flatCombinedArraySorted.length).toBeGreaterThan(0)
    })

    test('renders with text schema type', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: { name: { type: 'text', label: 'Name' } },
        modelValue: { name: '' }
      })
      await wrapper.vm.$nextTick()
      expect(wrapper.exists()).toBe(true)
    })

    test('renders with radio schema type', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: {
          choice: {
            type: 'radio',
            label: 'Choice',
            options: ['A', 'B', 'C']
          }
        },
        modelValue: { choice: null }
      })
      await wrapper.vm.$nextTick()
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('setValue', () => {
    test('returns value for simple schema type', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: { name: { type: 'text' } },
        modelValue: { name: 'John' }
      })
      const obj = { key: 'name', value: 'John', schema: { type: 'text' } }
      expect(wrapper.vm.setValue(obj)).toBe('John')
    })
  })

  describe('onInput', () => {
    test('emits update:modelValue when input changes', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: { name: { type: 'text', label: 'Name' } },
        modelValue: { name: '' }
      })
      await wrapper.vm.$nextTick()
      const obj = wrapper.vm.flatCombinedArraySorted[0]
      if (obj) {
        wrapper.vm.onInput('Jane', obj)
        await wrapper.vm.$nextTick()
        expect(wrapper.emitted('update:modelValue')).toBeTruthy()
      }
    })
  })

  describe('flatCombinedArray', () => {
    test('flattens schema into array of { key, value, schema } objects', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: {
          name: { type: 'text', label: 'Name' },
          email: { type: 'email', label: 'Email' }
        },
        modelValue: { name: '', email: '' }
      })
      await wrapper.vm.$nextTick()
      const flat = wrapper.vm.flatCombinedArraySorted
      expect(flat.length).toBe(2)
      expect(flat.map(o => o.key)).toContain('name')
      expect(flat.map(o => o.key)).toContain('email')
    })
  })

  describe('tryAutogenerateModelStructure', () => {
    test('handles schema with type list', async () => {
      const model = {}
      const schema = { items: { type: 'list', label: 'Items' } }
      const wrapper = await factory({ id: 'test', schema, modelValue: model })
      wrapper.vm.tryAutogenerateModelStructure(model, schema)
      expect(model.items).toEqual({})
    })

    test('handles schema with type array and nested schema', async () => {
      const model = {}
      const schema = { tasks: { type: 'array', schema: { title: { type: 'text' } } } }
      const wrapper = await factory({ id: 'test', schema, modelValue: model })
      wrapper.vm.tryAutogenerateModelStructure(model, schema)
      expect(model.tasks).toEqual({})
    })

    test('handles nested schema without type (plain object)', async () => {
      const model = {}
      const schema = { address: { city: { type: 'text' }, zip: { type: 'text' } } }
      const wrapper = await factory({ id: 'test', schema, modelValue: model })
      wrapper.vm.tryAutogenerateModelStructure(model, schema)
      expect(model.address).toEqual({})
    })
  })

  describe('rebuildArrays', () => {
    test('throws when model is null', async () => {
      const wrapper = await factory({ id: 'test', schema: { name: { type: 'text' } }, modelValue: {} })
      expect(() => wrapper.vm.rebuildArrays(null, { name: { type: 'text' } })).toThrow(/model.*null.*undefined/)
    })
  })

  describe('updateInput', () => {
    test('emits update:schema when event is object with key and value', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: { profile: { name: { type: 'text' }, role: { type: 'text' } } },
        modelValue: { profile: { name: '', role: '' } }
      })
      await wrapper.vm.$nextTick()
      const obj = { key: 'profile' }
      wrapper.vm.updateInput({ key: 'role', value: 'admin' }, obj)
      expect(wrapper.emitted('update:schema')).toBeTruthy()
      const emittedSchema = wrapper.emitted('update:schema')[0][0]
      expect(emittedSchema.profile.role).toBe('admin')
    })

    test('emits update:schema when event is array of key-value pairs', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: { profile: { name: { type: 'text' }, role: { type: 'text' } } },
        modelValue: { profile: { name: '', role: '' } }
      })
      await wrapper.vm.$nextTick()
      const obj = { key: 'profile' }
      wrapper.vm.updateInput([{ key: 'name', value: 'Jane' }, { key: 'role', value: 'editor' }], obj)
      expect(wrapper.emitted('update:schema')).toBeTruthy()
      const emittedSchema = wrapper.emitted('update:schema')[0][0]
      expect(emittedSchema.profile.name).toBe('Jane')
      expect(emittedSchema.profile.role).toBe('editor')
    })
  })

  describe('setCascadeSelect', () => {
    test('handles select with cascade - does not throw', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: {
          country: { type: 'select', name: 'country', cascade: 'city', items: [{ id: 1, value: 'TR', schema: [] }], itemValue: 'id' },
          city: { type: 'select', name: 'city', items: [], cascadeKey: 'items' }
        },
        modelValue: { country: 1, city: null }
      })
      await wrapper.vm.$nextTick()
      const obj = wrapper.vm.flatCombinedArraySorted.find(o => o.key === 'country')
      if (obj) {
        expect(() => wrapper.vm.setCascadeSelect(obj)).not.toThrow()
      }
    })

    test('handles select with autofill - does not throw', async () => {
      const wrapper = await factory({
        id: 'test',
        schema: {
          user: {
            type: 'select',
            name: 'user',
            autofill: ['email'],
            items: [{ id: 1, value: 'John', email: 'john@test.com' }],
            itemValue: 'id'
          },
          email: { type: 'text', name: 'email', autofillable: true }
        },
        modelValue: { user: null, email: '' }
      })
      await wrapper.vm.$nextTick()
      const obj = wrapper.vm.flatCombinedArraySorted.find(o => o.key === 'user')
      if (obj) {
        expect(() => wrapper.vm.setCascadeSelect(obj)).not.toThrow()
      }
    })
  })
})
