<!-- EditorMentionMenu.vue -->
<!-- NEW FILE -->
<!-- VueRenderer ile mount edilir. Aynı klavye API'sı: onKeyDown({ event }) → bool -->

<template>
  <teleport to="body">
    <transition name="ue-mention-fade">
      <div
        v-if="items.length"
        ref="menuEl"
        class="ue-mention-menu"
        :style="menuStyle"
        role="listbox"
        aria-label="Kişiler"
      >
        <button
          v-for="(item, index) in items"
          :key="item.id"
          class="ue-mention-menu__item"
          :class="{ 'ue-mention-menu__item--active': selectedIndex === index }"
          role="option"
          :aria-selected="selectedIndex === index"
          @mouseenter="selectedIndex = index"
          @mousedown.prevent="selectItem(index)"
        >
          <!-- Avatar: ya resim, ya baş harf avatarı -->
          <span
            class="ue-mention-menu__avatar"
            :style="item.avatar ? {} : { background: avatarColor(item.label) }"
          >
            <img v-if="item.avatar" :src="item.avatar" :alt="item.label">
            <span v-else>{{ item.label.charAt(0).toUpperCase() }}</span>
          </span>

          <span class="ue-mention-menu__info">
            <span class="ue-mention-menu__name">{{ item.label }}</span>
            <span v-if="item.subtitle" class="ue-mention-menu__subtitle">{{ item.subtitle }}</span>
          </span>
        </button>
      </div>
    </transition>
  </teleport>
</template>

<script>
// Deterministik renk paleti (label'dan üretilir, tema-agnostik)
const AVATAR_COLORS = [
  '#5b6af0', '#e05c7a', '#12b886', '#f59f00',
  '#228be6', '#ae3ec9', '#f76707', '#2f9e44',
]

function hashStr (str) {
  let h = 0
  for (let i = 0; i < str.length; i++) h = ((h << 5) - h + str.charCodeAt(i)) | 0
  return Math.abs(h)
}

export default {
  name: 'EditorMentionMenu',
  props: {
    items:      { type: Array,    default: () => [] },
    command:    { type: Function, required: true },
    clientRect: { type: Function, default: null },
  },
  data () {
    return { selectedIndex: 0 }
  },
  computed: {
    menuStyle () {
      if (!this.clientRect) return {}
      const rect = this.clientRect()
      if (!rect) return {}
      return {
        position: 'fixed',
        top:  `${rect.bottom + 6}px`,
        left: `${rect.left}px`,
        zIndex: 2000,
      }
    },
  },
  watch: {
    items () { this.selectedIndex = 0 },
  },
  methods: {
    avatarColor (label) {
      return AVATAR_COLORS[hashStr(label) % AVATAR_COLORS.length]
    },
    selectItem (index) {
      const item = this.items[index]
      if (item) this.command({ id: item.id, label: item.label })
    },
    onKeyDown ({ event }) {
      if (event.key === 'ArrowUp') {
        this.selectedIndex = (this.selectedIndex - 1 + this.items.length) % this.items.length
        return true
      }
      if (event.key === 'ArrowDown') {
        this.selectedIndex = (this.selectedIndex + 1) % this.items.length
        return true
      }
      if (event.key === 'Enter') {
        this.selectItem(this.selectedIndex)
        return true
      }
      return false
    },
  },
}
</script>

<style lang="scss">
  .ue-mention-menu {
    min-width: 220px;
    max-height: 300px;
    overflow-y: auto;
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.14);
    padding: 6px;

    &__item {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      padding: 7px 10px;
      border: none;
      border-radius: 8px;
      background: transparent;
      cursor: pointer;
      text-align: left;
      transition: background 0.1s ease;

      &--active,
      &:hover {
        background: rgba(var(--v-theme-primary), 0.08);
      }
    }

    &__avatar {
      width: 32px;
      height: 32px;
      flex-shrink: 0;
      border-radius: 50%;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 600;
      color: #fff;

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }

    &__info {
      display: flex;
      flex-direction: column;
      gap: 1px;
      min-width: 0;
    }

    &__name {
      font-size: 13.5px;
      font-weight: 500;
      color: rgba(var(--v-theme-on-surface), 0.88);
      line-height: 1.3;
    }

    &__subtitle {
      font-size: 11.5px;
      color: rgba(var(--v-theme-on-surface), 0.45);
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }

  // Mention chip (editör içi)
  .ue-mention {
    display: inline-flex;
    align-items: center;
    background: rgba(var(--v-theme-primary), 0.1);
    color: rgb(var(--v-theme-primary));
    border-radius: 4px;
    padding: 0 5px;
    font-weight: 500;
    font-size: 0.92em;
    white-space: nowrap;
    cursor: default;
    user-select: all;
  }

  .ue-mention-fade-enter-active,
  .ue-mention-fade-leave-active {
    transition: opacity 0.1s ease, transform 0.1s ease;
  }
  .ue-mention-fade-enter-from,
  .ue-mention-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
  }
</style>
