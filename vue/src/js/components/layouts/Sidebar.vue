<template>
  <!-- <v-navigation-drawer
    @update:rail="methods.handleExpanding($event)"
    :location="mainLocation"
  > -->
  <v-navigation-drawer
    ref="navigationDrawer"
    id="navigation-drawer"
    v-model="status"
    :rail="rail"
    :expand-on-hover="isHoverable"
    :location="options.location"
    :railWidth="options.railWidth"
    :persistent="options.persistent ?? false"
    :width="width"
  >
    <!-- <v-avatar class="d-block text-center mx-auto mt-2">
      <v-icon color="green darken-2" large icon="fa:fab fa-atlassian"/>
    </v-avatar> -->

    <!-- <ue-svg-icon class="ue-sidebar__logo" symbol="main-logo-light"></ue-svg-icon> -->
    <template v-slot:prepend>
      <v-list class="ue-sidebar__info">
        <v-list-item
          v-if="!$store.getters.isSuperAdmin"
          prepend-avatar="https://randomuser.me/api/portraits/women/85.jpg"
          :subtitle="$store.getters.appEmail"
          :title="$store.getters.appName"
          class="ue-sidebar__info-item"
        >
          <template v-slot:prepend>
            <v-avatar class="ue-sidebar__avatar" color="primary">
              <ue-svg-icon class="ue-sidebar__logo" symbol="main-logo-dark"></ue-svg-icon>
            </v-avatar>
          </template>
        </v-list-item>
        <div v-else class="mx-3 text-subtitle-2">
          <div v-for="(version, key) in $store.getters.versions" :key="key" class="d-flex align-center my-1">
            <div class="flex-grow-1">{{ $headline(key) }}:</div>
            <div class="flex-grow-0 ml-1 font-weight-bold">{{ version }}</div>
          </div>
          <div v-if="$store.getters.isSuperAdmin" v-for="key in ['appName', 'appEnv', 'appDebug']" :key="key" class="d-flex align-center my-1">
            <div class="flex-grow-1">{{ key === 'appDebug' ? 'Debug Mode' : $headline(key) }}:</div>
            <div class="flex-grow-0 ml-1 font-weight-bold">{{ key === 'appDebug' ? $store.getters[key] ? 'Active' : 'Inactive' : $store.getters[key] }}</div>
          </div>
        </div>
      </v-list>
      <div class="d-flex align-center position-relatie" style="">
        <v-divider class="flex-grow-1"></v-divider>
        <v-btn
          v-if="!hasRail && $vuetify.display.lgAndUp"
          icon
          color="orange"
          @click="railManual = !railManual"
          class="sidebar-toggle-btn"
          size="small"
          style="position: absolute; right: -20px; z-index: 9999;"
        >
          <v-icon>{{ rail ? 'mdi-chevron-right' : 'mdi-chevron-left' }}</v-icon>
        </v-btn>
      </div>
    </template>

    <ue-navigation-group :items="items" :hideIcons="hideIcons" :showTooltip="rail && !isHoverable" id="ue-sidebar__menu">
    </ue-navigation-group>

    <template v-slot:append>
      <template v-if="!$store.getters.isGuest || true">
        <v-divider></v-divider>
        <v-list class="">
          <v-list-item
            prepend-avatar="https://randomuser.me/api/portraits/women/85.jpg"
            :title="$store.getters.userProfile.name"
            :subtitle="$store.getters.userProfile.email"
            class="ue-sidebar__info-item"
          >
            <template v-slot:prepend="prependScope">
              <v-avatar :image="$store.getters.userProfile.avatar_url"
                @click="$openProfileDialog"/>
            </template>
            <template v-slot:append>
              <v-btn
                @click="profileMenuOpen = !profileMenuOpen"
                :icon="profileMenuOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'"
                size="small"
                variant="text"
              ></v-btn>
            </template>
          </v-list-item>

          <v-expand-transition>
            <ue-navigation-group
              v-if="profileMenuOpen"
              :items="profileMenu"
              :profileMenu="true"
              @activateMenu="handleMenu($event)"
            >
            </ue-navigation-group>
          </v-expand-transition>

          <ue-logout-modal :csrf="$csrf()">
            <template v-slot:activator="{ props }">
              <v-tooltip text="Logout" location="top" :disabled="!(rail && !isHoverable)">
                <template v-slot:activator="tooltipActivator">
                  <div v-bind="tooltipActivator.props">
                    <v-list-item prepend-icon="mdi-logout" v-bind="props" :disabled="$store.getters.isGuest">
                      {{ $t("authentication.logout") }}
                    </v-list-item>
                  </div>
                </template>

              </v-tooltip>
            </template>
          </ue-logout-modal>

          <!-- About Dialog -->
          <v-dialog ref="aboutDialog" max-width="500" v-if="$store.getters.versions && !$store.getters.isGuest && !$store.getters.isClient && !$store.getters.isSuperAdmin">
            <template v-slot:activator="{ props: activatorProps }">
              <v-list-item prepend-icon="mdi-information" v-bind="activatorProps">
                {{ $t("About") }}
              </v-list-item>
            </template>

            <template v-slot:default="{ isActive }">
              <v-card :title="$t('About')">
                <v-card-text>
                  <div v-for="(version, key) in $store.getters.versions" :key="key" class="d-flex align-center my-1">
                    {{ $headline(key) }}:
                    <v-chip variant="outlined" color="primary" class="ml-2">
                      {{ version }}
                    </v-chip>
                  </div>
                  <div v-if="$store.getters.isSuperAdmin" v-for="key in ['appName', 'appEnv', 'appDebug']" :key="key" class="d-flex align-center my-1">
                    {{ key === 'appDebug' ? 'Debug Mode' : $headline(key) }}:
                    <v-chip variant="outlined" :color="key === 'appDebug' ? $store.getters[key] ? 'success' : 'error' : 'primary'" class="ml-2">
                      {{ key === 'appDebug' ? $store.getters[key] ? 'Active' : 'Inactive' : $store.getters[key] }}
                    </v-chip>
                  </div>
                  <!-- Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. -->
                </v-card-text>

                <v-divider></v-divider>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn
                    variant="outlined"
                    :text="$t('Close')"
                    @click="isActive.value = false"
                  ></v-btn>
                </v-card-actions>
              </v-card>
            </template>
          </v-dialog>

          <!-- Bottom Slot -->
          <slot name="bottom"> </slot>
        </v-list>
      </template>
    </template>

    <!-- <template v-slot:append>

    </template> -->
  </v-navigation-drawer>
  <v-navigation-drawer
    v-if="options.contentDrawer.exists"
    :width="width"
    :location="options.location"
    style="max-width: 15%"
  />
  <v-navigation-drawer
    v-if="secondaryOptions.exists"
    :location="secondaryOptions.location"
    :width="width"
  />
</template>

<script>
  import { computed } from 'vue';
  import { useGoTo } from 'vuetify'
  import { useSidebar } from '@/hooks';
  import { USER } from '@/store/mutations';

  export default {
    provide() {
      return {
        activeMenu: computed(() => this.activeMenu)
      }
    },
    setup() {
      const goTo = useGoTo()

      return {
        ...useSidebar(),
        goTo
      }
    },
    props: {
      items: {
        type: Array,
        required: true,
      },
      rating: {
        type: Number,
        default: 0,
      },
    },
    data() {
      return {
        dialog: false,
        logo: "@/sass/themes/template/main-logo.svg",
        // isExpanded: true,
        profileMenuOpen: false,
      };
    },
    mounted() {
      try {
        const activeItems = window.$('.sidebar-item-active')
        const el = activeItems[activeItems.length - 1]
        this.goTo(el, {
          container: '.v-navigation-drawer__content',
          duration: 200,
          offset: -200,
          easing: 'easeInOutQuad',
        })
      } catch (e) {
        console.log(e)
      }
    },
    methods: {
      profileFormSubmitted(res) {

        if (typeof URLS !== 'undefined' && URLS) {
          axios.get(URLS.profileShow).then(res => {
            this.$store.commit(USER.SET_PROFILE_DATA, res.data)
          })
        }
      }
    }
  };
</script>

<style lang="sass">

</style>

<style>

</style>
