<template>
  <v-container class="fill-height">
    <v-row justify="center" align="center">
      <v-col cols="12" sm="12" md="12" lg="12" class="text-center">
        <v-card flat class="bg-transparent">
          <v-card-item>
            <v-icon
              icon="mdi-check-circle"
              color="success"
              size="64"
              class="mb-4"
            ></v-icon>
            <h1 class="text-h4 mb-2 text-success">{{ title }}</h1>
            <p class="text-body-1 text-medium-emphasis mb-6">
              {{ description }}
            </p>
            <v-btn
              color="success"
              class="mt-10"
              :href="button_url"
              min-width="200"
              density="default"

            >
              {{ button_text }}
            </v-btn>
          </v-card-item>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'UeSuccess',

  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      required: true
    },
    button_text: {
      type: String,
      required: true
    },
    button_url: {
      type: String,
      required: true
    }
  },

  setup(props) {
    // You can access props directly here if needed
    // Example:
    // const formattedTitle = computed(() => props.title.toUpperCase())
    // console.log(props.button_text)
    return {
      // Return computed properties or methods if needed
    }
  }
})
</script>
