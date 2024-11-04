<template>
  <v-list
    v-model="activeIndex"
    v-model:opened="opened"
    :style="style"
    :active-class="`sidebar-item-active sidebar-item-active-${level}`"
    class="mb-2 pl-0"
    nav
  >
    <template v-for="(item, i) in items" :key="i">
      <v-list-group v-if="isSubgroup(item)"
        :key="i + 'subgroup'"
        :index="i"

        :ripple="true"
        :prepend-icon="item.icon"

        :sub-group="false"
        :active-class="`sidebar-item-active sidebar-item-active-${level}`"
        :value="item.name"
      >
        <template v-slot:activator="{ props }">
          <v-tooltip bottom :disabled="!showTooltip">
            <template v-slot:activator="tooltipActivator">
              <div v-bind="tooltipActivator.props">
                <v-list-item
                  v-bind="props"
                  :title="item.name"
                  :prepend-icon="!hideIcons ? item.icon : null"
                  nav
                ></v-list-item>
              </div>
            </template>
            <span>{{ item.name }}</span>
          </v-tooltip>
        </template>
        <ue-navigation-group :items="item.items" :level="level + 1" :hideIcons="hideIcons" :show-tooltip="showTooltip"/>
      </v-list-group>

      <template v-else-if="isMenu(item)">
        <v-list-item

          nav
          :title="item.name"
          :prepend-icon="item.icon"

          append-icon="mdi-menu-right"
          density="compact"
          :id="item.menuActivator"
          :slim="true"

          @click="$emit('activateMenu', item.menuActivator)"
          >
          <!-- @click="sideBar.methods.handleMenu(item.menuActivator)" -->
        </v-list-item>
        <v-menu
          v-if="activeMenu === `#${item.menuActivator}`"
          location="end"
          :activator="activeMenu"
        >
          <v-list>
            <ue-navigation-group :items="Object.values(item.menuItems)" :show-tooltip="showTooltip">
            </ue-navigation-group>
          </v-list>
        </v-menu>
      </template>

      <!-- Handle other components -->
      <v-tooltip v-else bottom :disabled="!showTooltip">
        <template v-slot:activator="{ props }">
          <div v-bind="props">
            <component
              :is="getComponentType(item)"
              v-bind="getComponentProps(item, i)"
            />
          </div>
        </template>
        <span>{{ item.name }}</span>
      </v-tooltip>
      <!-- <component
        v-else
        :is="getComponentType(item)"
        v-bind="getComponentProps(item, i)"
      /> -->
      <!-- <template v-else-if="isHeader(item)">
        <v-divider v-if="i != 0" :key="i + 'subdivider'" :index="i"></v-divider>
        <v-list-item
          :key="i + 'subheader'"
          :index="i"

          nav
          :ripple="false"
          :append="false"
          :prepend-icon="!hideIcons ? item.icon : null"

          disabled
          :inactive="true"
          >
        </v-list-item>
      </template> -->
    </template>
  </v-list>
</template>

<script>
import { inject } from "vue";

export default {
  name: "ue-navigation-group",
  inject: ['activeMenu'],
  props: {
    items: {
      type: Array,
      required: true,
    },
    level: {
      type: Number,
      default: 0,
    },
    hideIcons: {
      type: Boolean,
      default: false,
    },
    showTooltip: {
      type: Boolean,
      default: false,
    },
    profileMenu: {
      type: Boolean,
      default: false,
    },
  },
  setup(props, context) {

    const getListGroupOpens = (matches, items) => {
      if (!Array.isArray(items)) return matches;

      items.forEach(function (i) {
        if (__isset(i.is_active) && i.is_active && __isset(i.items)) {
          matches.push(i.name);
          getListGroupOpens(matches, i.items);
        }
      });

      return matches;
    };

    return {
      // opened,
      getListGroupOpens,
    };
  },
  data: () => ({
    opened: [],
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
      get() {
        try {
          return this.items.findIndex((item) => item.is_active == 1);
        } catch (error) {
          return 0;
        }
      },
      set(value) {
        // __log('setter selectIndex', value)
      },
    },

    style() {
      return {
        marginLeft: "0rem",
      };
    },
  },

  created() {
    this.opened = this.getListGroupOpens([], this.items);
  },
  watch: {},
  methods: {
    getComponentType(item) {
      if (this.isSubgroup(item)) return 'v-list-group';
      if (this.isMenu(item)) return 'v-menu-wrapper';
      return 'v-list-item';
    },
    getComponentProps(item, index) {
      const baseProps = {
        index,

        // 'active-class': `sidebar-item-active sidebar-item-active-${this.level}`,
      };

      if (this.isSubgroup(item)) {
        return {
          ...baseProps,
          'prepend-icon': item.icon,
          value: item.name,
          items: item.items,
          level: this.level + 1,
        };
      }

      if (this.isMenu(item)) {
        return {
          ...baseProps,
          item,
          hideIcons: this.hideIcons,
        };
      }

      return {
        ...baseProps,
        ...this.getListItemProps(item, index),
      };
    },
    getListItemProps(item, index) {
      const props = {
        nav: true,
        active: this.activeIndex === index,
        ripple: false,
        append: false,
        title: item.name,
        'prepend-icon': !this.hideIcons ? item.icon : null,
      };

      if (this.isRoute(item)) {
        props.href = item.route || item.href;
        Object.assign(props, this.$bindAttributes(item));
      } else if (this.isEvent(item)) {
        props.onClick = () => this.$call(item.event);
      } else if (this.isMenuRoute(item)) {
        props.density = 'compact';
        props.href = item.route || item.href;
        props.slim = true;
        props.class = { 'px-4': !this.hideIcons, 'ml-2': true };
        Object.assign(props, this.$bindAttributes(item));
      } else if (this.isHeader(item)) {
        props.inactive = true;
        props.disabled = true;
      }

      return props;
    },
    getListGroupOpens(matches, items) {
      if (!Array.isArray(items)) return matches;

      items.forEach((i) => {
        if (i.is_active && i.items) {
          matches.push(i.name);
          this.getListGroupOpens(matches, i.items);
        }
      });

      return matches;
    },
    isSubgroup: (item) => !!item.items,
    isRoute (item) { return !item.items && (!!item.route || !!item.href) && !this.profileMenu},
    isEvent: (item) => !!item.attr,
    isHeader: (item) => !item.href && !item.route && !item.items && !!item.name,
    isMenu: (item) => !!item.menuItems,
    isMenuRoute(item){ return !item.menuItems && this.profileMenu && (!!item.route || !!item.href)}
  },
};
</script>

<style scoped>
</style>
