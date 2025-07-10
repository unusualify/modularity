<script setup>
  const props = defineProps({
    elevation: {
      type: Number,
      default: 8
    },
    rounded: {
      type: String,
      default: 'lg'
    },
    icon: {
      type: String,
      default: 'mdi-lock-outline'
    },
    iconSize: {
      type: Number,
      default: 120
    },
    statusCode: {
      type: Number,
      default: 403
    },
    statusText: {
      type: String,
      default: 'Access Forbidden'
    },
    description: {
      type: String,
      default: 'You don\'t have permission to access this resource.'
    },
    alertText: {
      type: String,
      default: 'This action is restricted for modularity authenticated users.'
    },
    alert: {
      type: String,
      default: 'warning'
    },
    homeUrl: {
      type: String,
      default: '/'
    },
    homeText: {
      type: String,
      default: 'Home'
    }
  })
</script>

<template>
    <v-card
      :elevation="elevation"
      :rounded="rounded"
      class="mx-auto text-center"
    >
      <v-card-text class="pa-8">
        <v-icon
          :color="alert"
          :size="iconSize"
          class="mb-6"
        >
          {{ icon }}
        </v-icon>
        <h1 class="text-h1 font-weight-bold text-{{ alert }} mb-4">
          {{ statusCode }}
        </h1>
        <h2 class="text-h4 font-weight-medium mb-6">
          {{ statusText }}
        </h2>
        <p class="text-h6 text-medium-emphasis mb-4">
          {{ description }}
        </p>
        <v-alert
          :type="alert"
          variant="tonal"
          class="mb-6"
        >
          {{ alertText }}
        </v-alert>
        <v-btn
          color="primary"
          size="large"
          variant="elevated"
          prepend-icon="mdi-arrow-left"
          @click="window.history.back()"
          class="me-4"
        >
          {{ $t('Go Back') }}
        </v-btn>
        <v-btn
          color="secondary"
          size="large"
          variant="outlined"
          prepend-icon="mdi-home"
          :href="homeUrl"
        >
          {{ $t(homeText) }}
        </v-btn>
      </v-card-text>
    </v-card>
</template>