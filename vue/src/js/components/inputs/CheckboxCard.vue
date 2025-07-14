<template>
  <v-input
    v-model="input[value]"
    hideDetails="auto"
    :variant="boundProps.variant"
    class="v-input-checkbox-card"
    :readonly="readonly"
  >
    <template v-slot:default="defaultSlot">
      <v-card
        :class="[
          'v-input-checkbox-card__item border border-sm border-opacity-75',
          input.includes(value) ? 'bg-primary-lighten-4 border-primary' : 'border-grey-lighten-4',
          disabled ? 'v-input-checkbox-card__item--disabled' : ''
        ]"
        :variant="input.includes(value) ? 'elevated' : 'outlined'"
        @click="toggleSelection"
        :disabled="disabled"
      >
        <v-card-item>
          <template #prepend v-if="!checkboxOnRight">
            <v-checkbox
              v-model="input"
              :value="value"
              :disabled="disabled"
              :color="checkboxColor"
              hide-details
              :readonly="readonly"
            />
          </template>
          <v-card-title
            :class="[
              'v-input-checkbox-card__title',
              input.includes(value) ? `bg-primary-lighten-5 border-primary ${activeTitleColor ? `text-${activeTitleColor}` : ''}` : '',
              input.includes(value) ? `v-input-checkbox-card__title--selected ${activeTitleColor ? `text-${activeTitleColor}` : ''}` : '',
            ]"
          >
            {{ title }}
          </v-card-title>
          <template #append>
            <v-checkbox v-if="checkboxOnRight"
              v-model="input"
              :value="value"
              :disabled="disabled"
              :color="checkboxColor"
              hide-details
              :readonly="readonly"
            />
          </template>
        </v-card-item>

        <v-card-text v-if="description">
          {{ description }}
        </v-card-text>

        <v-card-text v-if="stats">
          <v-row no-gutters>
            <v-col v-for="(stat, index) in stats" :key="index"
                  :cols="12 / stats.length"
                  class="text-center">
              <div class="text-h4 font-weight-bold" :class="`text-${stat.color || 'primary'}`">
                {{ stat.value }}
              </div>
              <div :class="`text-body-2 text-${stat.color || 'primary'}`">{{ stat.label }}</div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </template>
  </v-input>
</template>

<script>
  import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

  export default {
    name: 'v-input-checkbox-card',
    emits: [...makeInputEmits],
    props: {
      ...makeInputProps(),
      title: {
        type: String,
        required: true
      },
      description: {
        type: String,
        default: ''
      },
      disabled: {
        type: Boolean,
        default: false
      },
      readonly: {
        type: Boolean,
        default: false
      },
      value: {
        type: [Number, String],
        default: null
      },
      activeColor: {
        type: String,
        default: null
      },
      activeTitleColor: {
        type: String,
        default: null
      },
      checkboxColor: {
        type: String,
        default: 'primary'
      },
      stats: {
        type: Array,
        default: null,
        validator: (value) => {
          return value === null || value.every(stat =>
            'label' in stat &&
            'value' in stat &&
            (!('color' in stat) || typeof stat.color === 'string')
          )
        }
      },
      checkboxOnRight: {
        type: Boolean,
        default: false
      }
    },
    setup(props, context) {
      return {
        ...useInput(props, context)
      }
    },
    data() {
      return {
        input_: false
      }
    },
    methods: {
      toggleSelection() {
        if (!this.disabled && !this.readonly) {
          if(this.input.includes(this.value)) {
            this.input = this.input.filter(item => item !== this.value)
          } else {
            this.input = [...this.input, this.value]
          }
        }
      }
    },
  }
</script>

<style lang="sass">
  .v-input-checkbox-card
    &__item
      width: 100%
      cursor: pointer
      transition: all 0.3s ease

      &--disabled
        opacity: 0.6
        cursor: not-allowed

      .v-input-checkbox-card__title
        padding: 0

      .v-card-item
        padding: 16px

      .v-card-text
        padding-top: 0
</style>
