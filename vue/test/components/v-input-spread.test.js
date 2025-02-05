// test/components/v-input-Spread.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import vuetify from '../../src/js/plugins/vuetify'

import VInputSpread from '../../src/js/components/inputs/Spread.vue'

let getModel = () => {}

async function factory(props = {}, options = {}) {

  return await mount(VInputSpread, {
    global: {
      plugins: [vuetify],
    },
    ...options,
    props: {
      ...props
    }
  })
}


describe('VInputSpread tests', () => {

  test('renders the component', async () => {


  })

})

