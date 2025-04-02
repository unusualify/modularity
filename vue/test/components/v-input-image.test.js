// test/components/input-image.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import vuetify from '../../src/js/plugins/vuetify'

import Image from '../../src/js/components/inputs/Image.vue'
import ModalMedia from '../../src/js/components/modals/ModalMedia.vue'

import i18n from '../../src/js/config/i18n'
import store from '../../src/js/store'
import fitGrid from '../../src/js/directives/fit-grid'
import { wrap } from 'lodash'

global.ResizeObserver = require('resize-observer-polyfill')

// vi.mock('../src/js/components/modals/ModalMedia.vue', () => ({
//   render: () => h('div', {

//   })
// }))

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

  return await mount(Image, {
    global: {
      plugins: [vuetify, store, i18n, fitGrid],
      // plugins: [UEConfig],
    },
    ...options,
    props: {
      name: 'image',
      ...props
    }
  })
}

describe('image input tests', () => {

  test('renders the add button', async () => {
    const wrapper = await factory()

    expect(wrapper.findAll('[data-test="addButton"]')).toHaveLength(1);

  })

  test('renders the add button with max 2', async () => {
    const wrapper = await factory( {
      name: 'image',
      modelValue: getModel(),
      max: 2
    })

    expect(wrapper.findAll('[data-test="addButton"]')).toHaveLength(1)
  })

  test('does not render any add buttons', async () => {
    const openMediaLibrarySpy = vi.spyOn(Image.methods, 'openMediaLibrary')

    const wrapper = await factory({
      modelValue: getModel(),
    })

    expect(wrapper.findAll('[data-test="addButton"]')).toHaveLength(0)
  })

  test('clicks a add button', async () => {
    // const openMediaLibrarySpy = vi.spyOn(Image.methods, 'openMediaLibrary')

    const wrapper = await factory()

    wrapper.vm.openMediaLibrary = await vi.fn()

    const openMediaLibrarySpy = vi.spyOn(wrapper.vm, 'openMediaLibrary')

    const addButton = await wrapper.findAll('[data-test="addButton"]')[0]

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

  test('clicks a download button', async () => {
    const wrapper = await factory({
      modelValue: getModel(),
      max: 2
    })

    const downloadButtons = await wrapper.findAll('[data-test="downloadButton"]')

    expect(downloadButtons).toHaveLength(1)

    // const downloadButton = downloadButtons[0]
  })

  test('add a new image', async () => {
    const wrapper = await factory({
      modelValue: getModel(),
      max: 2
    })

    await wrapper.vm.input.push(getModel())

    const deleteButtons = await wrapper.findAll('[data-test="deleteButton"]')

    expect(deleteButtons).toHaveLength(2)
  })

})

