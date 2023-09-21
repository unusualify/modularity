<template>
    <v-navigation-drawer
        v-model="$root.sidebarToggle"
        id="navigation-drawer"
        :expand-on-hover="$root.isHoverable"
        :mini-variant="$root.isMini"
        v-model:mini-variant="$root.miniStatus"
        @update:mini-variant="miniChanging"
        :width="width"
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
                :items="items">
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
          <ue-logout-modal :csrf="$root.csrf">
            <template v-slot:activator="{props}">
              <v-btn
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
            </template>
          </ue-logout-modal>

          <div class="d-flex justify-center">
            <v-btn
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
        </template>
        <!-- <div style="position:absolute; bottom: 20px; margin-left: auto; margin-right: auto; left:0; right:0; text-align:center;">

        </div> -->

    </v-navigation-drawer>
</template>

<script>
export default {
  props: {
    items: {
      type: Array,
      required: true
    },
    rating: {
      type: Number,
      default: 0
    },
    socialMediaLinks: {
      type: Array,
      default: () => {
        return [
          [
            'mdi-twitter',
            ''
          ],
          [
            'mdi-linkedin',
            ''
          ],
          [
            'mdi-facebook',
            ''
          ],
          [
            'mdi-instagram',
            ''
          ]
        ]
      }
    }

  },
  data () {
    return {
      dialog: false,
      logo: '@/sass/themes/template/main-logo.svg'

      // isMini: this.mini,
    }
  },

  created () {
  },

  beforeCreate () {
    // __log('beforeCreate mini', this.mini)
  },

  mounted () {

  },
  watch: {

  },

  computed: {
    width () {
      return this.$root.isXlAndUp ? 320 : 256
    }
  },
  methods: {
    onChange (event) {
      console.log('sidebar onChange', event.target.value)
    },
    addItem (item) {
      // console.log(this.items)
      this.items.push(item)
    },

    miniChanging (val) {
      // __log(
      //     'mini changing',
      //     val
      // )
    }
  }
}
</script>

<style lang="scss">
  // @use 'styles/themes/b2press/settings' with(
  //   $button-text-transform: 'capitalize'
  // );
</style>

<style>
    .border{
        padding-left: 12px;
        /* margin-right: 12px; */
        background: #97ffff;
        border-radius: 10%;
        text-decoration: none;
    }

    /* .v-list-item-group .v-list-item-active {
      color: grey;
    } */
</style>
