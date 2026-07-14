<template>
  <v-app>
    <v-main class="auth-main">
      <div class="auth-scrim" />
      <v-container fluid class="auth-container fill-height">
        <div class="auth-card" :style="formSheetStyle">
          <!-- Logo (light variant for light card background) -->
          <ue-svg-icon
            :symbol="logoLightSymbol"
            class="auth-logo"
          />

          <!-- Description slot (custom auth components use this for banner content) -->
          <div v-if="hasBannerContent" class="auth-header">
            <slot name="description" />
          </div>

          <!-- cardTop slot -->
          <slot name="cardTop" />

          <!-- Form slot (ue-form) -->
          <v-sheet class="auth-form-sheet" elevation="0">
            <slot v-bind="{}" />
          </v-sheet>

          <!-- Divider + bottom slot -->
          <div v-if="showDivider" class="auth-divider">
            <v-divider />
            <span class="auth-divider-text">{{ dividerText }}</span>
            <v-divider />
          </div>
          <slot name="bottom" v-bind="{}" />
        </div>
      </v-container>
    </v-main>
    <ue-alert ref="alert" />
    <ue-dynamic-modal />
  </v-app>
</template>

<script>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useSlots } from 'vue'
import { useDisplay } from 'vuetify'
import { useSvg } from '@/hooks'

export default {
  name: 'UeAuth',
  inheritAttrs: false,
  props: {
    slots: { type: Object, default: () => ({}) },
    noDivider: { type: [Boolean, Number], default: false },
    noSecondSection: { type: [Boolean, Number], default: false },
    logoLightSymbol: { type: String, default: 'main-logo-light' },
    logoSymbol: { type: String, default: 'main-logo-dark' },
  },
  setup (props) {
    const { t, te } = useI18n()
    const slots = useSlots()
    const { name } = useDisplay()
    const { getLocaleSymbol } = useSvg()

    const hasBannerContent = computed(() => !props.noSecondSection)
    const showDivider = computed(() => !props.noDivider && !!slots.bottom)

    const logoLightSymbol = computed(() =>
      getLocaleSymbol(props.logoLightSymbol, 'main-logo-light')
    )

    const formSheetStyle = computed(() => {
      const config = window.__MODULAROUS_AUTH_CONFIG__ || window.MODULAROUS?.AUTH_COMPONENT
      const widths = config?.formWidth ?? {}
      const bp = name.value
      const val = widths[bp] ?? { xs: '85vw', sm: '450px', md: '450px', lg: '500px', xl: '600px', xxl: 700 }[bp] ?? '100%'
      return { width: typeof val === 'number' ? `${val}px` : val }
    })

    const dividerText = computed(() => {
      const config = window.__MODULAROUS_AUTH_CONFIG__ || window.MODULAROUS?.AUTH_COMPONENT
      const key = config?.dividerText ?? 'or'
      return te(key) ? t(key) : key
    })

    return {
      hasBannerContent,
      showDivider,
      logoLightSymbol,
      formSheetStyle,
      dividerText,
    }
  },
}
</script>

<style lang="scss" scoped>
.auth-main {
  min-height: 100vh;
  background: rgb(var(--v-theme-surface));
  position: relative;
}

.auth-scrim {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgb(var(--v-theme-primary)) 0%,
    rgb(var(--v-theme-primary)) 15%,
    transparent 50%,
    rgb(var(--v-theme-surface)) 100%
  );
  opacity: 0.08;
  pointer-events: none;
}

.auth-container {
  max-width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  position: relative;
  z-index: 1;
}

.auth-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: rgb(var(--v-theme-surface));
  border-radius: 16px;
  padding: 2.5rem 2rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.auth-logo {
  margin-bottom: 1.5rem;

  :deep(svg) {
    max-width: 140px;
  }
}

.auth-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.auth-form-sheet {
  width: 100%;
  background: transparent;
}

.auth-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  margin: 1.25rem 0;
}

.auth-divider-text {
  font-size: 0.8125rem;
  font-weight: 500;
  color: rgb(var(--v-theme-on-surface-variant));
  padding: 0 1rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
</style>
