// test/components/v-input-chat.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import vuetify from '../../src/js/plugins/vuetify'

import VInputChat from '../../src/js/components/inputs/Chat.vue'

let getModel = () => {}

async function factory(props = {}, options = {}) {

  return await mount(VInputChat, {
    global: {
      plugins: [vuetify],
    },
    ...options,
    props: {
      ...props
    }
  })
}


describe('VInputChat tests', () => {

  test('renders the component', async () => {


  })

})

