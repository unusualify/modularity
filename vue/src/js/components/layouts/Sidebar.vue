<template>
  <v-navigation-drawer
    v-model="sideBar.sidebarToggle.value"
    id="navigation-drawer"
    :expand-on-hover="sideBar.isHoverable.value"
    :mini-variant="sideBar.isMini.value"
    v-model:mini-variant="sideBar.isMini.value"
    :rail="sideBar.rail.value"
    :width="sideBar.width.value"
    @update:rail="sideBar.methods.handleExpanding($event)"
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
      :expanded="sideBar.expanded.value"
      :showIcon="sideBar.showIcon.value"
      >
    </ue-list-group>

    <!-- <template v-slot:append>
        <div class="d-flex justify-content-center pa-2">
            <v-btn class="text-none" stacked variant="plain">
                <v-badge bordered overlap color="green">
                    <v-avatar size="50">
                        <v-img src="https://cdn.vuetifyjs.com/images/lists/2.jpg"/>
                    </v-avatar>
                </v-badge>
            </v-btn>
        </div>
        <div class="d-flex justify-content-center pa-2">
            <ue-logout-modal :csrf="$root.csrf" />
        </div>
    </template> -->
    <template v-slot:append>
      <ue-logout-modal :csrf="sideBar.csrf.value">
        <template v-slot:activator="{props}">
          <v-btn
              v-if="sideBar.expanded.value"
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
          <v-list-item
          v-else
          prepend-icon="mdi-power"
          >

          </v-list-item>

        </template>
      </ue-logout-modal>

      <div class="d-flex justify-center">
        <v-btn
          v-if="sideBar.expanded.value"
          v-for="[_icon, _link] in sideBar.socialMediaLinks.value"
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
    </template>
    <!-- <div style="position:absolute; bottom: 20px; margin-left: auto; margin-right: auto; left:0; right:0; text-align:center;">

    </div> -->

  </v-navigation-drawer>
</template>

<script>
import { inject } from 'vue'

export default {
  setup(){
    const sideBar = inject('hooks')
    return {
      sideBar,
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
