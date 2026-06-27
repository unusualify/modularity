import { describe, expect, test, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { defineComponent, ref } from 'vue'
import i18n from '../../src/js/config/i18n'
import TableFormatterCell from '../../src/js/components/table/TableFormatterCell.vue'
import useFormatter from '../../src/js/hooks/useFormatter.js'
import { shorten } from '../../src/js/utils/helpers.js'

beforeEach(() => {
  window.__shorten = shorten
  window.__isset = (v) => v !== undefined && v !== null && v !== ''
})

const FormatterHarness = defineComponent({
  components: { TableFormatterCell },
  props: {
    groupContext: { type: Boolean, default: false },
    cellValue: { type: String, default: '' },
    formatter: { type: Array, default: () => ['shorten', 5] },
    maxChars: { type: Number, default: 10 },
    disableTooltip: { type: Boolean, default: false }
  },
  setup (props) {
    const headers = ref([])
    const { handleFormatter } = useFormatter({}, {}, headers)
    return { handleFormatter }
  },
  template: `
    <TableFormatterCell
      :col="{ key: 'title', formatter: formatter }"
      :item="{}"
      :cell-value="cellValue"
      :handle-formatter="handleFormatter"
      :item-action="() => {}"
      :cell-options="{ maxChars: maxChars }"
      :group-context="groupContext"
      :disable-tooltip="disableTooltip"
    />
  `
})

/** edit/activate column with `formatter: ['shorten', n]` and `formatterName: 'edit'` (isFormatting true). */
const EditActivateHarness = defineComponent({
  components: { TableFormatterCell },
  props: {
    cellValue: { type: String, default: '' },
    shortenMax: { type: Number, default: 50 },
    disableTooltip: { type: Boolean, default: true }
  },
  setup (props) {
    const headers = ref([])
    const { handleFormatter } = useFormatter({}, {}, headers)
    return { handleFormatter }
  },
  template: `
    <TableFormatterCell
      :col="{
        key: 'title',
        formatter: ['shorten', shortenMax],
        formatterName: 'edit',
        isFormatting: true,
        target: '_blank'
      }"
      :item="{ title: cellValue }"
      :handle-formatter="handleFormatter"
      :item-action="() => {}"
      :cell-options="{}"
      :clickable-row="false"
      :disable-tooltip="disableTooltip"
    />
  `
})

describe('TableFormatterCell', () => {
  test('group header + shorten formatter uses 40 char max (ignores column shorten args)', async () => {
    const long = 'x'.repeat(60)
    const wrapper = mount(FormatterHarness, {
      global: {
        plugins: [i18n],
        stubs: {
          'ue-recursive-stuff': true,
          'ue-copy-text': true,
          'ue-dynamic-component-renderer': true
        }
      },
      props: {
        groupContext: true,
        cellValue: long,
        formatter: ['shorten', 5]
      }
    })
    const cell = wrapper.findComponent(TableFormatterCell)
    const bind = cell.vm.recursiveFormatterVBind
    expect(bind.configuration.elements).toBe(shorten(long, 40))
  })

  test('non-group shorten keeps pre-shorten + formatter args', async () => {
    const long = 'y'.repeat(60)
    const wrapper = mount(FormatterHarness, {
      global: {
        plugins: [i18n],
        stubs: {
          'ue-recursive-stuff': true,
          'ue-copy-text': true,
          'ue-dynamic-component-renderer': true
        }
      },
      props: {
        groupContext: false,
        cellValue: long,
        formatter: ['shorten', 5],
        maxChars: 10
      }
    })
    const cell = wrapper.findComponent(TableFormatterCell)
    const bind = cell.vm.recursiveFormatterVBind
    const pre = shorten(long, 10)
    expect(bind.configuration.elements).toBe(shorten(pre, 5))
  })

  /** Must match TableFormatterCell.vue MOBILE_SHORTEN_MAX_CHARS */
  const MOBILE_SHORTEN_MAX = 15

  test('group header + shorten on mobile caps at MOBILE_SHORTEN_MAX_CHARS', async () => {
    const long = 'x'.repeat(60)
    const wrapper = mount(FormatterHarness, {
      global: {
        plugins: [i18n],
        stubs: {
          'ue-recursive-stuff': true,
          'ue-copy-text': true,
          'ue-dynamic-component-renderer': true
        }
      },
      props: {
        groupContext: true,
        cellValue: long,
        formatter: ['shorten', 5],
        disableTooltip: true
      }
    })
    const cell = wrapper.findComponent(TableFormatterCell)
    const bind = cell.vm.recursiveFormatterVBind
    expect(bind.configuration.elements).toBe(shorten(long, MOBILE_SHORTEN_MAX))
  })

  test('non-group mobile shorten caps pre-maxChars and formatter second arg at MOBILE_SHORTEN_MAX_CHARS', async () => {
    const long = 'y'.repeat(60)
    const wrapper = mount(FormatterHarness, {
      global: {
        plugins: [i18n],
        stubs: {
          'ue-recursive-stuff': true,
          'ue-copy-text': true,
          'ue-dynamic-component-renderer': true
        }
      },
      props: {
        groupContext: false,
        cellValue: long,
        formatter: ['shorten', 50],
        maxChars: 100,
        disableTooltip: true
      }
    })
    const cell = wrapper.findComponent(TableFormatterCell)
    const bind = cell.vm.recursiveFormatterVBind
    const pre = shorten(long, MOBILE_SHORTEN_MAX)
    expect(bind.configuration.elements).toBe(shorten(pre, MOBILE_SHORTEN_MAX))
  })

  test('edit + shorten (isFormatting) on mobile caps shorten() arg at MOBILE_SHORTEN_MAX_CHARS', async () => {
    const long = 'z'.repeat(60)
    const wrapper = mount(EditActivateHarness, {
      global: {
        plugins: [i18n],
        stubs: {
          'ue-recursive-stuff': true,
          'ue-copy-text': true,
          'ue-dynamic-component-renderer': true
        }
      },
      props: {
        cellValue: long,
        shortenMax: 50,
        disableTooltip: true
      }
    })
    const cell = wrapper.findComponent(TableFormatterCell)
    expect(cell.vm.colFormatterForShortenPass).toEqual(['shorten', MOBILE_SHORTEN_MAX])
    const out = cell.vm.handleFormatter(cell.vm.colFormatterForShortenPass, long)
    expect(out.configuration.elements).toBe(shorten(long, MOBILE_SHORTEN_MAX))
  })
})
