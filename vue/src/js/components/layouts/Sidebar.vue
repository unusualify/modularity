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
        prepend-avatar="https://randomuser.me/api/portraits/women/85.jpg"
        subtitle="info@b2press.com"
        title="B2Press"
        class="ue-sidebar__info-item"
      >
        <template v-slot:prepend>
          <v-avatar class="ue-sidebar__avatar" color="primary">
            <ue-svg-icon class="ue-sidebar__logo" symbol="main-logo-dark"></ue-svg-icon>
          </v-avatar>
        </template>
      </v-list-item>

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
          :title="$store.getters.currentUser.name"
          :subtitle="$store.getters.currentUser.email"
          class="ue-sidebar__info-item"
        >
          <template v-slot:prependxxxx>
            <v-avatar class="" color="primary">
              <ue-svg-icon class="ue-sidebar__logo" symbol="main-logo-dark"></ue-svg-icon>
            </v-avatar>
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
};
</script>

<style lang="sass">

</style>

<style>

</style>
