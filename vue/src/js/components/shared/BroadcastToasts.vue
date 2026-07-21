<template>
  <div class="ue-broadcast-toasts" aria-live="polite">
    <transition-group name="ue-broadcast-toast" tag="div">
      <v-alert
        v-for="toast in visible"
        :key="toast.id"
        :type="alertType(toast.variant)"
        :color="toast.variant"
        density="comfortable"
        variant="elevated"
        closable
        class="ue-broadcast-toasts__item mb-2"
        @click:close="dismiss(toast.id)"
      >
        <div class="ue-broadcast-toasts__body">
          <div
            v-if="toast.title"
            class="ue-broadcast-toasts__title"
          >
            {{ toast.title }}
          </div>
          <div
            v-if="toast.description"
            class="ue-broadcast-toasts__description"
          >
            {{ toast.description }}
          </div>
          <div
            v-if="toast.detail"
            class="ue-broadcast-toasts__detail text-medium-emphasis"
          >
            {{ toast.detail }}
          </div>
        </div>
      </v-alert>
    </transition-group>
  </div>
</template>

<script setup>
import { watch, onBeforeUnmount } from 'vue'
import useBroadcastToast from '@/hooks/useBroadcastToast'

const { visible, dismiss } = useBroadcastToast()

/** @type {Map<number|string, ReturnType<typeof setTimeout>>} */
const timers = new Map()

const ALERT_TYPES = new Set(['success', 'info', 'warning', 'error'])

function alertType (variant) {
  return ALERT_TYPES.has(variant) ? variant : 'info'
}

function clearTimer (id) {
  const timer = timers.get(id)
  if (timer) {
    clearTimeout(timer)
    timers.delete(id)
  }
}

function scheduleDismiss (toast) {
  if (timers.has(toast.id)) {
    return
  }

  const timeout = Number(toast.timeout)
  if (!Number.isFinite(timeout) || timeout <= 0) {
    return
  }

  timers.set(
    toast.id,
    setTimeout(() => {
      timers.delete(toast.id)
      dismiss(toast.id)
    }, timeout),
  )
}

watch(
  visible,
  (toasts) => {
    const ids = new Set(toasts.map((toast) => toast.id))

    for (const id of timers.keys()) {
      if (!ids.has(id)) {
        clearTimer(id)
      }
    }

    toasts.forEach(scheduleDismiss)
  },
  { immediate: true, deep: true },
)

onBeforeUnmount(() => {
  for (const id of [...timers.keys()]) {
    clearTimer(id)
  }
})
</script>

<style scoped>
.ue-broadcast-toasts {
  position: fixed;
  top: 16px;
  right: 16px;
  z-index: 10001;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: min(420px, calc(100vw - 32px));
  pointer-events: none;
}

.ue-broadcast-toasts__item {
  pointer-events: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
}

.ue-broadcast-toasts__body {
  display: flex;
  flex-direction: column;
  gap: 2px;
  word-break: break-word;
}

.ue-broadcast-toasts__title {
  font-weight: 600;
  line-height: 1.3;
}

.ue-broadcast-toasts__description {
  white-space: pre-line;
  line-height: 1.35;
}

.ue-broadcast-toasts__detail {
  margin-top: 2px;
  font-size: 0.75rem;
  line-height: 1.3;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.ue-broadcast-toast-enter-active,
.ue-broadcast-toast-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.ue-broadcast-toast-enter-from,
.ue-broadcast-toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
