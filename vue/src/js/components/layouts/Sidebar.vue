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
      <!-- <v-list-item
        v-else
      >
      </v-list-item> -->

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

    <ue-navigation-group :items="items" :hideIcons="hideIcons" :showTooltip="rail && !isHoverable" class="ue-sidebar__menu">
    </ue-navigation-group>

    <template v-slot:append>
      <v-divider></v-divider>
      <v-list class="">
        <v-list-item
          prepend-avatar="https://randomuser.me/api/portraits/women/85.jpg"
          :title="$store.getters.userProfile.name"
          :subtitle="$store.getters.userProfile.email"
          class="ue-sidebar__info-item"
        >
          <template v-slot:prepend="prependScope">
            <v-dialog ref="profileDialog" width="500">
              <template v-slot:activator="{ props }">
                <v-avatar v-bind="props" :image="$store.getters.userProfile.avatar_url"/>
              </template>
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
                  <v-list-item prepend-icon="mdi-logout" v-bind="props">
                    {{ $t("authentication.logout") }}
                  </v-list-item>
                </div>
              </template>

            </v-tooltip>
          </template>
        </ue-logout-modal>

        <!-- About Dialog -->
        <v-dialog ref="aboutDialog" max-width="500" v-if="$store.getters.versions && !$store.getters.isClient && !$store.getters.isSuperAdmin">
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
      <!-- <slot name="profileMenu">
        <v-list
          v-if="profileMenu.length"
          v-model:opened="open"
          bg-color="grey-lighten-3"
        >
          <v-list-group
            value="User"
            expand-icon="mdi-menu-up"
            collapse-icon="mdi-menu-down"
          >
            <template v-slot:activator="{ props }">
              <v-list-item
                v-bind="props"
                :title="currentUser.name"
                :subtitle="currentUser.email"
                lines="one"
                @mouseenter="methods.handleProfile"
              >
              </v-list-item>
            </template>
            <ue-list-group
              :items="profileMenu"
              :hideIcons="hideIcons"
              :expanded="expanded"
              :profileMenu="true"
            >
            </ue-list-group>

            <ue-logout-modal :csrf="csrf">
              <template v-slot:activator="{ props }">
                <v-list-item prepend-icon="mdi-logout" v-bind="props">
                  {{ $t("authentication.logout") }}
                </v-list-item>
              </template>
            </ue-logout-modal>
          </v-list-group>
          <div>
            <slot name="bottom"> </slot>
          </div>
        </v-list>
        <div v-else>
          <ue-logout-modal :csrf="csrf">
            <template v-slot:activator="{ props }">
              <v-btn
                v-if="expanded"
                class="v-button--logout my-3"
                variant="plain"
                v-bind="props"
                color="white"
                prepend-icon="mdi-power"
              >
                {{ $t("authentication.logout") }}
              </v-btn>
              <v-btn
                v-else
                variant="plain"
                v-bind="props"
                icon="mdi-power"
                color="white"
                class="px-6"
              >
              </v-btn>
            </template>
          </ue-logout-modal>

          <div class="d-flex justify-center">
            <v-btn
              v-if="expanded"
              v-for="[_icon, _link] in socialMediaLinks"
              class="ma-1"
              :key="_icon"
              :icon="_icon"
              :href="_link"
              target="_blank"
              color="white"
              size="x-small"
            >
              <v-icon size="medium" color="primary"></v-icon>
            </v-btn>
          </div>
          <div>
            <slot name="bottom"> </slot>
          </div>
        </div>
      </slot> -->
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
import { useSidebar } from '@/hooks';
import { USER } from '@/store/mutations';
export default {
  provide() {
    return {
      activeMenu: computed(() => this.activeMenu)
    }
  },
  setup() {
    return {
      ...useSidebar()
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
