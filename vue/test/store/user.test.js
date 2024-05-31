// test/store/user.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import { createApp } from '~/vue/dist/vue.esm-bundler.js'
import { createStore } from 'vuex'

import user from '../../src/js/store/modules/user'

import authorizationObject from './authorization'

user.state._testData = 'test'

user.state.authorization = authorizationObject

const App = {
  template: `
    <div>
      <button @click="handleClick" />
      TEST: {{ testData }}
    </div>
  `,
  computed: {
    testData() {
      return this.$store.state?.user?._testData ?? 'test'
    }
  },
  methods: {
    handleClick() {
      // this.$store.commit('increment')
    }
  }
}

const factory = () => mount(App, {
  global: {
    plugins: [store]
  }
})

const store = createStore()

store.registerModule('user', user)

const app = createApp(App)

app.use(store)

describe('Vuex User Module', () => {
  test('show test state', async () => {
    const wrapper = factory()

    expect(wrapper.html()).toContain('TEST: test')
  })
})

