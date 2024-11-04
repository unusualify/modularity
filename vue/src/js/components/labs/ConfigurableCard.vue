<template>
  <v-card class="ue-configurable-card" :class="[$bindAttributes().class, cardClass]">
    <!-- <template v-if="title" v-slot:title>
      <span class="font-weight-bold text-primary text-body-1">{{ title }}</span>
    </template> -->
    <ue-title :text="title" padding="x-4" class="pt-4"/>

    <div no-gutters class="ue-configurable-card__row">
      <div
        v-for="( segment,  segmentIndex) in itemsWithActions"
        :key="segmentIndex"
        :class="[
          'ue-configurable-card__col',
          !hideSeperator ? 'ue-configurable-card__col--seperator' : '',
          justifyCenterColumns ? 'ue-configurable-card__col--justify-center' : '',
          alignCenterColumns ? 'ue-configurable-card__col--align-center' : ''
        ]"
      >
        <template v-if="actions.length && segmentIndex === '_actions'">
          <!-- <v-divider class="my-2"></v-divider> -->
          <div class="d-flex fill-height flex-wrap justify-space-evenly align-center">
            <v-btn
              v-for="(action, index) in actions"
              :key="index"
              class="mx-1 rounded"
              :min-width="actionIconMinHeight"
              :min-height="actionIconMinHeight"
              :size="actionIconSize"
              v-bind="action"
            />
          </div>
        </template>
        <template v-else-if="isObject(segment)">
          <div class="d-flex fill-height">
            <PropertyList :data="segment" class="" noPadding/>
          </div>
        </template>
        <template v-else-if="isArray( segment)">
          <div class="d-flex fill-height">
            <PropertyList :data="segment.map(item => [item])" class="" noPadding/>
          </div>
          <!-- <v-list dense class="">
            <v-list-item v-for="(item, itemIndex) in segment" :key="itemIndex">
              {{ item }}
            </v-list-item>
          </v-list> -->
        </template>
        <template v-else>
          <div class="d-flex fill-height">
            <ue-dynamic-component-renderer :subject="segment"/>
          </div>
        </template>

      </div>
    </div>

    <!-- <v-card-actions v-if="actions && actions.length">
      <v-spacer></v-spacer>
      <v-btn v-for="(action, index) in actions" :key="index" :icon="action.icon" :color="action.color">
        <v-icon>{{ action.icon }}</v-icon>
      </v-btn>
    </v-card-actions> -->
  </v-card>
</template>

<script>
  import PropertyList from '@/components/labs/PropertyList.vue';

  export default {
    components: {
      PropertyList,
    },
    name: 'ue-configurable-card',
    props: {
      title: {
        type: String,
        default: ''
      },
      items: {
        type: [Object, Array],
        required: true
      },
      actions: {
        type: Array,
        default: () => []
      },
      hideSeperator: {
        type: Boolean,
        default: false
      },
      maxSegments: {
        type: Number,
        default: null,
        validator: (value) => value === null || (value > 0 && value <= 12)
      },
      actionIconSize: {
        type: String,
        default: 'medium'
      },
      actionIconMinWidth: {
        type: Number,
        default: 44
      },
      actionIconMinHeight: {
        type: Number,
        default: 44
      },
      alignCenterColumns: {
        type: Boolean,
        default: false
      },
      justifyCenterColumns: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      cardClass() {
        return `ue-configurable-card--${this.effectiveSegmentCount}-columns`;
      },
      itemsWithActions() {
        // const lastKey = Object.keys(this.items).pop();
        const result = { ...this.items };
        if (this.actions.length) {
          result['_actions'] = this.actions;
          // __log(result)
        }
        return result;
      },
      effectiveSegmentCount() {
        const itemCount = this.isArray(this.itemsWithActions)
          ? this.itemsWithActions.length
          : Object.keys(this.itemsWithActions).length;

        if (this.maxSegments === null) {
          return itemCount;
        }
        return Math.min(this.maxSegments, itemCount);
      }
    },
    methods: {
      isObject(value) {
        return __isObject(value);
      },
      isArray(value) {
        return __isArray(value);
      },
    }
  }
</script>

<style scoped lang="sass">
  .ue-configurable-card
    &__row
      display: flex
      flex-wrap: wrap
      // padding-bottom: calc(12 * $spacer / 2)

      .ue-configurable-card__col
        &--justify-center:not(:first-child)
            > *:first-child
              justify-content: center

    .ue-configurable-card__col
      flex: 1
      min-width: 0
      padding: 0 calc($spacer * 4)
      margin: calc($spacer * 4) 0
      &--seperator
        border-right: 1px solid rgba(0, 0, 0, 0.12)
      &--align-center
        > *:first-child
          align-items: center

      &:last-child
        border-right: none

    // Custom styles for different column counts
    @for $i from 1 through 12
      &--#{$i}-columns
        .ue-configurable-card__col
          flex-basis: calc(100% / #{$i})
          max-width: calc(100% / #{$i})

    @media (max-width: 600px)
      &__row
        flex-direction: column

      &__col
        flex-basis: 100% !important
        max-width: 100% !important
        padding: calc( 12 * $spacer/ 2) 0
        &--seperator
          border-right: none
          border-bottom: 1px solid rgba(0, 0, 0, 0.12)

        &:last-child
          border-bottom: none
</style>
