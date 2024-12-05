import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'

import Checklist from '../../src/js/components/inputs/Checklist.vue'

import vuetify from '../../src/js/plugins/vuetify'
import UEConfig from '../../src/js/plugins/UEConfig'

function factory(props, options = {}) {

  return mount(Checklist, {
    global: {
      plugins: [UEConfig],
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

    // expect( wrapper.get('[data-test="title"]').text() ).toBe('Checklist label')

    const checkboxComponent = await wrapper.findAll('[data-test="checkbox"]')[0]

    // await checkboxComponent.setChecked(true)
    // await checkboxComponent.find('input').setChecked()

    const input = await checkboxComponent.find('input[type="checkbox"]')
    // await input.trigger('click')

    // await input.setChecked(true)
    // input.element.checked = true
    // await input.trigger('click')
    await input.setValue()
    // await input.trigger('change')

    // await checkboxComponent.trigger('click')
    // await checkboxComponent.trigger('change')

    // console.log(

    //   wrapper.vm.input,
    //   checkboxComponent.classes(),
    //   input.element.checked
    // )

    const updateModelEvent = wrapper.emitted('update:modelValue')

    // expect(updateModelEvent).toHaveProperty('update:modelValue')

    expect(updateModelEvent[0][0]).toEqual(['role'])

    expect(input.element.checked).toBeTruthy()
    // console.log(checkbox.element.value)

    // // await checkbox.trigger('click')
    // // await checkbox.setValue('click')

    // console.log(wrapper.vm.modelValue)
    // // expect(wrapper.vm.input).toBe(1)
    // expect( wrapper.findAll('[data-test="checkbox"]')[0] ).click(2)
  })

})

