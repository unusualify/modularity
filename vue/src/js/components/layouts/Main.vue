<template>
  <v-app id="inspire">

    <slot name="top"></slot>

    <v-chip
      v-if="$store.getters.isHot"
      color="green"
      class="position-absolute"
      style="top: 4px; right: 0;  z-index: 10000;"
      @click="$store.state.ambient.isHot = false"
    >
      Development Mode
    </v-chip>
    <v-app-bar app v-if="$vuetify.display.mdAndDown">
      <v-app-bar-nav-icon
        v-if="!$vuetify.display.lgAndUp"
        :icon="!$store.getters.sidebarStatus ? '$menu' : '$close'"
        @click="$toggleSidebar()">
      </v-app-bar-nav-icon>

      <v-toolbar-title>CRM</v-toolbar-title>

      <v-btn
        v-if="false"
        class="ma-2"
        :loading="loading"
        :disabled="loading"
        color="secondary"
        @click="addSidebarItem"
      >
        Add Sidebar Item
      </v-btn>

      <v-spacer></v-spacer>
      <div class="d-flex justify-end mr-4">
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

    </v-app-bar>

    <ue-sidebar
      :items="sidebarItems"
      ref="sidebar"
    >
      <template v-slot:bottom>
        <ue-impersonate-toolbar
          v-if="impersonation.active"
          v-model="showImpersonateToolbar"
          v-bind="impersonation"
        />
      </template>
    </ue-sidebar>

    <v-main>
      <slot name="main-top"></slot>

      <!--  -->
      <!-- <ue-footer :items="footerLinks" /> -->
      <div v-if="false">
        <v-breadcrumbs :items="breadcrumbs">
          <template v-slot:divider>
            <v-icon>mdi-forward</v-icon>
          </template>
        </v-breadcrumbs>
      </div>
      <slot></slot>

      <!-- Media Library -->
      <ue-modal-media
        v-if="authorization && !$lodash.isEmpty(authorization) && !authorization.isClient"
        v-model="showMediaLibrary"
        ref="mediaLibrary"
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
      <v-dialog
        ref="profileDialog"
        width="500"
        v-model="$store.state.user.profileDialog"
      >
        <template v-slot:default="{ isActive }">
          <v-card>
            <v-card-title>
              <ue-title padding="0" :text="$t('Upload Profile Image')" color="grey-darken-5" transform="none" align="center" justify="space-between">
                <template #right>
                  <v-btn icon="$close" variant="plain" color="grey-darken-5" size="default" @click="isActive.value = false"/>
                </template>
              </ue-title>
              <v-divider/>
            </v-card-title>
            <v-card-text>
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
            </v-card-text>
          </v-card>
        </template>
      </v-dialog>

      <!-- <a17-dialog
        ref="deleteWarningMediaLibrary"
        modal-title="{{ twillTrans("twill::lang.media-library.dialogs.delete.delete-media-title") }}"
        confirm-label="{{ twillTrans("twill::lang.media-library.dialogs.delete.delete-media-confirm") }}">
          <p class="modal--tiny-title"><strong>{{ twillTrans("twill::lang.media-library.dialogs.delete.delete-media-title") }}</strong></p>
          <p>{!! twillTrans("twill::lang.media-library.dialogs.delete.delete-media-desc") !!}</p>
      </a17-dialog>
      <a17-dialog ref="replaceWarningMediaLibrary" modal-title="{{ twillTrans("twill::lang.media-library.dialogs.replace.replace-media-title") }}" confirm-label="{{ twillTrans("twill::lang.media-library.dialogs.replace.replace-media-confirm") }}">
          <p class="modal--tiny-title"><strong>{{ twillTrans("twill::lang.media-library.dialogs.replace.replace-media-title") }}</strong></p>
          <p>{!! twillTrans("twill::lang.media-library.dialogs.replace.replace-media-desc") !!}</p>
      </a17-dialog> -->

      <ue-alert ref='alert'></ue-alert>
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
                <v-btn-cta  @click="closeAlertDialog">
                  {{ $t('fields.close') }}
                </v-btn-cta>
            </v-card-actions>
          </v-card>
        </template>
      </ue-modal>

      <!-- <v-layout-item
        v-if="impersonation.active"
        class="text-end pointer-events-none"
        model-value
        position="bottom"
        size="88"
      >
        <div class="ma-4">
          <v-fab-transition>
            <v-btn
              class="mt-auto pointer-events-initial"
              color="error"
              elevation="8"
              :icon="(showImpersonateToolbar ? 'mdi-chevron-down' : 'mdi-chevron-up')"
              size="large"
              @click="showImpersonateToolbar = !showImpersonateToolbar"
            />
          </v-fab-transition>
        </div>
      </v-layout-item> -->
    </v-main>
  </v-app>
</template>

<script>
  import { ALERT, CONFIG } from '@/store/mutations/index'
  import { USER } from '@/store/mutations';

  export default {
    props: {
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

        sidebarItems: this.navigation.sidebar,
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

        showMediaLibrary: false,
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
        __log('alertDialogMessage computed', this.$store.state.alert)
        return this.$store.state.alert.dialogMessage
      }
    },
    created () {
      if(this.$vuetify.display.mdAndDown){
        this.$store.commit(CONFIG.SET_SIDEBAR, false)
      }
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
      }
    }
  }
</script>
