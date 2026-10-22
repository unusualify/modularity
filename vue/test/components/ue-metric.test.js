import { describe, expect, test } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import Metric from '../../src/js/components/shared/Metric.vue'
import Metrics from '../../src/js/components/shared/Metrics.vue'

const vuetify = createVuetify({ components, directives })

const mountMetric = (props = {}) => mount(Metric, {
  global: { plugins: [vuetify] },
  props: { value: 12, label: 'In Progress', ...props },
})

describe('ue-metric', () => {
  test('stacked layout is the default and renders no divider', () => {
    const wrapper = mountMetric()

    expect(wrapper.classes()).not.toContain('ue-metric--horizontal')
    expect(wrapper.find('.v-divider').exists()).toBe(false)
    expect(wrapper.text()).toContain('12')
    expect(wrapper.text()).toContain('In Progress')
  })

  test('horizontal layout renders label, divider and value in order', () => {
    const wrapper = mountMetric({ layout: 'horizontal', color: 'primary', labelColor: 'grey-darken-1' })

    expect(wrapper.classes()).toContain('ue-metric--horizontal')

    const text = wrapper.find('.v-card-text')
    const children = text.element.children
    expect(children[0].classList.contains('ue-metric__label')).toBe(true)
    expect(children[1].classList.contains('v-divider')).toBe(true)
    expect(children[2].querySelector('.ue-metric__value').textContent.trim()).toBe('12')

    expect(wrapper.find('.ue-metric__value').classes()).toContain('text-primary')
    expect(wrapper.find('.ue-metric__label').classes()).toContain('text-grey-darken-1')
    expect(wrapper.find('.ue-metric__label').classes()).not.toContain('text-primary')
  })

  test('horizontal divider stretches with the tile instead of a fixed length', () => {
    const divider = mountMetric({ layout: 'horizontal' }).find('.v-divider')

    expect(divider.classes()).toContain('ue-metric__divider')
    expect(divider.classes().some(cls => cls.startsWith('text-'))).toBe(false)
    expect(divider.attributes('style') ?? '').not.toContain('height')
  })

  test('horizontal layout renders appendIcon only when provided', () => {
    expect(mountMetric({ layout: 'horizontal' }).find('.v-icon').exists()).toBe(false)
    expect(mountMetric({ layout: 'horizontal', appendIcon: 'mdi-check' }).find('.v-icon').exists()).toBe(true)
  })

  test('forwards card styling props', () => {
    const wrapper = mountMetric({ variant: 'flat', elevation: 0, rounded: 'lg', border: 'sm' })

    expect(wrapper.classes()).toContain('v-card--variant-flat')
    expect(wrapper.classes()).toContain('rounded-lg')
    expect(wrapper.classes()).toContain('border-sm')
  })
})

describe('ue-metrics', () => {
  const items = [
    { label: 'In Review', value: 1 },
    { label: 'Cancelled', value: 33 },
  ]

  const mountMetrics = (props = {}) => mount(Metrics, {
    global: {
      plugins: [vuetify],
      components: { UeMetric: Metric },
      stubs: { UeTitle: { template: '<div />' } },
      mocks: { $t: (key) => key },
    },
    props: { title: 'AT A GLANCE', items, ...props },
  })

  test('defaults keep stacked metrics in a flex row and a flat panel', () => {
    const wrapper = mountMetrics()

    expect(wrapper.classes()).toContain('elevation-0')
    expect(wrapper.find('.d-flex.flex-wrap').exists()).toBe(true)
    expect(wrapper.find('.ue-metrics__grid').exists()).toBe(false)
    expect(wrapper.findAll('.ue-metric--horizontal')).toHaveLength(0)
  })

  test('horizontal metricLayout renders a grid of horizontal metrics inside a bordered panel', () => {
    const wrapper = mountMetrics({ metricLayout: 'horizontal', rounded: 'lg', border: 'sm', minMetricWidth: 140 })

    expect(wrapper.classes()).toContain('rounded-lg')
    expect(wrapper.classes()).toContain('border-sm')

    const grid = wrapper.find('.ue-metrics__grid')
    expect(grid.exists()).toBe(true)
    expect(grid.attributes('style')).toContain('minmax(140px, 1fr)')
    expect(wrapper.findAll('.ue-metric--horizontal')).toHaveLength(2)
  })

  test('metricColumns caps the horizontal grid while keeping minMetricWidth as the floor', () => {
    const wrapper = mountMetrics({ metricLayout: 'horizontal', metricColumns: 2, minMetricWidth: 140 })

    expect(wrapper.find('.ue-metrics__grid').attributes('style'))
      .toContain('minmax(max(140px, calc((100% - 16px) / 2)), 1fr)')
  })

  test('headerDivider adds a full-width divider between header and metrics', () => {
    expect(mountMetrics().find('.ue-metrics__header-divider').exists()).toBe(false)
    expect(mountMetrics().find('.v-card-title').classes()).toContain('pa-4')

    const wrapper = mountMetrics({ headerDivider: true })
    const divider = wrapper.find('.ue-metrics__header-divider')
    expect(divider.exists()).toBe(true)
    expect(divider.element.previousElementSibling.classList.contains('v-card-title')).toBe(true)
    expect(divider.element.nextElementSibling.classList.contains('v-card-text')).toBe(true)
    expect(wrapper.find('.v-card-title').classes()).toEqual(expect.arrayContaining(['px-4', 'pt-4', 'pb-5']))
  })

  test('titleAttributes are forwarded to the title without conflicting weight classes', () => {
    const titleStub = {
      props: ['text', 'type', 'weight', 'color'],
      template: '<div class="title-stub" :data-type="type" :data-weight="weight" :data-color="color">{{ text }}</div>',
    }
    const mountWithTitle = (props = {}) => mount(Metrics, {
      global: {
        plugins: [vuetify],
        components: { UeMetric: Metric },
        stubs: { UeTitle: titleStub },
        mocks: { $t: (key) => key },
      },
      props: { title: 'AT A GLANCE', items, ...props },
    })

    const defaults = mountWithTitle().find('.title-stub')
    expect(defaults.attributes('data-weight')).toBeUndefined()
    expect(defaults.classes()).not.toContain('font-weight-medium')

    const flat = mountWithTitle({ titleAttributes: { type: 'title-large', weight: 'regular', color: 'grey' } }).find('.title-stub')
    expect(flat.attributes('data-type')).toBe('title-large')
    expect(flat.attributes('data-weight')).toBe('regular')
    expect(flat.attributes('data-color')).toBe('grey')
  })

  test('compactFilter swaps the fixed 280px filter for the compact field', () => {
    const filterable = [{ label: 'Task Deadlines', value: 0, connectorFilter: { name: 'lastStatusAssignment', args: {} } }]
    const mountFilterable = (props = {}) => mountMetrics({
      items: filterable,
      endpoint: '/metrics',
      ...props,
    })
    const legacy = mountFilterable()
    const legacyFilter = legacy.find('.v-card-title > div:last-child')
    expect(legacyFilter.attributes('style')).toContain('width: 280px')
    expect(legacyFilter.classes()).not.toContain('ue-metrics__filter--compact')

    const compact = mountFilterable({ compactFilter: true, filterPlaceholder: 'Date - Date' })
    const compactFilter = compact.find('.ue-metrics__filter--compact')
    expect(compactFilter.exists()).toBe(true)
    expect(compactFilter.attributes('style')).toBeUndefined()
    expect(compact.find('.v-card-title').classes()).toContain('ga-3')
    expect(compact.find('[placeholder="Date - Date"]').exists()).toBe(true)
  })
})
