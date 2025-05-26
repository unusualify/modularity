<template>
  <v-app>
    <v-layout class="rounded rounded-md bg-tertiary h-100">
      <v-main class="d-flex align-center justify-center" >
        <v-row class="h-100 mw-100">
          <v-col
            v-bind="{
              cols: '12',
              ...(noSecondSection ? {} : {
                md: '6',
                lg: '6',
              })
            }"
            class="py-12 d-flex flex-column align-center justify-center bg-white">
            <!-- <v-card class="mx-auto"> -->
              <v-row width="85%" class="d-flex flex-column justify-center align-center">
                <!-- <h1 class="text-primary">{{ title }}</h1> -->
                <div class="bg-primary darken-3">
                </div>

                <span v-if="noSecondSection" v-svg symbol="main-logo-full-light"></span>

                <slot name="cardTop"></slot>

                <v-sheet
                  class=""
                  :width="width"
                >
                  <slot v-bind="{}">
                    <ue-form
                      :model="model"
                      :schema="schema"
                      action-url="/login"
                      :async="true"
                      :hasSubmit="true"
                      buttonText="auth.login"
                      class="auth-form"
                    >
                      <template #submit="submitScope">
                        <v-btn block dense type="submit" :disabled="!submitScope.validForm" :loading="submitScope.loading">
                          {{ submitScope.buttonDefaultText.toUpperCase() }}
                        </v-btn>
                      </template>
                    </ue-form>
                  </slot>
                </v-sheet>

                <div
                  v-if="!noDivider && $isset($slots.bottom)"
                  class="d-flex w-100 align-center justify-center"
                >
                  <v-divider />
                  <div class="text-no-wrap px-3">or</div>
                  <v-divider />
                </div>

                <slot name="bottom" v-bind="{}"></slot>
              </v-row>

            <!-- </v-card> -->
          </v-col>
          <v-col v-if="!noSecondSection"
            cols="12"
            md="6"
            lg="6"
            class="px-xs-12 py-xs-3 px-sm-12 py-sm-3 pa-12 pa-md-0 d-flex flex-column align-center justify-center col-right bg-primary"
          >
            <div class="mw-420">
              <ue-svg-icon symbol="main-logo" class="mx-0 "></ue-svg-icon>

              <slot name="description">
                <h2 class="text-white mt-5 text-h4 custom-mb-8rem fs-2rem">
                  {{ bannerDescription }}
                </h2>
              </slot>
              <span class="text-white">
                <v-img :href="adImg"></v-img>
                {{ bannerSubDescription }}
              </span>
              <v-btn
                v-if="redirectUrl"
                variant="outlined"
                class="text-white custom-right-auth-button my-5"
                density="default"
                :href="redirectUrl"
                >
                {{ redirectButtonText }}
              </v-btn>
            </div>
          </v-col>
        </v-row>

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
    },
    title: {
      type: String,
    },
    bannerDescription: {
      type: String,
      default: ''
    },
    bannerSubDescription: {
      type: String,
      default: ''
    },
    adImg: {
      type: String,
      default: '',
    },
    redirectUrl: {
      type: String,
      default: null,
    },
    redirectButtonText: {
      type: String,
      default: ''
    },
    noDivider: {
      type: [Boolean, Number],
      default: false
    },
    noSecondSection: {
      type: [Boolean, Number],
      default: false
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
  setup (props) {
    const { name } = useDisplay()

    const width = computed(() => {
      // name is reactive and
      // must use .value

      switch (name.value) {
        case 'xs': return 400
        case 'md': return 400
        case 'lg': return 500
        case 'xl': return 600
        default: return 400
      }
    })

    const title = computed(() => {
      // Use the title prop if it's provided
      if (props.title) {
        return props.title
      }
    })

    const description = computed(() => {
      // Use the description prop if it's provided
      if (props.description) {
        return props.description
      }
    })

    const ad = computed(() => {
      // Use the ad prop if it's provided
      if (props.ad) {
        return props.ad
      }
    })

    const rightBtnText = computed(() => {
      // Use the ad rightBtnText if it's provided
      if (props.rightBtnText) {
        return props.rightBtnText
      }
    })
    // Fix for showDivider logic

    return {
      width,
      title,
      description,
      ad,
      rightBtnText
    }
  },
  methods: {

  }
}
</script>

<style lang="scss">
@media screen and (min-width: 960px) {

  .mw-420 {
    max-width: 420px;
  }
}

.custom-mb-8rem{
  margin-bottom: 8.25rem !important;
}
.text-h4{
  &.fs-2rem{
    font-size: 2rem !important;
  }
}


.custom-auth-button {
  width: 100%;
  min-width: 100% !important;
  position: relative !important;
  display: flex !important;
  background: transparent !important;
  --v-theme-overlay-multiplier: var(--v-hover-opacity);

  &:hover{
    background: transparent !important;
  }

  .v-btn__prepend {
    position: absolute !important;
    left: 16px !important;
    margin-right: 0 !important;

  }
    svg {
      width: 16px;
      height: 16px;
    }
}


.mx-0 {
  svg {
    margin: {
      right: 0 !important;
      left: 0 !important;
    }
  }
}

.custom-right-auth-button {
  &.text-white {
    color: #fff !important;
  }
}

.col-right {
  span.text-white {
    display: block;
  }
  .icon--main-logo{
    svg{
      max-width: 180px;
    }
  }
}
.justify-content-start{
  justify-content: flex-start;
}
.mw-100{
  max-width: 100%;
}

</style>
