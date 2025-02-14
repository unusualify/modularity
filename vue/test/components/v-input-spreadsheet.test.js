// test/components/v-input-spreadsheet.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import vuetify from '../../src/js/plugins/vuetify'

import VInputSpreadsheet from '../../src/js/components/VInputSpreadsheet.vue'

let getModel = () => {}

async function factory(props = {}, options = {}) {

  return await mount(VInputSpreadsheet, {
    global: {
      plugins: [vuetify],
    },
    ...options,
    props: {
      ...props
    }
  })
}


describe('VInputSpreadsheet tests', () => {

  test('renders the component', async () => {


  })

})

