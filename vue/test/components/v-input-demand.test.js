// test/components/v-input-demand.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import vuetify from '../../src/js/plugins/vuetify'

import VInputDemand from '../../src/js/components/VInputDemand.vue'

let getModel = () => {}

async function factory(props = {}, options = {}) {

  return await mount(VInputDemand, {
    global: {
      plugins: [vuetify],
    },
    ...options,
    props: {
      ...props
    }
  })
}


describe('VInputDemand tests', () => {

  test('renders the component', async () => {


  })

})

