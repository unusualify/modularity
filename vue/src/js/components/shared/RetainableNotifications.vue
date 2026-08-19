<template>
  <div
    v-if="count > 0"
    class="ue-retainable-notifications position-fixed d-flex flex-column align-end ga-2 pointer-pass-through"
  >
    <v-expand-transition>
      <v-card
        v-show="panelOpen"
        elevation="4"
        rounded="lg"
        role="dialog"
        aria-label="Retained notifications"
        class="ue-retainable-notifications__panel d-flex flex-column overflow-hidden"
      >
        <v-card-title class="d-flex align-center justify-space-between text-body-medium py-2 px-3">
          <span class="font-weight-medium">Notifications</span>
          <v-btn
            icon
            size="x-small"
            variant="text"
            aria-label="Close panel"
            @click="toggle(false)"
          >
            <v-icon icon="mdi-close" />
          </v-btn>
        </v-card-title>

        <v-divider />

        <v-list
          density="compact"
          bg-color="transparent"
          class="pa-2 overflow-y-auto flex-grow-1"
        >
          <v-sheet
            v-for="item in items"
            :key="item.id"
            rounded="lg"
            color="surface-variant"
            class="pa-3 mb-2"
          >
            <div
              v-if="item.title"
              class="text-title-small font-weight-medium"
            >
              {{ item.title }}
            </div>
            <div
              v-if="item.description"
              class="text-body-small text-pre-line text-medium-emphasis"
            >
              {{ item.description }}
            </div>
            <div class="d-flex flex-wrap ga-1 mt-2">
              <v-btn
                v-if="item.hasRedirector && item.redirector"
                size="x-small"
                variant="flat"
                color="white"
                @click.stop="openRedirector(item)"
              >
                {{ item.redirectorText || 'Look' }}
              </v-btn>
              <v-btn
                size="x-small"
                variant="text"
                @click.stop="dismissItem(item)"
              >
                Close
              </v-btn>
            </div>
          </v-sheet>
        </v-list>
      </v-card>
    </v-expand-transition>

    <v-badge
      :content="count"
      color="primary"
      floating
    >
      <v-btn
        icon
        rounded="pill"
        size="large"
        elevation="4"
        color="surface"
        :aria-expanded="panelOpen ? 'true' : 'false'"
        :aria-label="toggleLabel"
        @click="toggle()"
      >
        <v-icon icon="mdi-bell-badge-outline" />
      </v-btn>
    </v-badge>
  </div>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, watch } from 'vue'
import useRetainableNotifications from '@/hooks/useRetainableNotifications'

const {
  items,
  count,
  panelOpen,
  dismiss,
  dismissByGroup,
  prune,
  hydrate,
  toggle,
} = useRetainableNotifications()

const toggleLabel = computed(() => (
  panelOpen.value
    ? 'Hide retained notifications'
    : `Show ${count.value} retained notification${count.value === 1 ? '' : 's'}`
))

function resolveUserId () {
  const ns = import.meta.env.VUE_APP_NAME
  const raw = window[ns]?.STORE?.broadcast?.userId
    ?? window[ns]?.STORE?.user?.profile?.id
    ?? null
  const id = Number(raw)

  return Number.isFinite(id) && id > 0 ? id : null
}

function dismissItem (item) {
  if (item?.retainGroup) {
    dismissByGroup(item.retainGroup)

    return
  }

  dismiss(item.id)
}

function openRedirector (item) {
  if (!item?.redirector) {
    return
  }
  window.open(item.redirector, '_blank', 'noopener')
  dismissItem(item)
}

let pruneTimer = null

onMounted(() => {
  hydrate(resolveUserId())
  prune()
  pruneTimer = setInterval(prune, 60_000)
})

onBeforeUnmount(() => {
  if (pruneTimer) {
    clearInterval(pruneTimer)
    pruneTimer = null
  }
})

watch(count, (n) => {
  if (n === 0) {
    toggle(false)
  }
})
</script>

<style scoped>
/* Fixed inset + stacking only — Vuetify has position-fixed / pointer-pass-through but not these offsets or z-index. */
.ue-retainable-notifications {
  right: 16px;
  bottom: 16px;
  z-index: 10002;
}

.ue-retainable-notifications__panel {
  width: min(360px, calc(100vw - 32px));
  max-height: min(420px, 50vh);
}
</style>
