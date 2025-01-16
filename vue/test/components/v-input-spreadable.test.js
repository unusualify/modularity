// test/components/v-input-spreadable.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import vuetify from '../../src/js/plugins/vuetify'

import VInputSpreadable from '../../src/js/components/VInputSpreadable.vue'

let getModel = () => {}

async function factory(props = {}, options = {}) {

  return await mount(VInputSpreadable, {
    global: {
      plugins: [vuetify],
    },
    ...options,
    props: {
      ...props
    }
  })
}


describe('VInputSpreadable tests', () => {

  test('renders the component', async () => {


  })

})

