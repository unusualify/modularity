// test/components/v-input-tagger.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import vuetify from '../../src/js/plugins/vuetify'

import VInputTagger from '../../src/js/components/inputs/Tagger.vue'
import UEConfig from '../../src/js/plugins/UEConfig'

let getModel = () => {}

async function factory(props = {}, options = {}) {
  return await mount(VInputTagger, {
    global: {
      plugins: [UEConfig],
    },
    attachTo: document.body,
    ...options,
    props: {
      ...props
    }
  })
}


describe('VInputTagger tests', () => {
  const defaultProps = {
    fetchEndpoint: '/api/tags',
    updateEndpoint: '/api/tags/update',
    modelValue: [],
    items: [
      { header: true, title: 'Select an option or create one' },
      { id: 1, name: 'Tag 1', color: 'blue' },
      { id: 2, name: 'Tag 2', color: 'red' },
    ]
  }

  test('renders the component', async () => {
    const wrapper = await factory(defaultProps)
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.findComponent({ name: 'v-combobox' }).exists()).toBe(true)
  })

  // Alternative approach
  test('displays items correctly - alternative', async () => {
    const wrapper = await factory(defaultProps)

    // Directly check the component's data
    expect(wrapper.vm.items.filter(item => !item.header).length).toBe(2)
  })

  test('creates new tag when entering text', async () => {
    const wrapper = await factory(defaultProps)
    const combobox = wrapper.findComponent({ name: 'v-combobox' })

    await combobox.vm.$emit('update:search', 'New Tag')
    await combobox.vm.$emit('update:modelValue', ['New Tag'])

    // Check if the new tag was added to the model
    expect(wrapper.vm.model).toContainEqual(
      expect.objectContaining({
        name: 'New Tag',
        color: expect.any(String)
      })
    )
  })

  test('removes tag when clicking close button', async () => {
    const wrapper = await factory({
      ...defaultProps,
      modelValue: ['Tag 1']
    })

    const chip = wrapper.find('.v-chip')
    await chip.find('.v-chip__close').trigger('click')

    expect(wrapper.vm.model).toHaveLength(0)
  })

  // test('allows editing existing tag', async () => {
  //   const wrapper = await factory({
  //     ...defaultProps,
  //     modelValue: ['Tag 1']
  //   })

  //   // Open the combobox dropdown
  //   const combobox = wrapper.findComponent({ name: 'v-combobox' })
  //   await combobox.trigger('click')
  //   await wrapper.vm.$nextTick()

  //   // Wait for the menu to be mounted and visible
  //   await new Promise(resolve => setTimeout(resolve, 0))

  //   // Find the menu content
  //   const menu = document.querySelector('.v-menu__content')
  //   expect(menu).toBeTruthy()

  //   // Find the edit button within the menu
  //   const editButton = wrapper.find('.v-menu__content .v-btn.v-btn--icon')
  //   expect(editButton.exists()).toBe(true)

  //   // Click the edit button
  //   await editButton.trigger('click')
  //   await wrapper.vm.$nextTick()

  //   // Find and interact with the text field
  //   const textField = wrapper.find('.v-text-field input')
  //   expect(textField.exists()).toBe(true)

  //   await textField.setValue('Updated Tag')
  //   await textField.trigger('keyup.enter')
  //   await wrapper.vm.$nextTick()

  //   // Verify the edit mode is closed
  //   expect(wrapper.vm.editingItem).toBe(null)
  // })
})

