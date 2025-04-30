<template>
  <div :class="['ue-collapsible', {'ue-collapsible--bordered': bordered, 'ue-collapsible--elevated': elevated, 'ue-collapsible--dense': dense}]">
    <div
      v-if="title || $slots.title"
      :class="[
        'ue-collapsible__header',
        isOpen ? 'ue-collapsible__header--active' : '',
        `px-${horizontalPadding} py-${verticalPadding}`,
        !noHeaderBackground ? 'ue-collapsible__header--background' : ''
      ]"
      @click="toggle"
    >
      <div class="ue-collapsible__title">
        <slot name="title">{{ title }}</slot>
      </div>
      <v-icon
        v-if="!noCollapse"
        :icon="isOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'"
        size="small"
        class="ue-collapsible__icon"
      />
    </div>

    <transition
      name="expand"
      @enter="enter"
      @after-enter="afterEnter"
      @leave="leave"
    >
      <div v-if="isOpen" class="ue-collapsible__content">
        <div :class="['ue-collapsible__content-inner', `px-${horizontalPadding} py-${verticalPadding}`]">
          <slot></slot>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { VExpandTransition } from 'vuetify/components'

export default {
  name: 'ue-collapsible',
  components: {
    VExpandTransition
  },
  props: {
    title: {
      type: String,
      default: ''
    },
    modelValue: {
      type: Boolean,
      default: false
    },
    bordered: {
      type: Boolean,
      default: false
    },
    elevated: {
      type: Boolean,
      default: false
    },
    horizontalPadding: {
      type: Number,
      default: 4
    },
    verticalPadding: {
      type: Number,
      default: 3
    },
    color: {
      type: String,
      default: 'primary'
    },
    dense: {
      type: Boolean,
      default: false
    },
    noHeaderBackground: {
      type: Boolean,
      default: false
    },
    noCollapse: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isOpen: this.noCollapse || this.modelValue
    }
  },
  watch: {
    modelValue(val) {
      if (this.noCollapse) {
        this.isOpen = true;
      } else {
        this.isOpen = val;
      }
    }
  },
  methods: {
    toggle() {
      if (this.noCollapse) {
        return;
      }

      this.isOpen = !this.isOpen;
      this.$emit('update:modelValue', this.isOpen);
      this.$emit(this.isOpen ? 'open' : 'close');
    },
    enter(element) {
      const width = getComputedStyle(element).width;
      element.style.width = width;
      element.style.position = 'absolute';
      element.style.visibility = 'hidden';
      element.style.height = 'auto';

      const height = getComputedStyle(element).height;
      element.style.width = null;
      element.style.position = null;
      element.style.visibility = null;
      element.style.height = 0;

      // Force repaint
      getComputedStyle(element).height;

      requestAnimationFrame(() => {
        element.style.height = height;
      });
    },
    afterEnter(element) {
      element.style.height = 'auto';
    },
    leave(element) {
      const height = getComputedStyle(element).height;
      element.style.height = height;

      // Force repaint
      getComputedStyle(element).height;

      requestAnimationFrame(() => {
        element.style.height = 0;
      });
    }
  },
  created() {},
  mounted() {
    // Ensure the initial state is correct
    if (this.isOpen && this.$el) {
      const content = this.$el.querySelector('.ue-collapsible__content');
      if (content) {
        content.style.height = 'auto';
      }
    }
  }
}
</script>

<style lang="scss">
  .ue-collapsible {
    margin-bottom: 8px;
    position: relative;

    &--bordered {
      border: 1px solid rgba(0, 0, 0, 0.12);
      border-radius: 4px;
      overflow: hidden;
    }

    &--elevated {
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      border-radius: 4px;
      overflow: hidden;
    }

    &__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      // background-color: rgba(0, 0, 0, 0.03);
      transition: background-color 0.2s ease;

      &--background {
        background-color: rgba(0, 0, 0, 0.03);
      }

      &:hover {
        opacity: 0.9;
        // background-color: rgba(0, 0, 0, 0.06);
      }

      &--active {
        font-weight: 500;
      }
    }

    &__title {
      flex: 1;
    }

    &__icon {
      margin-left: 8px;
    }

    &__content {
      overflow: hidden;
      transition: height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      height: 0;
    }

    &__content-inner {
      padding: 0;
    }
  }

  // Dense variant
  // .ue-collapsible--dense {
  //   .ue-collapsible__header {
  //     padding: 8px 12px;
  //   }

  //   .ue-collapsible__content-inner {
  //     padding: 0;
  //   }
  // }

  .expand-enter-active,
  .expand-leave-active {
    transition: height 0.3s ease;
    overflow: hidden;
  }

  .expand-enter-from,
  .expand-leave-to {
    height: 0;
  }
</style>
