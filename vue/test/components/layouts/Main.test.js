import { describe, expect, test, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createStore } from 'vuex'
import { CONFIG } from '@/store/mutations'
import Main from '@/components/layouts/Main.vue'
import createModularousVuetify from '@/plugins/vuetify'
import mediaLibraryModule from '@/store/modules/media-library'
import i18n from '@/config/i18n'

const vuetify = createModularousVuetify()

const defaultNavigation = {
  sidebar: [],
  profileMenu: [],
  sidebarBottom: [],
}

const mainStubs = {
  'ue-sidebar': { name: 'UeSidebar', template: '<div><slot /><slot name="bottom" /></div>' },
  'ue-modal': { template: '<div />' },
  'ue-modal-media': true,
  'ue-alert': true,
  'ue-dynamic-modal': true,
  'ue-impersonate-toolbar': true,
  'ue-navigation-group': true,
  'ue-title': true,
  'ue-form': true,
}

function createTestStore() {
  return createStore({
    modules: {
      config: {
        namespaced: false,
        state: {
          sidebarStatus: true,
          sidebarOptions: { expandHover: 'mini', rail: false, width: 264 },
          topbarOptions: { enabled: true, showOnMobile: true, showOnDesktop: true },
          bottomNavigationOptions: { enabled: false },
          uiPreferences: {},
          profileMenu: [],
        },
        mutations: {
          [CONFIG.SET_SIDEBAR](state, status = true) {
            state.sidebarStatus = status
          },
          [CONFIG.SIDEBAR_TOGGLE](state) {
            state.sidebarStatus = !state.sidebarStatus
          },
        },
      },
      alert: {
        namespaced: false,
        state: { dialog: false, dialogMessage: '' },
      },
      user: {
        namespaced: false,
        state: {
          profileDialog: false,
          showLoginModal: false,
          profile: { avatar_url: '', name: '', email: '' },
          profileShortcutSchema: {},
          profileShortcutModel: {},
          profileRoute: '',
          loginShortcutSchema: {},
          loginShortcutModel: {},
          loginRoute: '',
          isGuest: false,
        },
      },
      mediaLibrary: mediaLibraryModule,
      ambient: {
        namespaced: false,
        state: { isHot: false },
      },
    },
    getters: {
      sidebarStatus: (state) => state.config?.sidebarStatus ?? true,
      isHot: () => false,
      userProfile: (state) => state.user?.profile ?? { avatar_url: '', name: '', email: '' },
      appName: () => 'Test App',
      appEmail: () => 'test@example.com',
      isGuest: () => false,
    },
  })
}

function mountMain(mountOptions = {}) {
  const store = mountOptions.store ?? createTestStore()

  return mount(Main, {
    ...mountOptions,
    global: {
      plugins: [store, vuetify, i18n],
      stubs: mainStubs,
      mocks: {
        $openProfileDialog: vi.fn(),
        $toggleSidebar: vi.fn(),
      },
      ...mountOptions.global,
    },
  })
}

describe('Main', () => {
  test('renders v-app with id inspire', () => {
    const wrapper = mountMain({
      props: {
        headerTitle: 'Test App',
        navigation: defaultNavigation,
      },
    })

    expect(wrapper.find('#inspire').exists()).toBe(true)
  })

  test('renders sidebar when hideDefaultSidebar is false', () => {
    const wrapper = mountMain({
      props: {
        headerTitle: 'Test',
        hideDefaultSidebar: false,
        navigation: {
          sidebar: [{ icon: 'mdi-home', text: 'Home' }],
          profileMenu: [],
          sidebarBottom: [],
        },
      },
    })

    expect(wrapper.findComponent({ name: 'UeSidebar' }).exists()).toBe(true)
  })

  test('hides sidebar when hideDefaultSidebar is true', () => {
    const wrapper = mountMain({
      props: {
        headerTitle: 'Test',
        hideDefaultSidebar: true,
        navigation: defaultNavigation,
      },
    })

    expect(wrapper.findComponent({ name: 'UeSidebar' }).exists()).toBe(false)
  })

  test('renders v-main with default slot', () => {
    const wrapper = mountMain({
      props: {
        headerTitle: 'Test',
        navigation: defaultNavigation,
      },
      slots: {
        default: '<div data-testid="main-content">Main content</div>',
      },
    })

    expect(wrapper.find('[data-testid="main-content"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="main-content"]').text()).toBe('Main content')
  })
})
