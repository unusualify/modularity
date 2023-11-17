<template>
    <v-list
        v-model="activeIndex"
        v-model:opened="opened"
        :style="style"
        :active-class="`sidebar-item-active sidebar-item-active-${level}`"
        class="mb-2 py-0"
        >
        <template
            v-for="(item, i) in items"
            >
            <v-list-group
                v-if="isSubgroup(item)"
                :key="i + 'subgroup'"
                :index="i"
                :prepend-icon="item.icon"
                :ripple="true"
                :sub-group="false"
                :active-class="`sidebar-item-active sidebar-item-active-${level}`"
                :value="item.name"
                >
                <template v-slot:activator="{ props }">
                    <v-list-item
                        v-bind="props"
                        :title="item.name"
                        :prepend-icon="item.icon"
                    ></v-list-item>
                </template>
                <ue-list-group
                  :items="item.items"
                  :level="level+1"
                />
            </v-list-group>

            <v-list-item
                v-else-if="isRoute(item)"
                :key="i + 'route'"
                :index="i"
                :ripple="false"
                :href="item.route"
                :append="false"
                :prepend-icon="item.icon"
                :title="item.name"
                :active="activeIndex === i"
                :active-class="`sidebar-item-active sidebar-item-active-${level}`"
                v-bind="$bindAttributes(item)"
                >
            </v-list-item>

            <v-list-item
                v-else-if="isEvent(item)"
                :key="i + 'event'"
                :index="i"
                :ripple="false"
                :append="false"
                @click="$root.handleVmFunctionCall(item.event)"
                :prepend-icon="item.icon"
                :title="item.name"
                :active="activeIndex === i"
                :active-class="`sidebar-item-active sidebar-item-active-${level}`"
                >

                <!-- <v-list-item-icon v-if="!!item.icon">
                    <v-icon> {{item.icon}} </v-icon>
                </v-list-item-icon>

                <v-list-item-content>
                    <v-list-item-title v-text="item.name"></v-list-item-title>
                </v-list-item-content> -->

            </v-list-item>

            <template
                v-else-if="isHeader(item)"
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
                    :prepend-icon="item.icon"
                    :title="item.name"
                    >

                    <!-- <v-list-item-icon v-if="!!item.icon">
                        <v-icon> {{item.icon}} </v-icon>
                    </v-list-item-icon>

                    <v-list-item-content>
                        <v-list-item-title v-text="item.name"></v-list-item-title>
                    </v-list-item-content> -->
                </v-list-item>
            </template>
        </template>
    </v-list>
</template>

<script>
import { toRef } from 'vue'

export default {
  setup (props, context) {
    // const items = toRef(props, 'items')
    // const opened = getListGroupOpens([], items.value)
    // const opened = []

    const getListGroupOpens = (matches, items) => {
      if (!Array.isArray(items)) return matches

      items.forEach(function (i) {
        if (__isset(i.is_active) && i.is_active && __isset(i.items)) {
          matches.push(i.name)
          getListGroupOpens(matches, i.items)
        }
      })

      return matches
    }

    return {
      // opened,
      getListGroupOpens
    }
  },
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
  data: () => ({
    opened: []
  }),
  computed: {
    // opened: {
    //   get () {
    //     const matches = []
    //     this.getListGroupOpens(matches, this.items)

    //     __log('opened getter', matches)
    //     return matches
    //   },
    //   set (val) {
    //     // __log('opened setter', val)
    //     return val
    //   }

    // },

    activeIndex: {
      get () {
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
    this.opened = this.getListGroupOpens([], this.items)
  },
  methods: {
    isSubgroup (item) {
      return !!item.items
    },
    isRoute (item) {
      return !item.items && !!item.route
    },
    isEvent (item) {
      return !!item.attr
    },
    isHeader (item) {
      return !item.route && !item.items && !!item.name
    }
  }
}
</script>

<style>
    /* .sidebar-item-active{
        background: #11758D;
        text-decoration: none;
    } */
    /* .sidebar-item-active-0{
        background: #6ff4f4;
        border-radius: 10%;
    }
    .sidebar-item-active-1{
        background: #97ffff;
        border-radius: 0%;
    }
    .sidebar-item-active-2{
        background: #a9ffff;
        border-radius: 0%;
    } */
</style>
