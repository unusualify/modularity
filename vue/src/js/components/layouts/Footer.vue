
<template>
  <v-footer
      dark
      padless
      v-show="display"
    >
      <v-card
        flat
        tile
        class="indigo lighten-1 white--text text-center"
      >
        <v-card-text>
          <v-btn
            v-for="(link,i) in links"
            :key="i"
            class="mx-4 white--text"
            :href="link.url"
            icon
          >
            <v-icon size="24px">
              {{ link.icon }}
            </v-icon>
          </v-btn>
        </v-card-text>

        <v-card-text class="white--text pt-0">
          Phasellus feugiat arcu sapien, et iaculis ipsum elementum sit amet. Mauris cursus commodo interdum. Praesent ut risus eget metus luctus accumsan id ultrices nunc. Sed at orci sed massa consectetur dignissim a sit amet dui. Duis commodo vitae velit et faucibus. Morbi vehicula lacinia malesuada. Nulla placerat augue vel ipsum ultrices, cursus iaculis dui sollicitudin. Vestibulum eu ipsum vel diam elementum tempor vel ut orci. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
        </v-card-text>

        <v-divider></v-divider>

        <v-card-text class="white--text">
          {{ new Date().getFullYear() }} — <strong>Vuetify</strong>
        </v-card-text>
      </v-card>
    </v-footer>
</template>

<script>
  export default {
    name: 'Footer',
    props: {
        items: {
            type: Array,
            required: true,
        },
        show: {
          type: Boolean,
          default: true
        }
    },
    data: function() {
        return {
            links: this.items,
            display: this.show
        }
    },
    // data() {
    //     return {
    //         links: this.items
    //     }
    // },
    // data: () => ({
    //   links: this.items // not working. this not return object because of arrow functions

    // }),

    created(){

    }
  }
</script>