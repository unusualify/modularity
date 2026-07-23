import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'

import Checklist from '../../src/js/components/inputs/Checklist.vue'

import vuetify from '../../src/js/plugins/vuetify'
import UEConfig from '../../src/js/plugins/UEConfig'


function factory(props, options = {}) {

  return mount(Checklist, {
    global: {
      plugins: [UEConfig]
    },
    ...options,
    props
  })
}

describe('checklist tests', () => {


  test('renders checklist label and items', () => {
    const wrapper = factory({
      label: 'Checklist label',
      items: [
        {
          id: 1,
          name: 'Check 1'
        },
        {
          id: 2,
          name: 'Check 2'
        }
      ]
    })

    expect( wrapper.get('[data-test="title"]').text() ).toBe('Checklist label')

    expect( wrapper.findAll('[data-test="checkbox"]') ).toHaveLength(2)
  })

  test('renders checklist input click', async () => {
    const wrapper = factory({
      label: 'Checklist label',
      modelValue: [],
      items: [
        {
          id: 'role',
          name: 'Check 1'
        },
        {
          id: 'permission',
          name: 'Check 2'
        }
      ]
    })

    const checkboxComponent = await wrapper.findAll('[data-test="checkbox"]')[0]
    const input = await checkboxComponent.find('input[type="checkbox"]')
    await input.setValue()

    const updateModelEvent = wrapper.emitted('update:modelValue')

    expect(updateModelEvent[0][0]).toEqual(['role'])

    expect(input.element.checked).toBeTruthy()
  })

  test('registers only the outer v-input with VForm when items are present', async () => {
    const { defineComponent, h, nextTick, ref } = await import('vue')
    const { VForm } = await import('vuetify/components')

    const formRef = ref(null)

    const Host = defineComponent({
      setup () {
        return () => h(VForm, {
          ref: formRef
        }, {
          default: () => h(Checklist, {
            label: 'Permissions',
            modelValue: [],
            items: Array.from({ length: 30 }, (_, i) => ({
              id: i + 1,
              name: `perm_${i + 1}`
            }))
          })
        })
      }
    })

    const wrapper = mount(Host, {
      global: {
        plugins: [UEConfig]
      }
    })

    await nextTick()

    // Nested checkboxes must not register — otherwise large item lists break form validity.
    expect(formRef.value.items.length).toBe(1)

    wrapper.unmount()
  })

})

