<template>
  <v-app
    id="inspire"
    :class="{
      'ue-sidebar-expanded': sidebarExpandedClass,
      'ue-sidebar-rail-only': sidebarRailOnly,
      'ue-sidebar-fully-hidden': sidebarHiddenOverlay
    }"
    :style="sidebarLayoutStyle"
  >
    <v-chip
      v-if="store.getters.isHot"
      color="green"
      class="position-absolute"
      style="top: 4px; right: 0; z-index: 10000;"
      @click="store.state.ambient.isHot = false"
    >
      Development Mode
    </v-chip>

    <!-- Top bar (v-app-bar) - configurable via ui_settings.topbar / user preferences -->
    <v-app-bar v-if="showTopbar" app :order="topbarOrder">
      <slot name="app-bar">
        <v-app-bar-nav-icon
          v-if="!lgAndUp && !hideDefaultSidebar"
          :icon="!store.getters.sidebarStatus ? '$menu' : '$close'"
          @click="$toggleSidebar()"
        />
        <v-toolbar-title class="flex-1-1-100 ml-0 text-center">
          {{ headerTitle }}
        </v-toolbar-title>
        <v-spacer />
        <div class="d-flex justify-end mr-4">
          <div class="d-flex align-center">
            <v-avatar
              :image="store.getters.userProfile.avatar_url"
              @click="$openProfileDialog"
            />
          </div>
        </div>
      </slot>
    </v-app-bar>

    <ue-sidebar
      v-if="!hideDefaultSidebar"
      ref="sidebarRef"
      :items="sidebarItems"
      v-bind="sidebarAttributes"
      :profile-menu="profileMenu"
    >
      <template v-slot:bottom>
        <ue-navigation-group v-if="sidebarBottom.length > 0" :items="sidebarBottom" />
        <ImpersonateToolbar
          v-if="impersonation.active"
          v-model="showImpersonateToolbar"
          v-bind="impersonation"
        />
      </template>
    </ue-sidebar>

    <v-main class="d-flex flex-column flex-grow-1 min-height-0">
      <slot name="top" />
      <slot />
      <slot name="bottom" />
    </v-main>

    <!-- Bottom navigation (v-bottom-navigation) - optional, for mobile-first layouts -->
    <v-bottom-navigation v-if="showBottomNav" v-model="bottomNavValue" app grow shift>
      <slot name="bottom-nav">
        <v-btn value="home" icon="mdi-home" href="/" />
        <v-btn value="profile" icon="mdi-account" @click="$openProfileDialog" />
      </slot>
    </v-bottom-navigation>

    <!-- Modals -->
    <ue-modal-media
      ref="mediaLibrary"
      v-if="store.getters.mediaLibraryAccessible"
      v-model="store.state.mediaLibrary.showModal"
    />

    <ue-modal
      ref="deleteWarningMediaModalRef"
      v-model="showDeleteWarning"
      transition="dialog-bottom-transition"
      width-type="sm"
      :confirm-text="$t('media-library.dialogs.delete.delete-media-confirm')"
      cancel-text="Cancel"
    >
      <template v-slot:body.description>
        <div class="d-flex flex-column align-center justify-center">
          <p class="modal--tiny-title">
            <strong>{{ $t('media-library.dialogs.delete.delete-media-title') }}</strong>
          </p>
          <p v-html="$t('media-library.dialogs.delete.delete-media-desc')" />
        </div>
      </template>
    </ue-modal>

    <ue-modal
      ref="profileDialogRef"
      v-model="store.state.user.profileDialog"
      scrollable
    >
      <template v-slot:body="{ isActive, toggleFullscreen, close, isFullActive }">
        <v-card>
          <v-card-title>
            <ue-title
              padding="y-3"
              :text="$t('Upload Profile Image')"
              color="grey-darken-5"
              transform="none"
              align="center"
              justify="space-between"
            >
              <template #right>
                <div class="d-flex align-center">
                  <v-icon
                    :icon="isFullActive ? 'mdi-fullscreen-exit' : 'mdi-fullscreen'"
                    variant="plain"
                    color="grey-darken-5"
                    size="default"
                    @click="toggleFullscreen()"
                  />
                  <v-icon
                    icon="$close"
                    variant="plain"
                    color="grey-darken-5"
                    size="default"
                    @click="close()"
                  />
                </div>
              </template>
            </ue-title>
            <v-divider />
          </v-card-title>
          <v-card-text>
            <div class="d-flex">
              <div class="my-3 flex-grow-0">
                <v-avatar
                  class="my-aut"
                  :image="store.getters.userProfile.avatar_url"
                  size="100"
                />
              </div>
              <ue-form
                class="flex-grow-1 pl-6"
                :schema="store.state.user.profileShortcutSchema"
                v-model="store.state.user.profileShortcutModel"
                :action-url="store.state.user.profileRoute"
                :async="true"
                :hasSubmit="true"
                no-default-form-padding
                is-editing
                buttonText="fields.save"
                @submitted="profileFormSubmitted"
              />
            </div>
          </v-card-text>
        </v-card>
      </template>
    </ue-modal>

    <ue-alert ref="alertRef" />
    <ue-broadcast-toasts />
    <ue-retainable-notifications />
    <ue-dynamic-modal />

    <ue-modal
      ref="dialogRef"
      v-model="alertDialog"
      scrollable
      transition="dialog-bottom-transition"
      width-type="lg"
      persistent
    >
      <template v-slot:body>
        <v-card>
          <v-card-text class="text-center" style="word-break: break-word;">
            <div v-html="alertDialogMessage" />
          </v-card-text>
          <v-divider />
          <v-card-actions class="justify-center">
            <v-btn-secondary @click="closeAlertDialog">
              {{ $t('fields.close') }}
            </v-btn-secondary>
          </v-card-actions>
        </v-card>
      </template>
    </ue-modal>

    <ue-modal
      ref="loginModalRef"
      v-model="store.state.user.showLoginModal"
      scrollable
      transition="dialog-bottom-transition"
      width-type="sm"
      persistent
    >
      <template v-slot:body="{ toggleFullscreen, close, isFullActive }">
        <v-card>
          <v-card-title>
            <ue-title
              padding="y-3"
              color="grey-darken-5"
              transform="none"
              align="center"
              justify="space-between"
            >
              <div>
                {{ $t('Login') }}
                <br />
                <span class="text-grey-darken-2 text-body-small">
                  {{ $t('Your session has expired, please login again.') }}
                </span>
              </div>
            </ue-title>
            <v-divider />
          </v-card-title>
          <v-card-text>
            <ue-form
              class="flex-grow-1"
              :schema="store.state.user.loginShortcutSchema"
              v-model="store.state.user.loginShortcutModel"
              :action-url="store.state.user.loginRoute"
              :async="true"
              :hasSubmit="true"
              no-default-form-padding
              buttonText="fields.login"
              titleJustify="center"
              @submitted="loginFormSubmitted"
            />
          </v-card-text>
        </v-card>
      </template>
    </ue-modal>
  </v-app>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useStore } from 'vuex'
import { useDisplay } from 'vuetify'
import { useConfig, useNavigationLayout, useSidebar } from '@/hooks'
import { ALERT, CONFIG, MEDIA_LIBRARY, USER } from '@/store/mutations'
import ImpersonateToolbar from '__components/others/ImpersonateToolbar.vue'

const props = defineProps({
  fixedAppBar: { type: Boolean, default: false },
  appBarOrder: { type: Number, default: 0 },
  headerTitle: { type: String, default: '' },
  hideDefaultSidebar: { type: Boolean, default: false },
  navigation: {
    type: Object,
    default: () => ({
      sidebar: [],
      breadcrumbs: [],
      profileMenu: [],
      sidebarBottom: [],
    }),
  },
  impersonation: { type: Object, default: () => ({}) },
  authorization: { type: Object, default: () => ({}) },
  sidebarAttributes: { type: Object, default: () => ({}) },
})

const store = useStore()
const { lgAndUp } = useDisplay()
const { shouldUseInertia } = useConfig()
const { showTopbar: showTopbarRaw, showBottomNav, topbarOptions } = useNavigationLayout()
const { isExpanded, expandHover, rail, railWidth, width, sidebarPinned } = useSidebar()

const sidebarRef = ref(null)
const alertRef = ref(null)
const bottomNavValue = ref('home')
const showDeleteWarning = ref(false)
const showImpersonateToolbar = ref(false)

const sidebarItems = computed(() => props.navigation?.sidebar ?? [])
const profileMenu = computed(() => props.navigation?.profileMenu ?? [])
const sidebarBottom = computed(() => props.navigation?.sidebarBottom ?? [])

const sidebarStatus = computed(() => store.state.config.sidebarStatus)
const sidebarExpanded = computed(() => isExpanded.value)
const sidebarHiddenOverlay = computed(
  () => expandHover.value === 'hidden' && !sidebarPinned.value
)
const sidebarRailOnly = computed(
  () =>
    lgAndUp.value &&
    rail.value &&
    sidebarStatus.value &&
    ((expandHover.value === 'mini' && !sidebarExpanded.value) ||
      (expandHover.value === 'hidden' && sidebarPinned.value))
)
const sidebarExpandedClass = computed(
  () =>
    lgAndUp.value &&
    sidebarStatus.value &&
    ((expandHover.value === 'hidden' && sidebarPinned.value && !rail.value) ||
      (expandHover.value === 'mini' && sidebarExpanded.value))
)
const sidebarLayoutStyle = computed(() => {
  if (!lgAndUp.value) return undefined
  if (sidebarExpandedClass.value) return { '--ue-sidebar-width': `${width.value}px` }
  if (sidebarRailOnly.value) return { '--ue-sidebar-rail-width': `${railWidth.value}px` }
  return undefined
})

const topbarOrder = computed(() => props.appBarOrder ?? topbarOptions.value?.order ?? 0)

const showTopbar = computed(() => {
  if (props.fixedAppBar) return true
  if (topbarOptions.value?.enabled === false) return false
  return showTopbarRaw.value
})

const alertDialog = computed({
  get: () => store.state.alert.dialog,
  set: (val) => store.commit(ALERT.SET_DIALOG_SHOW, val),
})
const alertDialogMessage = computed(() => store.state.alert.dialogMessage)

function closeAlertDialog() {
  store.commit(ALERT.CLEAR_DIALOG)
}

function loginFormSubmitted(res) {
  if (res.variant !== 'success') return
  store.commit(ALERT.SET_ALERT, { ...res })
  const reload = () => {
    if (shouldUseInertia.value && false) {
      router.reload()
      store.commit(USER.CLOSE_LOGIN_MODAL)
    } else {
      window.location.reload()
    }
  }
  if (res.timeout) {
    setTimeout(reload, res.timeout)
  } else {
    reload()
  }
}

function profileFormSubmitted() {
  if (typeof URLS !== 'undefined' && URLS) {
    axios.get(URLS.profileShow).then((res) => {
      store.commit(USER.SET_PROFILE_DATA, res.data)
    })
  }
}

onMounted(() => {
  // Below lg: drawer is overlay; never leave it open after full page load (mini + persisted status:true).
  if (!lgAndUp.value) {
    store.commit(CONFIG.SET_SIDEBAR, false)
  }

  const hasAuth = props.authorization && typeof props.authorization === 'object'
  const notEmpty = hasAuth && Object.keys(props.authorization).length > 0
  store.commit(
    MEDIA_LIBRARY.SET_ACCESSIBLE,
    hasAuth && notEmpty && !props.authorization.isClient
  )
})
</script>

<style>
.min-height-0 {
  min-height: 0;
}

.v-main > .v-main__wrap {
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  min-height: 0;
  max-width: 100%;
}
</style>
