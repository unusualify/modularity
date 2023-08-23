<template>
  <v-app>
    <v-layout class="rounded rounded-md bg-tertiary h-100">
      <v-main class="d-flex align-center justify-center" >
        <v-card class="mx-auto elevation-3">
          <div class="bg-primary darken-3">
            <span v-svg symbol="main-logo"></span>
          </div>
          <slot name="cardTop"></slot>

          <v-sheet class="px-6 py-8" :width="width">
              <slot
                v-bind="{
                  // validForm,
                  // buttonDefaultText
                }"
                >
                <!-- <ue-form
                  :model="model"
                  :schema="schema"
                  action-url="/login"
                  :async="true"
                  :hasSubmit="true"
                  buttonText="auth.login"
                  >
                  <template #submit="{validForm, buttonDefaultText}">
                    <v-btn block dense type="submit" :disabled="!validForm">
                      {{ buttonDefaultText.toUpperCase() }}
                    </v-btn>
                  </template>
                </ue-form> -->
              </slot>
          </v-sheet>
          <slot name="bottom"
            v-bind="{
              // validForm,
              // buttonDefaultText
            }"
            >
          </slot>
        </v-card>
      </v-main>
    </v-layout>
    <ue-alert ref='alert'></ue-alert>
  </v-app>
</template>
<script>
import { computed } from 'vue'
import { useDisplay } from 'vuetify'

export default {
  props: {
    slots: {
      type: Object,
      default () {
        return {}
      }
    }
  },
  data: () => ({
    schema: {
      email: {
        type: 'text',
        label: 'E-mail',
        col: {
          cols: 12
        },
        variant: 'outlined',
        rules: [['email']]
      },
      password: {
        type: 'password',
        label: 'Password',
        col: {
          cols: 12
        },
        variant: 'outlined',
        rules: []
      }
    },
    model: {
      email: '',
      password: ''
    }

  }),
  setup () {
    const { name } = useDisplay()

    const width = computed(() => {
      // name is reactive and
      // must use .value
      switch (name.value) {
        case 'xs': return 300
        // case 'sm': return 400
        // case 'md': return 400
        // case 'lg': return 400
        case 'xl': return 600
        case 'xxl': return 600
        default: return 400
      }
    })

    return { width }
  },
  methods: {
    onSubmit () {
      if (!this.form) return

      this.loading = true

      setTimeout(() => (this.loading = false), 2000)
    },
    required (v) {
      return !!v || 'Field is required'
    }
  }
}
</script>
