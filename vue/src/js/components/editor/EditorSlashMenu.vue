<!-- EditorSlashMenu.vue -->
<!-- NEW FILE -->
<!-- VueRenderer ile mount edilir. Props: items[], command(item) -->
<!-- Klavye: ok tuşları + Enter, fare: click -->

<template>
  <teleport to="body">
    <transition name="ue-slash-fade">
      <div
        v-if="items.length"
        ref="menuEl"
        class="ue-slash-menu"
        :style="menuStyle"
        role="listbox"
        aria-label="Komutlar"
      >
        <div class="ue-slash-menu__header">Komut ekle</div>

        <template v-for="(group, gi) in groupedItems" :key="gi">
          <div class="ue-slash-menu__group-label">{{ group.label }}</div>
          <button
            v-for="(item, ii) in group.items"
            :key="item.id"
            class="ue-slash-menu__item"
            :class="{ 'ue-slash-menu__item--active': selectedIndex === flatIndex(gi, ii) }"
            role="option"
            :aria-selected="selectedIndex === flatIndex(gi, ii)"
            @mouseenter="selectedIndex = flatIndex(gi, ii)"
            @mousedown.prevent="selectItem(flatIndex(gi, ii))"
          >
            <span class="ue-slash-menu__icon">
              <v-icon size="18">{{ item.icon }}</v-icon>
            </span>
            <span class="ue-slash-menu__text">
              <span class="ue-slash-menu__label">{{ item.label }}</span>
              <span class="ue-slash-menu__desc">{{ item.desc }}</span>
            </span>
          </button>
        </template>

        <div v-if="!items.length" class="ue-slash-menu__empty">Komut bulunamadı</div>
      </div>
    </transition>
  </teleport>
</template>

<script>
export default {
  name: 'EditorSlashMenu',
  props: {
    items:        { type: Array,  default: () => [] },
    command:      { type: Function, required: true },
    clientRect:   { type: Function, default: null },
  },
  data () {
    return {
      selectedIndex: 0,
    }
  },
  computed: {
    // grup başlıklarını koruyarak flat item'lardan gruplu yapı çıkar
    groupedItems () {
      const map = new Map()
      for (const item of this.items) {
        const g = item.group ?? 'Diğer'
        if (!map.has(g)) map.set(g, [])
        map.get(g).push(item)
      }
      return [...map.entries()].map(([label, items]) => ({ label, items }))
    },
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
    items () {
      this.selectedIndex = 0
    },
  },
  methods: {
    // gi = group index, ii = item index içinde → flat index
    flatIndex (gi, ii) {
      let offset = 0
      for (let g = 0; g < gi; g++) offset += this.groupedItems[g].items.length
      return offset + ii
    },
    selectItem (index) {
      const flat = this.items[index]
      if (flat) this.command(flat)
    },
    onKeyDown ({ event }) {
      if (event.key === 'ArrowUp') {
        this.selectedIndex = (this.selectedIndex - 1 + this.items.length) % this.items.length
        this.scrollActiveIntoView()
        return true
      }
      if (event.key === 'ArrowDown') {
        this.selectedIndex = (this.selectedIndex + 1) % this.items.length
        this.scrollActiveIntoView()
        return true
      }
      if (event.key === 'Enter') {
        this.selectItem(this.selectedIndex)
        return true
      }
      return false
    },
    scrollActiveIntoView () {
      this.$nextTick(() => {
        const el = this.$refs.menuEl?.querySelector('.ue-slash-menu__item--active')
        el?.scrollIntoView({ block: 'nearest' })
      })
    },
  },
}
</script>

<style lang="scss">
  .ue-slash-menu {
    min-width: 260px;
    max-width: 320px;
    max-height: 360px;
    overflow-y: auto;
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.14), 0 2px 8px rgba(0, 0, 0, 0.08);
    padding: 6px;

    &__header {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: rgba(var(--v-theme-on-surface), 0.4);
      padding: 4px 8px 6px;
    }

    &__group-label {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: rgba(var(--v-theme-on-surface), 0.35);
      padding: 8px 10px 4px;

      &:first-of-type {
        padding-top: 2px;
      }
    }

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
      transition: background 0.12s ease;

      &--active,
      &:hover {
        background: rgba(var(--v-theme-primary), 0.08);
      }

      &--active .ue-slash-menu__label {
        color: rgb(var(--v-theme-primary));
      }
    }

    &__icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      flex-shrink: 0;
      border-radius: 8px;
      background: rgba(var(--v-theme-on-surface), 0.06);
      color: rgba(var(--v-theme-on-surface), 0.7);
    }

    &__text {
      display: flex;
      flex-direction: column;
      gap: 1px;
      min-width: 0;
    }

    &__label {
      font-size: 13.5px;
      font-weight: 500;
      color: rgba(var(--v-theme-on-surface), 0.88);
      line-height: 1.3;
    }

    &__desc {
      font-size: 11.5px;
      color: rgba(var(--v-theme-on-surface), 0.45);
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    &__empty {
      padding: 16px 10px;
      text-align: center;
      font-size: 13px;
      color: rgba(var(--v-theme-on-surface), 0.4);
    }
  }

  // Transition
  .ue-slash-fade-enter-active,
  .ue-slash-fade-leave-active {
    transition: opacity 0.1s ease, transform 0.1s ease;
  }
  .ue-slash-fade-enter-from,
  .ue-slash-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
}
</style>
