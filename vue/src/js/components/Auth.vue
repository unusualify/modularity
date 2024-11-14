<template>
  <v-app>
    <v-layout class="rounded rounded-md bg-tertiary h-100">
      <v-main class="d-flex align-center justify-center" >
        <v-row class="h-100 mw-100">
          <v-col
            cols="12"
            md="6"
            lg="6"
            class="py-12 d-flex flex-column align-center justify-center bg-white">
            <!-- <v-card class="mx-auto"> -->
              <v-row width="85%" class="d-flex flex-column justify-center align-center">
                <!-- <h1 class="text-primary">{{ title }}</h1> -->
                <div class="bg-primary darken-3">
                  <!-- <span v-svg symbol="main-logo"></span> -->
                </div>
                <slot name="cardTop"></slot>

                <v-sheet class="" :width="width">
                    <slot v-bind="{}"
                      >
                      <ue-form
                        :model="model"
                        :schema="schema"
                        action-url="/login"
                        :async="true"
                        :hasSubmit="true"
                        buttonText="auth.login"
                        class="auth-form"
                        >
                        <template #submit="{validForm, buttonDefaultText}">
                          <v-btn block dense type="submit" :disabled="!validForm">
                            {{ buttonDefaultText.toUpperCase() }}
                          </v-btn>
                        </template>
                      </ue-form>
                    </slot>
                </v-sheet>

                  <div class="d-flex w-100 align-center justify-center">
                    <v-divider />
                    <div class="text-no-wrap px-3">or</div>
                    <v-divider />
                  </div>

                <slot name="bottom" v-bind="{}"></slot>
              </v-row>

            <!-- </v-card> -->
          </v-col>
          <v-col
            cols="12"
            md="6"
            lg="6"
            class="px-xs-12 py-xs-3 px-sm-12 py-sm-3 pa-12 pa-md-0 d-flex flex-column align-center justify-center col-right bg-primary">
            <div class="mw-420">
              <ue-svg-icon symbol="main-logo" class="mx-0 "></ue-svg-icon>

              <h2 class="text-white my-5 text-h4">
                {{ description }}
              </h2>
              <span class="text-white">
                <v-img :href="adImg">

                </v-img>
                {{ ad }}
              </span>
              <v-btn
                variant="outlined"
                class="text-white custom-right-auth-button my-5"
                density="default">
                {{ rightBtnText }}
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
    description: {
      type: String,
      default: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr.'
    },
    ad: {
      type: String,
      default: '3k+ people joined us, now it’s your turn'
    },
    adImg: {
      type: String,
      default: '',
    },
    rightBtnText: {
      type: String,
      default: 'CONTINUE WITHOUT LOGIN'
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
        case 'xs': return 300
        case 'xl': return 600
        case 'xxl': return 600
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
  .mw-lg-24em {
    max-width: 24rem;
    width: 24rem;
  }

  .mw-420 {
    max-width: 420px;
  }
}

.custom-auth-button {
  width: 100%;
  min-width: 100% !important;
  color: black !important;
  position: relative !important;
  display: flex !important;

  .v-btn__prepend {
    position: absolute !important;
    left: 16px !important;
    margin-right: 0 !important;

  }
    svg {
      width: 16px;
      height: 16px;
    }

  &.text-primary {
    color: black !important;
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
