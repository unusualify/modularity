<template>
  <v-radio-group v-model="input" direction="horizontal"  class="v-input-radio-group" hide-details>
    <ue-title v-if="label" :text="label" color="grey-darken-5" padding="b-4" margin="b-0" transform="none"/>
    <v-row
      :class="[
        $vuetify.display.mdAndUp && isCentered ? 'me-auto ms-auto' : '',
      ]"
      :style="{
        ...(!$vuetify.display.mobile ? {maxWidth: maxWidth, minWidth: minWidth} : {}),
      }">
      <v-col v-for="(item, i) in items" :key="`col-${item[itemValue]}`" :cols="12" :md="6">
        <v-btn
          block
          class="v-input-radio-group__btn"
          :color="color"
          :variant="input == item[itemValue] ? 'elevated' : 'outlined'"
          :disabled="$attrs.disabled ?? false"
          :readonly="protectInitialValue"
          @click="input=item[itemValue]"
        >
          <template #prepend>
            <v-radio :value="item[itemValue]" :disabled="$attrs.disabled ?? false"></v-radio>
          </template>
          {{ item[itemTitle].toUpperCase() }}
        </v-btn>

        <div v-if="item[descriptionTitle]" class="text-caption pa-2" v-html="item[descriptionTitle]"></div>
      </v-col>
    </v-row>
  </v-radio-group>
</template>

<script setup>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

const props = defineProps({
  ...makeInputProps(),
  items: {
    type: Object,
    default: () => []
  },
  itemTitle: {
    type: String,
    default: 'name'
  },
  itemValue: {
    type: String,
    default: 'id'
  },
  descriptionTitle: {
    type: String,
    default: 'description'
  },
  color: {
    type: String,
    default: 'primary'
  },
  maxWidth: {
    type: [String, Number],
    default: null
  },
  minWidth: {
    type: [String, Number],
    default: null
  },
  isCentered: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([...makeInputEmits])

const initializeInput = (val) => {
  return val
}

const { input } = useInput(props, {
  ...{
    emit
  },
  ...{ initializeInput }
})

</script>

<style lang="sass">
  .v-input-radio-group
    .v-input__control
      display: block


    .v-input-radio-group__btn
      padding-left: 4px
      justify-content: flex-start !important

      .v-btn__content
        width: 100%
        white-space: normal


</style>

<style lang="scss">

</style>
