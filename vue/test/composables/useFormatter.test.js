// test/composables/useFormatter.test.js
import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import { defineComponent } from 'vue'

import useFormatter from '../../src/js/hooks/useFormatter.js'

import i18n from '../../src/js/config/i18n'

const headers = [
  {
    "align": "start",
    "sortable": false,
    "filterable": false,
    "groupable": false,
    "divider": false,
    "class": "text-primary",
    "cellClass": "",
    "width": 30,
    "noPadding": true,
    "searchable": true,
    "isRowEditable": false,
    "isColumnEditable": false,
    "formatter": ["date", "numeric"],
    "title": "Created Time",
    "key": "created_at_timestamp"
  }
]

useFormatter.testMethod = () => true

const TestComponent = defineComponent({
  template: `
    <div>
      Test
    </div>
  `,
  props: {

  },
  setup (props, context) {
    let hook = useFormatter(props, context, headers);

    hook.testMethod = () => true

    return {
      // Call the composable and expose all return values into our
      // component instance so we can access them with wrapper.vm
      ...hook
    }
  }
})

async function factory(props = {}, options = {}) {
  return await mount(TestComponent, {
    global: {
        plugins: [
            i18n // adding plugin example
        ],
    },
    ...options,
    props: {
      ...props
    }
  })
}
describe('useFormatter composable', () => {

  test('show test state', async () => {

    const wrapper = await factory()

    expect(wrapper.vm.testMethod()).toBe(true)

    expect(wrapper.vm.dateFormatter('2024-01-12T11:56:43.000000Z', 'numeric').configuration.elements).toBe('1/12/2024')

  })

})

