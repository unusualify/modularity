// test/components/v-input-image.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import Image from '../../src/js/components/inputs/Image.vue'
import createModularousVuetify from '../../src/js/plugins/vuetify'
import i18n from '../../src/js/config/i18n'
import store from '../../src/js/store'
import fitGrid from '../../src/js/directives/fit-grid'

global.ResizeObserver = class ResizeObserver {
  observe() {}
  unobserve() {}
  disconnect() {}
}

const vuetify = createModularousVuetify()

let getModel = () => [
  {
    "id" : 1,
    "name" : "profil.png",
    "thumbnail" : "http://admin.jakomeet.test/storage/uploads/cc3d3003-235b-4924-a467-aa7910e6d1ad/profil-1.png",
    "original" : "http://admin.jakomeet.test/storage/uploads/cc3d3003-235b-4924-a467-aa7910e6d1ad/profil-1.png",
    "medium" : "http://admin.jakomeet.test/storage/uploads/cc3d3003-235b-4924-a467-aa7910e6d1ad/profil-1.png",
    "width" : 1200,
    "height" : 1600,
    "tags" : [],
    "deleteUrl" : null,
    "updateUrl" : "http://admin.jakomeet.test/api/media-library/medias/single-update",
    "updateBulkUrl" : "http://admin.jakomeet.test/api/media-library/medias/bulk-update",
    "deleteBulkUrl" : "http://admin.jakomeet.test/api/media-library/medias/bulk-delete",
    "metadatas" : {
      "default": {
        "caption": null,
        "altText": "Profil 1",
        "video": null,
      },
      "custom": {
        "caption": null,
        "altText": null,
        "video": null,
      }
    }
  }
]

async function factory(props = {}, options = {}) {
  return mount(Image, {
    global: {
      plugins: [vuetify, store, i18n, fitGrid]
    },
    ...options,
    props: {
      name: 'image',
      ...props
    }
  })
}

describe('image input tests', () => {

  test('renders the add button when empty', async () => {
    const wrapper = await factory({ modelValue: [], max: 1 })

    expect(wrapper.findAll('[data-test="addButton"]')).toHaveLength(1)
  })

  test('renders the add button with max 2 when one image exists', async () => {
    const wrapper = await factory({
      modelValue: getModel(),
      max: 2
    })

    expect(wrapper.findAll('[data-test="addButton"]')).toHaveLength(1)
  })

  test('does not render any add buttons when max reached', async () => {
    const wrapper = await factory({
      modelValue: getModel(),
      max: 1
    })

    expect(wrapper.findAll('[data-test="addButton"]')).toHaveLength(0)
  })

  test('clicks a add button', async () => {
    const wrapper = await factory({ modelValue: [], max: 1 })

    const openMediaLibrarySpy = vi.spyOn(wrapper.vm, 'openMediaLibrary')

    const addButton = wrapper.findAll('[data-test="addButton"]')[0]
    expect(addButton.exists()).toBe(true)

    await addButton.trigger('click')

    expect(openMediaLibrarySpy).toHaveBeenCalled()
  })

  test('clicks a delete button', async () => {
    const wrapper = await factory({
      modelValue: getModel(),
      max: 2
    })

    const deleteButtons = wrapper.findAll('[data-test="deleteButton"]')

    expect(deleteButtons).toHaveLength(1)

    const deleteButton = deleteButtons[0]

    await deleteButton.trigger('click')

    expect(wrapper.findAll('[data-test="deleteButton"]')).toHaveLength(0)
  })

  test('renders download button when image is present', async () => {
    const wrapper = await factory({
      modelValue: getModel(),
      max: 2
    })

    const downloadButtons = wrapper.findAll('[data-test="downloadButton"]')

    expect(downloadButtons).toHaveLength(1)
  })

  test('displays multiple images when model has multiple items', async () => {
    const modelWithTwo = [...getModel(), { ...getModel()[0], id: 2, name: 'profil2.png' }]
    const wrapper = await factory({
      modelValue: modelWithTwo,
      max: 3
    })

    const deleteButtons = wrapper.findAll('[data-test="deleteButton"]')

    expect(deleteButtons).toHaveLength(2)
  })

})

