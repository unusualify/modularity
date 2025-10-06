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
                >
                  <template v-if="item.badge" v-slot:prepend="prependScope">
                    <v-badge color="warning" v-bind="item.badgeProps ?? {}" :content="formatBadgeContent(item.badge)">
                      <v-icon :icon="item.icon"></v-icon>
                    </v-badge>
                  </template>
                </v-list-item>
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
          :data-level="level"

          @click="$emit('activateMenu', item.menuActivator)"
          >
          <template v-if="item.badge" v-slot:prepend="prependScope">
            <v-badge color="warning" v-bind="item.badgeProps ?? {}" :content="formatBadgeContent(item.badge)">
              <v-icon :icon="item.icon"></v-icon>
            </v-badge>
          </template>
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
          <component
            :is="isInertiaLink(item) ? 'ue-link' : 'div'"
            v-bind="{
              ...props,
              ...(isInertiaLink(item)
                ? {
                  href: item.route || item.href,
                  class: 'ue-inertia-anchor'
                }
                : {}
              ),
            }"
            :class="[
              !isInertiaLink(item) ? 'ue-inertia-anchor' : '',
            ]"
            >
            <component
              :is="getComponentType(item)"
              v-bind="getComponentProps(item, i)"
              :data-level="level"
              class="ue-navigation-link-item"
            >
              <template v-if="item.badge" v-slot:prepend="prependScope">
                <v-badge color="warning" v-bind="item.badgeProps ?? {}" :content="formatBadgeContent(item.badge)">
                  <v-icon v-bind="item.iconProps ?? {}" :icon="item.icon"></v-icon>
                </v-badge>
              </template>
            </component>
          </component>
        </template>
        <span>{{ item.name }}</span>
      </v-tooltip>
    </template>
  </v-list>
</template>

<script>
import { isSamePath, getPath } from '@/utils/pushState'

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
    activeIndex: {
      get() {
        try {
          return this.items.findIndex((item) => item.is_active == 1);
        } catch (error) {
          return 0;
        }
      },
      set(value) {
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
      // if (this.isInApp(item)) return 'ue-link';
      return 'v-list-item';
      // return 'ue-link';
    },
    getComponentProps(item, index) {
      const baseProps = {
        index,
        // 'active-class': `sidebar-item-active sidebar-item-active-${this.level}`,
      };

      if (this.isSubgroup(item)) {
        return {
          ...(item.props ?? {}),
          'prepend-icon': item.icon,
          value: item.name,
          items: item.items,
          level: this.level + 1,
          ...baseProps,
        };
      }

      if (this.isMenu(item)) {
        return {
          item,
          ...(item.props ?? {}),
          hideIcons: this.hideIcons,
          ...baseProps,
        };
      }

      return {
        ...baseProps,
        ...this.getListItemProps(item, index),
      };
    },
    getListItemProps(item, index) {
      const isActive = this.isInertiaLink(item)
        ? isSamePath(this.$page.url, getPath(item.route || item.href))
        : this.activeIndex === index

      const props = {
        nav: true,
        active: isActive,
        ripple: false,
        append: false,
        title: item.name,
        'prepend-icon': !this.hideIcons ? item.icon : null,
      };

      if (this.isRoute(item)) {
        if (!this.isInertiaLink(item)) {
          props.href = item.route || item.href;
        }else{
          props.class = 'ue-inertia-link';
        }
        Object.assign(props, this.$bindAttributes(item));
      } else if (this.isEvent(item)) {
        props.onClick = () => {
          this.$call(item.event);
        }
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
    isMenuRoute(item){ return !item.menuItems && this.profileMenu && (!!item.route || !!item.href)},
    isInApp(item){ return item.is_modularity_route ?? false},
    isInertiaLink(item){ return this.isInApp(item) && this.$shouldUseInertia()},

    formatBadgeContent(badge) {
      let value = parseInt(badge);
      if (value > 9) return '9+';
      return value;
    },
  },
};
</script>

<style scoped>
</style>
