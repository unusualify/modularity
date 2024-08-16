<template>
  <v-text-field
    v-model="input"
    @click="menuActive=true"
    v-mask="mask"
    class="ma-0 pa-0"
    v-bind="obj.schema"

    readonly
    dense
    outlined
    hide-details
  >

    <template v-slot:append>
      <v-menu
        v-model="menuActive"
        top
        nudge-bottom="105"
        nudge-left="16"
        :close-on-content-click="false"
      >

        <template v-slot:activator="{ on }">
          <div :style="swatchStyle(input)" v-on="on" />
        </template>
        <v-card>
          <v-card-text class="pa-0">
            <v-color-picker
              v-model="input"
              :label="obj.schema.label"
              flat
            />
          </v-card-text>
        </v-card>
      </v-menu>
    </template>
  </v-text-field>
</template>

<script>
import { InputMixin } from '@/mixins'
import { useInput } from '@/hooks'

export default {
  mixins: [InputMixin],
  name: 'v-input-color',
  setup (props, context) {
    return {
      ...useInput(props, context)
    }
  },
  data () {
    return {
      menuActive: false,
      mask: '!XNNNNNNNN'
    }
  },
  created () {

  },
  computed: {

  },
  methods: {
    swatchStyle (color) {
      const { menuActive } = this
      return {
        backgroundColor: color,
        cursor: 'pointer',
        height: '30px',
        width: '30px',
        borderRadius: menuActive ? '50%' : '4px',
        transition: 'border-radius 200ms ease-in-out',
        marginTop: '-3px'
      }
    }
  }
}
</script>
