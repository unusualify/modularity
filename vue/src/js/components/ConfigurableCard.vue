<template>
  <v-card class="ue-configurable-card" :class="[$bindAttributes().class, cardClass]">
    <!-- <template v-if="title" v-slot:title>
      <span class="font-weight-bold text-primary text-body-1">{{ title }}</span>
    </template> -->
    <slot name="title">
      <ue-title v-if="title" :text="title" :color="titleColor" :padding="`x-${titlePXNumber}`" class="pt-4"/>
    </slot>
    <div no-gutters class="ue-configurable-card__row"
      :style="rowStyle"
      :class="[
        $vuetify.display.smAndDown ? `ga-${mobileRowGap}` : '',
        rowMarginY ? `my-${rowMarginY}` : '',
        rowMarginX ? `mx-${rowMarginX}` : ''
      ]"
    >
      <div
        v-for="( segment,  segmentIndex) in itemsWithActions"
        :key="segmentIndex"
        :class="[
          'ue-configurable-card__col',
          !hideSeparator ? 'ue-configurable-card__col--seperator' : '',
          justifyCenterColumns ? 'ue-configurable-card__col--justify-center' : '',
          alignCenterColumns ? 'ue-configurable-card__col--align-center' : '',
          $isset(columnStyles[segmentIndex]) || colRatios.length > 0 ? `ue-configurable-card__col--unset-flex-basis` : '',
          columnClasses[segmentIndex] ?? '',
          colPaddingX ? `px-${colPaddingX}` : '',
          colPaddingY ? `py-${colPaddingY}` : ''
        ]"
        :style="getEffectiveColumnStyle(segmentIndex)"
      >
        <slot :name="`segment.${segmentIndex === '_actions' ? 'actions' : (parseInt(segmentIndex) + 1)}`"
          v-bind="{
            data: segment,
            actions: actions,
            actionProps: {actionIconMinHeight, actionIconSize}
          }"
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
              <ue-property-list :data="segment" class="" noPadding/>
            </div>
          </template>
          <template v-else-if="isArray( segment)">
            <div class="d-flex fill-height">
              <ue-property-list :data="segment.map(item => [item])" class="" noPadding/>
            </div>

          </template>
          <template v-else>
            <div class="d-flex fill-height">
              <ue-dynamic-component-renderer :subject="segment"/>
            </div>
          </template>
        </slot>
      </div>
    </div>
  </v-card>
</template>

<script>
  export default {
    name: 'ue-configurable-card',
    props: {
      title: {
        type: String,
        default: ''
      },
      titleColor: {
        type: String,
      },
      titlePaddingX: {
        type: [Number, String],
      },
      titlePaddingY: {
        type: [Number, String],
      },
      items: {
        type: [Object, Array],
        required: true
      },
      actions: {
        type: Array,
        default: () => []
      },
      hideSeparator: {
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
      },
      rowMarginY: {
        type: [Number, String],
        default: 4
      },
      rowMarginX: {
        type: [Number, String],
      },
      colPaddingX: {
        type: [Number, String],
        default: 2
      },
      colPaddingY: {
        type: [Number, String],
      },
      columnStyles: {
        type: Object,
        default: () => ({}),
        // Example format: { 0: 'flex-basis: 50%', 1: 'flex-basis: 25%', 2: 'flex-basis: 25%' } or { 0: 'flex-grow: 2', 1: 'flex-grow: 1' }
      },
      columnClasses: {
        type: Object,
        default: () => ({}),
        // Example format: { 0: 'd-flex', '_actions': 'd-flex' }
      },
      colRatios: {
        type: Array,
        default: () => [],
        // Example format: [2, 1, 1] for 2:1:1 ratio or [3, 2, 1] for 3:2:1 ratio
      },
      rowMinHeight: {
        type: String,
        default: null
      },
      noActions: {
        type: Boolean,
        default: false
      },
      mobileRowGap: {
        type: [Number, String],
        default: 4
      }
    },
    computed: {
      titlePXNumber() {
        if (this.titlePaddingX) {
          return parseInt(this.titlePaddingX);
        }

        let padding = 0;

        if(this.rowMarginX) {
          padding += parseInt(this.rowMarginX);
        }

        if(this.colPaddingX) {
          padding += parseInt(this.colPaddingX);
        }

        return padding;
      },
      titlePYNumber() {
        return this.titlePaddingY ? parseInt(this.titlePaddingY) : 4;
      },
      cardClass() {
        return `ue-configurable-card--${this.effectiveSegmentCount}-columns`;
      },
      itemsWithActions() {
        // const lastKey = Object.keys(this.items).pop();
        const result = { ...this.items };
        if (this.actions.length && !this.noActions) {
          result['_actions'] = this.actions;
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
      },
      rowStyle() {
        return {
          ...(this.rowMinHeight ? { minHeight: this.rowMinHeight } : {})
        }
      },
      totalRatio() {
        // Calculate the total ratio to determine percentages
        if (!this.colRatios || this.colRatios.length === 0) {
          return this.effectiveSegmentCount;
        }

        return this.colRatios.reduce((sum, ratio, index) => {
          // Use the provided ratio or default to 1
          const value = ratio || 1;
          return sum + value;
        }, 0);
      }
    },
    methods: {
      isObject(value) {
        return __isObject(value);
      },
      isArray(value) {
        return __isArray(value);
      },
      getColumnStyleFromRatio(segmentIndex) {
        // Return null if no ratios are provided
        if (!this.colRatios || this.colRatios.length === 0) {
          return null;
        }

        // Convert segmentIndex to numeric index (handle '_actions' case)
        const colIndex = segmentIndex === '_actions' ? this.effectiveSegmentCount - 1 : parseInt(segmentIndex);

        // Use the provided ratio or default to 1
        const ratio = this.colRatios[colIndex] || 1;
        const percentage = (ratio / this.totalRatio) * 100;

        return {
          flex: `${ratio} 0 0`,
          maxWidth: `${percentage}%`
        };
      },
      getEffectiveColumnStyle(segmentIndex) {
        // Priority: 1. Custom columnStyles, 2. colRatios, 3. Default CSS classes
        const customStyle = this.columnStyles[segmentIndex];
        const ratioStyle = this.getColumnStyleFromRatio(segmentIndex);

        if (customStyle) {
          return customStyle;
        } else if (ratioStyle) {
          // Convert style object to CSS string
          return Object.entries(ratioStyle)
            .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value}`)
            .join('; ');
        }

        return '';
      }
    }
  }
</script>

<style scoped lang="sass">
  .ue-configurable-card
    &__row
      display: flex
      flex-wrap: wrap
      max-width: 100%

      .ue-configurable-card__col
        &--justify-center:not(:first-child)
            > *:first-child
              justify-content: center

    .ue-configurable-card__col
      // flex: 1
      min-width: 0
      max-width: 100%

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
        .ue-configurable-card__col:not([class*="ue-configurable-card__col--unset-flex-basis"])
          flex-basis: calc(100% / #{$i})
          max-width: calc(100% / #{$i})

    @media (max-width: 575px)
      &__row
        // flex-direction: column

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
