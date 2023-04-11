<template>
    <v-list
        v-model="activeIndex"
        :style="style"
        :active-class="`sidebar-item-active sidebar-item-active-${level}`"
        class="mb-2"
        >

        <template
            v-for="(item, i) in items"
            >

            <v-list-group
                v-if="isSubgroup(item)"
                :key="i + 'subgroup'"
                :index="i"
                :prepend-icon="item.icon"
                :value="item.is_active"
                :ripple="true"
                :sub-group="false"
                :active-class="`sidebar-item-active sidebar-item-active-${level}`"
                >
                <template v-slot:activator="{ props }">
                    <v-list-item
                        v-bind="props"
                        :title="item.text"
                        :icon="item.icon"
                    ></v-list-item>

                </template>
                <ue-list-group
                    :items="item.items"
                    :level="level+1"
                />
            </v-list-group>

            <v-list-item
                v-else-if="isLink(item)"
                :key="i + 'link'"
                :index="i"
                :ripple="false"
                :href="item.link"
                :append="false"
                :icon="item.icon"
                :title="item.text"
                >
            </v-list-item>

            <v-list-item
                v-else-if="isEvent(item)"
                :key="i + 'event'"
                :index="i"
                :ripple="false"
                :append="false"
                @click="$root.handleVmFunctionCall(item.event)"
                :icon="item.icon"
                :title="item.text"
                >

                <!-- <v-list-item-icon v-if="!!item.icon">
                    <v-icon> {{item.icon}} </v-icon>
                </v-list-item-icon>

                <v-list-item-content>
                    <v-list-item-title v-text="item.text"></v-list-item-title>
                </v-list-item-content> -->

            </v-list-item>

            <template
                v-else
                >
                <v-divider
                    v-if="i != 0"
                    :key="i + 'subdivider'"
                    :index="i"
                ></v-divider>

                <v-list-item
                    :key="i + 'subheader'"
                    :index="i"
                    :ripple="false"
                    :inactive="true"
                    :append="false"
                    disabled
                    :icon="item.icon"
                    :title="item.text"
                    >

                    <!-- <v-list-item-icon v-if="!!item.icon">
                        <v-icon> {{item.icon}} </v-icon>
                    </v-list-item-icon>

                    <v-list-item-content>
                        <v-list-item-title v-text="item.text"></v-list-item-title>
                    </v-list-item-content> -->
                </v-list-item>
            </template>
        </template>

    </v-list>
</template>

<script>
export default {
  name: 'ue-list-group',
  props: {
    items: {
      type: Array,
      required: true
    },
    level: {
      type: Number,
      default: 0
    }
  },
  data: function () {
    return {
      activeBackgroundColorLevels: [
        '#97ffff', // light blue
        '#7cffb9' // light green
      ]
    }
  },

  computed: {
    activeIndex: {
      get () {
        // __log(
        //     'getter selectIndex',
        //     this.items,
        //     this.items.find(item => item.is_active == 1 ),
        //     this.items.findIndex(item => item.is_active == 1 )
        // )
        return this.items.findIndex(item => item.is_active == 1)
      },
      set (value) {
        // __log('setter selectIndex', value)

      }
    },

    style () {
      // __log(
      //     'listGroup Style',
      //     this.$root.miniStatus,
      //     this.$root.isMini
      // )
      return {
        marginLeft: ((!this.$root.miniStatus || !this.$root.isMini) ? this.level * 20 : 0) + 'px'
      }
    }

  },

  created () {

  },

  methods: {
    isSubgroup (item) {
      return !!item.items
    },
    isLink (item) {
      return !item.items && !!item.link
    },
    isEvent (item) {
      return !!item.attr
    },
    isHeader (item) {
      return !item.link && !item.items
    }
  }

}
</script>

<style>
    .v-list-item-group .v-list-item-active {
        color: grey;
    }
    .sidebar-item-active{
        background: #ddd;
        text-decoration: none;
    }
    .sidebar-item-active-0{
        background: #6ff4f4;
        border-radius: 10%;
    }
    .sidebar-item-active-1{
        /* background: #7cffb9; */
        background: #97ffff;
        border-radius: 0%;
    }
    .sidebar-item-active-2{
        background: #a9ffff;
        border-radius: 0%;
    }

</style>
