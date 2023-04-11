<template>
    <v-navigation-drawer
        v-model="$root.sidebarToggle"
        app
        color="white"

        :expand-on-hover="$root.isHoverable"
        :mini-variant="$root.isMini"
        v-model:mini-variant="$root.miniStatus"
        @update:mini-variant="miniChanging"
        >

        <v-avatar class="d-block text-center mx-auto mt-2">
            <v-icon color="green darken-2" large icon="fa:fab fa-atlassian"/>
        </v-avatar>

        <v-divider class="mx-3"></v-divider>

        <!-- <ue-list-element>

        </ue-list-element> -->

        <v-list dense class="mt-5 d" nav shaped>
            <ue-list-group
                :items="items"
                >
            </ue-list-group>
        </v-list>

        <template v-slot:append>
            <div class="d-flex justify-content-center pa-2">
                <v-btn class="text-none" stacked variant="plain">
                    <v-badge bordered overlap color="green">
                        <!-- <v-icon>far fa-belief </v-icon> -->
                        <v-avatar size="50">
                            <v-img src="https://cdn.vuetifyjs.com/images/lists/2.jpg"/>
                        </v-avatar>
                    </v-badge>
                </v-btn>
            </div>
            <div class="d-flex justify-content-center pa-2">
                <ue-logout-dialog :csrf="$root.csrf" />
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
    }
  },
  data () {
    return {
      dialog: false

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

<style>
    .border{
        padding-left: 12px;
        /* margin-right: 12px; */
        background: #97ffff;
        border-radius: 10%;
        text-decoration: none;
    }

    .v-list-item-group .v-list-item-active {
        color: grey;
    }
</style>
