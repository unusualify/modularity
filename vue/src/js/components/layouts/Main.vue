<template>
  <v-app id="inspire">
    <v-chip v-if="$store.getters.isHot"
      color="green"
      class="position-absolute"
      style="top: 4px; right: 0;  z-index: 10000;"
      @click="$store.state.ambient.isHot = false"
    >
      Development Mode
    </v-chip>

    <!-- Mobile Header -->
    <v-app-bar v-if="$vuetify.display.mdAndDown || fixedAppBar"
      app
      :order="appBarOrder"
    >
      <slot name="app-bar">
        <v-app-bar-nav-icon v-if="!$vuetify.display.lgAndUp && !hideDefaultSidebar"
          :icon="!$store.getters.sidebarStatus ? '$menu' : '$close'"
          @click="$toggleSidebar()">
        </v-app-bar-nav-icon>

        <v-toolbar-title class="flex-1-1-100 ml-0 text-center">
          {{ headerTitle }}
        </v-toolbar-title>

        <v-spacer></v-spacer>

        <div class="d-flex justify-end mr-4">
          <!-- User Profile Image-->
          <div class="d-flex align-center">
            <v-avatar
              :image="$store.getters.userProfile.avatar_url"
              @click="$openProfileDialog"
            />
          </div>
        </div>

        <!-- #language selector -->
        <v-toolbar-title v-if="false">
          <!-- {{ $t('fields.list') }}
          {{ $n(100.77, 'currency') }} -->
          {{ $t('fields.language-select') }}
          <select v-model="$i18n.locale">
            <option v-for="(lang, i) in langs" :key="`Lang${i}`" :value="lang">
              {{ lang }}
            </option>
          </select>
        </v-toolbar-title>
      </slot>

    </v-app-bar>

    <ue-sidebar v-if="!hideDefaultSidebar"
      ref="sidebar"
      :items="sidebarItems"
    >
      <template v-slot:bottom>
        <ue-impersonate-toolbar v-if="impersonation.active"
          v-model="showImpersonateToolbar"
          v-bind="impersonation"
        />
      </template>
    </ue-sidebar>

    <v-main>
      <slot name="top"></slot>

      <!-- <ue-footer :items="footerLinks" /> -->
      <div v-if="false">
        <v-breadcrumbs :items="breadcrumbs">
          <template v-slot:divider>
            <v-icon>mdi-forward</v-icon>
          </template>
        </v-breadcrumbs>
      </div>

      <slot></slot>

      <slot name="bottom"></slot>
    </v-main>

    <!-- MODALS -->
    <!-- Media Library -->
    <ue-modal-media v-if="$store.getters.mediaLibraryAccessible"
      ref="mediaLibrary"
      v-model="$store.state.mediaLibrary.showModal"
    ></ue-modal-media>

    <!-- Delete Warning Media Modal -->
    <ue-modal
      ref="deleteWarningMediaModal"
      v-model="showDeleteWarning"
      transition="dialog-bottom-transition"
      width-type="sm"
      :confirm-text="$t('media-library.dialogs.delete.delete-media-confirm')"
      :cancel-text="`Cancel`"
      >
      <template v-slot:body.description>
        <p class="modal--tiny-title"><strong>{{ $t("media-library.dialogs.delete.delete-media-title") }}</strong></p>
        <p v-html="$t('media-library.dialogs.delete.delete-media-desc')"></p>
      </template>
    </ue-modal>

    <!-- Profile Dialog -->
    <ue-modal
      ref="profileDialog"
      v-model="$store.state.user.profileDialog"
      Xwidth="500"
      scrollable
    >
      <template v-slot:body="{ isActive, toggleFullscreen, close , isFullActive}">
        <v-card>
          <v-card-title>
            <ue-title padding="y-3" :text="$t('Upload Profile Image')" color="grey-darken-5" transform="none" align="center" justify="space-between">
              <template #right>
                <div class="d-flex align-center">
                  <v-icon :icon="isFullActive ? 'mdi-fullscreen-exit' : 'mdi-fullscreen'" variant="plain" color="grey-darken-5" size="default" @click="toggleFullscreen()"/>
                  <v-icon icon="$close" variant="plain" color="grey-darken-5" size="default" @click="close()"/>
                </div>
              </template>
            </ue-title>
            <v-divider/>
          </v-card-title>

          <v-card-text Xstyle="height: 30vh;">
            <div class="d-flex">
              <div class="my-3 flex-grow-0">
                <v-avatar class="my-aut" :image="$store.getters.userProfile.avatar_url" size="100"/>
              </div>
              <ue-form
                class="flex-grow-1 pl-6"
                :schema="$store.state.user.profileShortcutSchema"
                v-model="$store.state.user.profileShortcutModel"
                :action-url="$store.state.user.profileRoute"

                :async="true"
                :hasSubmit="true"
                no-default-form-padding
                is-editing
                buttonText="fields.save"

                @submitted="profileFormSubmitted"
              >
              </ue-form>
            </div>
            <!-- <div class="">
              <v-radio-group
                v-model="dialog"
                messages="Select a Country from the radio group"
              >
                <v-radio v-for="([label, value]) in [['1', 1], ['2', 2], ['3', 3], ['4', 4], ['5', 5], ['6', 6], ['7', 7], ['8', 8], ['9', 9], ['10', 10]]"
                  :key="value"
                  :label="label"
                  :value="value"
                ></v-radio>
              </v-radio-group>
            </div> -->
          </v-card-text>

        </v-card>
      </template>
    </ue-modal>

    <ue-alert ref='alert'></ue-alert>
    <ue-dynamic-modal></ue-dynamic-modal>

    <ue-modal
      ref='dialog'
      v-model="alertDialog"
      scrollable
      transition="dialog-bottom-transition"
      width-type="lg"
      persistent
      >
      <template v-slot:body="props">
        <v-card >
          <v-card-text class="text-center" style="word-break: break-word;" >
            <div v-html="alertDialogMessage"></div>
          </v-card-text>
          <v-divider/>
          <v-card-actions class="justify-center">
              <v-btn-secondary  @click="closeAlertDialog">
                {{ $t('fields.close') }}
              </v-btn-secondary>
          </v-card-actions>
        </v-card>
      </template>
    </ue-modal>

    <!-- Login Modal -->
    <ue-modal
      ref="loginModal"
      v-model="$store.state.user.showLoginModal"
      scrollable
      transition="dialog-bottom-transition"
      width-type="sm"
      persistent
    >
      <template v-slot:body="{ isActive, toggleFullscreen, close , isFullActive}">
        <v-card>
          <v-card-title>
            <ue-title padding="y-3" color="grey-darken-5" transform="none" align="center" justify="space-between">
              <div>
                {{ $t('Login') }}
                <br>
                <span class="text-grey-darken-2 text-caption" >{{ $t('Your session has expired, please login again.') }}</span>
              </div>
            </ue-title>
            <v-divider/>
          </v-card-title>

          <v-card-text>
            <ue-form
              class="flex-grow-1"
              :schema="$store.state.user.loginShortcutSchema"
              v-model="$store.state.user.loginShortcutModel"
              :action-url="$store.state.user.loginRoute"

              :async="true"
              :hasSubmit="true"
              no-default-form-padding
              buttonText="fields.login"

              @submitted="loginFormSubmitted"
            >
            </ue-form>
          </v-card-text>

        </v-card>
      </template>
    </ue-modal>
  </v-app>
</template>

<script>
  import { ALERT, CONFIG, MEDIA_LIBRARY } from '@/store/mutations/index'
  import { USER } from '@/store/mutations';

  export default {
    props: {
      fixedAppBar: {
        type: Boolean,
        default: false
      },
      appBarOrder: {
        type: Number,
        default: 0
      },
      headerTitle: {
        type: String,
      },
      hideDefaultSidebar: {
        type: Boolean,
        default: false
      },
      navigation: {
        type: Object,
        default () {
          return {
            sidebar: [
              {
                icon: '$package',
                text: 'Packages',
                action: '',
                is_active: false
              },
              {
                icon: '$creditCards',
                text: 'Payments',
                action: '',
                is_active: false
              },
              {
                icon: '$product',
                text: 'Customers',
                action: '',
                is_active: false
              },
              {
                icon: '$users',
                text: 'Users',
                is_active: true,
                items: [
                  {
                    icon: '$users',
                    text: 'Show Users',
                    action: '',
                    is_active: false
                  },
                  {
                    icon: '$userAdd',
                    text: 'Add User',
                    action: '',
                    is_active: false
                  },
                  {
                    icon: '$role',
                    text: 'Roles',
                    is_active: true,
                    items: [
                      {
                        icon: '$permission',
                        text: 'Add Permission',
                        action: '',
                        is_active: true
                      }
                    ]
                  }
                ]
              },

              {
                icon: '$check',
                text: 'Settings',
                action: '',
                is_active: false
              },
              {
                icon: 'fas fa-plus',
                text: 'Others',
                action: '',
                is_active: false
              }
            ]
          }
        }
      },
      impersonation: {
        type: Object,
        default () {
          return {

          }
        }
      },
      authorization: {
        type: Object,
        default () {
          return {

          }
        }
      }
    },
    data () {
      return {
        loader: null,
        loading: false,
        langs: ['tr', 'en'],

        footerDisplay: false,
        sidebarItem: [
          {
            icon: '$package',
            text: 'Packages',
            action: ''
          },
          {
            icon: '$creditCards',
            text: 'Payments',
            action: ''
          },
          {
            icon: '$product',
            text: 'Customers',
            action: ''
          },
          {
            icon: '$users',
            text: 'Users',
            items: [
              {
                icon: '$users',
                text: 'Show Users',
                action: ''
              },
              {
                icon: '$userAdd',
                text: 'Add User',
                action: ''
              }
            ]
          },

          {
            icon: '$check',
            text: 'Settings',
            action: ''
          },
          {
            icon: 'fas fa-plus',
            text: 'Others',
            action: ''
          }
        ],

        sidebarItems: (this.navigation && this.navigation.sidebar) ? this.navigation.sidebar : [],
        // activeItem: this.navigation.activeItem ?? 0,
        // activeSubItem: this.navigation.activeSubItem ?? -1,
        breadcrumbs: this.navigation.breadcrumbs ?? [],

        footerLinks: [
          {
            icon: 'mdi-facebook',
            url: 'facebook.com'
          },
          {
            icon: 'mdi-twitter',
            url: 'twitter.com'
          },
          {
            icon: 'mdi-linkedin',
            url: 'linkedin.com'
          },
          {
            icon: 'mdi-instagram',
            url: 'instagram.com'
          }
        ],

        showDeleteWarning: false,
        showImpersonateToolbar: false
      }
    },
    computed: {
      alertDialog: {
        get () {
          return this.$store.state.alert.dialog
        },
        set (val) {
          this.$store.commit(ALERT.SET_DIALOG_SHOW, val)
        }
      },
      alertDialogMessage() {
        return this.$store.state.alert.dialogMessage
      },
    },
    created () {
      if(this.$vuetify.display.mdAndDown){
        this.$store.commit(CONFIG.SET_SIDEBAR, false)
      }

      this.$store.commit(MEDIA_LIBRARY.SET_ACCESSIBLE, this.authorization
        && !this.$lodash.isEmpty(this.authorization)
        && !this.authorization.isClient
      )
    },

    mounted () {
    },

    methods: {
      addSidebarItem: function (event) {
        // drawer = !drawer
        // this.loading = true;
        // const l = this.loader;
        // this[l] = !this[l];

        // this.sidebarItem.push( {
        //   icon: "fas fa-circle-question",
        //   text: "New"
        // });

        this.$refs.sidebar.addItem({ icon: 'fa-regular fa-circle-question', text: 'New' })

        // setTimeout(() => (this[l] = false), 3000)

        // this.loader = null;
      },

      submit () {
        alert('Submit Form')
      },
      closeAlertDialog(){
        this.$store.commit(ALERT.CLEAR_DIALOG)
      },
      profileFormSubmitted(res) {

        if (typeof URLS !== 'undefined' && URLS) {
          axios.get(URLS.profileShow).then(res => {
            this.$store.commit(USER.SET_PROFILE_DATA, res.data)
          })
        }
      },
      loginFormSubmitted(res) {
        // this.$store.commit(USER.CLOSE_LOGIN_MODAL)
        if(res.variant === 'success') {
          this.$store.commit(ALERT.SET_ALERT, {...res})
          if(res.timeout) {
            setTimeout(() => {
              window.location.reload()
            }, res.timeout)
          }else{
            window.location.reload()
          }
        }
      }
    }
  }
</script>
