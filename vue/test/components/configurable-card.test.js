import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ConfigurableCard from '../../src/js/components/labs/ConfigurableCard.vue'
import vuetify from '../../src/js/plugins/vuetify'
import UEConfig from '../../src/js/plugins/UEConfig'

describe('ConfigurableCard', () => {
  const createWrapper = (props = {}) => {
    return mount(ConfigurableCard, {
      props,
      global: {
        plugins: [UEConfig]
      }
    })
  }

  describe('Press Release Content', () => {
    it('renders simple array items', () => {
      const wrapper = createWrapper({
        title: 'Press Release Content',
        items: [
          'I have Press Release',
          ['Filename.docx', 'Filename.png'],
          {
            Date: '24.09.2024',
            Time: '15:00',
            'Time Zone': 'GMT +3'
          }
        ]
      })
      expect(wrapper.html()).toMatchSnapshot('press-release-content')
    })
  })

  describe('Package Details', () => {
    it('renders nested object structure', () => {
      const wrapper = createWrapper({
        title: 'Package Details',
        items: {
          details: {
            Package: 'Exclusive',
            Languages: 'English, German, French',
            'Distribution Countries': 'Germany, France, Türkiye'
          },
          content: {
            Content: 'Filename.docx',
            Media: 'Filename.png',
            Date: '24.09.2024',
            Time: '15:00',
            'Time Zone': 'GMT +3 (Türkiye)'
          },
          price: '$1,450 + VAT',
          status: 'Pending Payment'
        }
      })
      expect(wrapper.html()).toMatchSnapshot('package-details')
    })
  })

  describe('Addon Configuration', () => {
    it('renders info structure', () => {
      const wrapper = createWrapper({
        title: 'ADDON',
        items: {
          info: {
            title: 'Lorem Ipsum Dolor',
            description: 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis At vero eos et accusamus etiu.'
          }
        }
      })
      expect(wrapper.html()).toMatchSnapshot('addon-configuration')
    })
  })

  describe('Actions', () => {
    it('renders with action buttons', () => {
      const actions = [
        { icon: 'mdi-pencil', color: 'primary', onClick: () => {} },
        { icon: 'mdi-delete', color: 'error', onClick: () => {} },
        { text: 'complete', color: 'primary', class: 'px-6', onClick: () => {} }
      ]

      const wrapper = createWrapper({
        title: 'With Actions',
        items: { test: 'value' },
        actions
      })

      const actionButtons = wrapper.findAll('.v-btn')
      expect(actionButtons).toHaveLength(3)
      expect(wrapper.html()).toMatchSnapshot('with-actions')
    })

    it('handles action click events', async () => {
      const mockEdit = vi.fn()
      const mockDelete = vi.fn()

      const wrapper = createWrapper({
        title: 'Test Actions',
        items: { test: 'value' },
        actions: [
          { icon: 'mdi-pencil', color: 'primary', onClick: mockEdit },
          { icon: 'mdi-delete', color: 'error', onClick: mockDelete }
        ]
      })

      const buttons = wrapper.findAll('.v-btn')
      await buttons[0].trigger('click')
      expect(mockEdit).toHaveBeenCalled()

      await buttons[1].trigger('click')
      expect(mockDelete).toHaveBeenCalled()
    })
  })
})
