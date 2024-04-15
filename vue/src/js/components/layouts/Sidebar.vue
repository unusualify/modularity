<template>

  <v-navigation-drawer
    v-model="sidebarToggle"
    id="navigation-drawer"
    :expand-on-hover="isHoverable"
    :mini-variant="isMini"
    v-model:mini-variant="isMini"
    :rail="rail"
    :width="width"
    @update:rail="methods.handleExpanding($event)"
    :location="mainLocation"
    >

    <!-- <v-avatar class="d-block text-center mx-auto mt-2">
      <v-icon color="green darken-2" large icon="fa:fab fa-atlassian"/>
    </v-avatar> -->

    <template v-slot:prepend>
      <div>
        <span v-svg symbol="main-logo"></span>
      </div>
    </template>

    <v-divider class=""></v-divider>

    <!-- <ue-list-element>

    </ue-list-element> -->

    <ue-list-group
      :items="items"
      :expanded="expanded"
      :showIcon="showIcon"
      >
    </ue-list-group>

    <template v-slot:append>
      <slot name="profileMenu">
      <v-list v-model:opened="open" v-if="profileMenu.length" bg-color="grey-lighten-3">
        <v-list-group value="User" expand-icon="mdi-menu-up" collapse-icon="mdi-menu-down" >
          <!-- prepend-avatar="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRKjNTobklLhC-OZ7sH94RPOZj2jtkS4KWfv9Q7z8von0qzIKe3kgUepfs7kpyI2Gnp0rQ&usqp=CAU" -->
          <template v-slot:activator="{props}">
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
          :showIcon="showIcon"
          :expanded="expanded"
          :profileMenu="true"
          >
          </ue-list-group>
          <!-- <v-list-item
          nav
          prepend-icon="mdi-cog"
          append-icon="mdi-menu-right"
          id="Settings"
          title="Settings"
          @click="methods.handleMenu('Settings')"></v-list-item>
          <v-menu v-bind:activator="activeMenu" location="end">
              <v-list>
                <v-list-item
                  v-for="(message, index) in messages['Settings']"
                  :key="index"
                  href="https://www.facebook.com"
                >
                  <v-list-item-title>{{message}}</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu> -->

          <ue-logout-modal :csrf="csrf">
            <template v-slot:activator="{props}">
              <v-list-item
              prepend-icon="mdi-logout"
              v-bind="props"
              >
              {{ $t('authentication.logout') }}
              </v-list-item>
            </template>
          </ue-logout-modal>

        </v-list-group>
      </v-list>
      <!-- DEFAULT IF NO PROFILE MENU -->
      <div v-else>
        <ue-logout-modal :csrf="csrf">
        <template v-slot:activator="{props}">
          <v-btn
              v-if="expanded"
              class="v-button--logout my-3"
              variant="plain"
              v-bind="props"
              color="white"
              prepend-icon="mdi-power"
              >
<!-- <template v-slot:prepend>
  <v-icon color="success"></v-icon>
</template> -->
              {{$t('authentication.logout')}}
          </v-btn>
          <v-btn v-else
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
        <slot name="bottom">

        </slot>
      </div>
      </div>

    </slot>
    </template>

    <!-- <template v-slot:append>

    </template> -->
  </v-navigation-drawer>
  <v-navigation-drawer v-if="contentDrawer"
  :width="width"
  :location="mainLocation"
  style="max-width: 15%;"
  >
  </v-navigation-drawer>
  <v-navigation-drawer v-if="secondarySidebarExists"
  :location="secondaryLocation"
  :width="width"
  >
  </v-navigation-drawer>
</template>

<script>
import { inject } from 'vue'

export default {
  setup(){
    const sideBar = inject('hooks')
    return {
      ...sideBar
    }
  },
  props: {
    items: {
      type: Array,
      required: true
    },
    rating: {
      type: Number,
      default: 0
    },

  },
  data () {
    return {
      dialog: false,
      logo: '@/sass/themes/template/main-logo.svg',
      isExpanded: true,
    }
  },
}
</script>

<style lang="sass">
  // @use 'styles/themes/b2press/settings' with(
  //   $button-text-transform: 'capitalize'
  // );
</style>

<style>

    /* .v-list-item-group .v-list-item-active {
      color: grey;
    } */
</style>
