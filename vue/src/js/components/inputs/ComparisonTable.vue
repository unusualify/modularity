<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    appendIcon="mdi-close"
    :variant="boundProps.variant"
    class="v-input-comparison-table"
    :class="{'v-input-comparison-table--striped': striped, 'v-input-comparison-table--highlighted': highlighted}"
    :rules="$attrs.rules"
    :style="inputAndTableStyle"
    >
    <template v-slot:default="defaultSlot">
      <v-data-table
        :class="[
          'h-100',
          selectable && !noFixedSelectable ? 'v-data-table--fixed-selectable' : ''
        ]"
        :style="inputAndTableStyle"

        :headers="headers"
        :items="comparisonItems"
        itemsLength="0"
        disable-sort
        hide-default-footer
        :items-per-page="itemsPerPage"
        :mobile-breakpoint="`md`"
        fixed-header
        >
        <!-- header slots -->
        <template v-for="(header, i) in headers"
          :key="`header-${header.key}`"
          v-slot:[`header.${header.key}`]="headerScope"
          >
          <span v-if="$isset(header.id) && input == header.id" class="v-input-comparison-table__header--selected">
            {{ header.title }}
          </span>
          <span v-else v-html="header.title"></span>
        </template>
        <!-- item slots -->
        <template v-for="(header, i) in headers"
          :key="`item-${header.key}`"
          v-slot:[`item.${header.key}`]="{ item }"
          >
          <v-btn class="my-2" v-if="item[header.key] == '__input'" @click="input=header.id" :variant="input == header.id ? 'elevated' : 'outlined'" :readonly="protectInitialValue">
              {{ $t('Select') }}
          </v-btn>
          <span v-else v-html="item[header.key]"></span>
        </template>

      </v-data-table>
    </template>
  </v-input>
</template>

<script>
  import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
  import Table from '../Table.vue'
  import { find, toUpper, isNumber, isString, get } from 'lodash-es';

  export default {
    name: 'v-input-comparison-table',
    emits: [...makeInputEmits],
    components: {
      Table
    },
    props: {
      ...makeInputProps(),
      schema: {
        type: Object,
        default: () => {}
      },
      items: {
        type: Object,
        default: () => []
      },
      comparatorField: {
        type: String,
        default: 'name'
      },
      comparatorValue: {
        type: String,
        default: 'value'
      },
      comparators: {
        type: Object,
        default: () => {}
      },
      selectable: {
        type: Boolean,
        default: true
      },
      noFixedSelectable: {
        type: Boolean,
        default: false
      },
      striped: {
        type: Boolean,
        default: true
      },
      highlighted: {
        type: Boolean,
        default: true
      },
      itemsPerPage: {
        type: Number,
        default: 50
      },
      maxHeight: {
        type: [String, Number],
        default: null
      }
    },
    setup (props, context) {
      return {
        ...useInput(props, context)
      }
    },
    data: function () {
      return {
        model: null
        // selectedGroup: Object.keys(this.schema)[0] ?? 1,
        // selectedItems: []
      }
    },
    computed: {
      headers() {
        return [{title: this.label, key: 'comparator_name', width: 400, fixed: true, cellProps: {class: 'py-2', style: {minWidth: `250px`}}}].concat( this.items.map((item) => {
          return {title: item.name, key: item.name, align: 'center', id: item.id}
        }))
      },
      inputAndTableStyle() {
        let maxHeight = this.maxHeight

        let style = {}

        if(maxHeight){
          if(isNumber(maxHeight)){
            maxHeight = `${maxHeight}px`
          }

          if(isString(maxHeight)){
            let isNumberable = isNumber(parseInt(maxHeight))
            if(isNumberable){
              maxHeight = `${parseInt(maxHeight)}px`
            } else {
              maxHeight = maxHeight
            }
          }

          style.maxHeight = maxHeight
        }

        return style
      },
      comparisonItems() {
        let commonComparators = Object.keys(this.comparators).reduce((acc, confKey) => {
          const comparatorItem = this.comparators[confKey]
          let comparatorKey = comparatorItem?.key ?? confKey
          this.items.forEach((item) => {
            if(Array.isArray( item[comparatorKey])){
              item[comparatorKey].forEach(function(comparisonObject){
                if(!find(acc, ['id', comparisonObject.id]) ){
                  acc.push({
                    ...comparisonObject,
                    _comparatorField: comparatorItem?.field ?? 'name',
                    ...(comparatorItem?.itemClasses ? {_comparatorItemClasses: comparatorItem.itemClasses} : {})
                  })
                }
              })
            }else {
              let searchKey = __isset(item[`${comparatorKey}_show`]) ? `${comparatorKey}_show` : comparatorKey
              if( !acc.includes(searchKey) ){
                acc.push(searchKey)
              }
            }
          })

          return acc
        }, [])

        let bottomRows = [];

        if(this.selectable){
          let inputRow = {comparator_name: ''}

          this.items.forEach( (item) => {
            inputRow[item.name] = '__input'
          })

          bottomRows.push(inputRow)
        }

        return commonComparators.map((comparator) => {
          let rowData = {}
          let comparisonValue = null

          if(__isObject(comparator)){
            comparisonValue = comparator[comparator._comparatorField]

            if(__isset(comparator._comparatorItemClasses)){
              comparisonValue = `<span class="${comparator._comparatorItemClasses}">${comparisonValue}</span>`
            }
          } else{
            let rec = null

            if(( rec = find(this.comparators, ['key', comparator]) ) && __isset(rec.title)){
              comparisonValue = rec.title
            } else {
              let match = comparator.match(/(\w+)?(_show)/)

              if(match) {
                comparisonValue = this.$headline(match[1])
              }else {
                comparisonValue = this.$headline(comparator)
              }

            }

            if( __isset(rec.itemClasses)){
              comparisonValue = `<span class="${rec.itemClasses}"> ${comparisonValue} </span>`
            }

          }
          rowData.comparator_name = comparisonValue

          this.items.forEach((item) => {
            if(__isObject(comparator)){
              let found = find( item[this.comparatorField], ['id', comparator.id] )

              if(found){
                let value = get(found, this.comparatorValue, 'unknown')

                if(value === 'unknown'){
                  console.error(`${this.comparatorField} not found for ${this.comparatorValue}`)
                  value = ''
                }
                rowData[item.name] = value

                // rowData[item.name] = found.pivot.active == '1'
                //   ? '<span class="mdi mdi-check text-info font-weight-bold"></span>'
                //   : '<span class="mdi mdi-close text-error font-weight-bold"></span>'
              }else{
                rowData[item.name] = ''
              }
            }else {
              let value = item[comparator]
              rowData[item.name] = value
              let rec = null
              if(( rec = find(this.comparators, ['key', comparator]) ) && __isset(rec.itemClasses)){
                rowData[item.name] = `<span class="${rec.itemClasses}"> ${value} </span>`
              }
            }
          })

          return rowData
        }).concat(bottomRows)
      }
    },
    watch: {
      input(val, old){

      },
      modelValue(val, old){

      }
    },
  }
</script>

<style scoped lang="sass">
  $borderWidth: 1px
  $borderColor: rgba(var(--v-theme-primary), .7 )
  $stripedColor: rgba(var(--v-theme-primary), .1 )
  $rowHeight: 40px


  .v-input-comparison-table
    .v-table
      --v-table-header-height: 50px
      --v-table-row-height: $rowHeight

    .v-input__control
      display: block

    th:has(> .v-input-comparison-table__header--selected)
      background-color: rgb(var(--v-theme-primary)) !important
      color: rgb(var(--v-theme-on-primary)) !important
    &--striped
      tr
        &:nth-of-type(2n)
          background-color: $stripedColor //TODO: table action border must be variable

    &--highlighted
      .v-table__wrapper
        > table
          tr
            &:first-child
              > th
                &:not(:first-child)
                  color: $borderColor
                  text-transform: uppercase
                  border-top: $borderWidth solid $borderColor !important
                  border-top-left-radius: 8px !important
                  border-top-right-radius: 8px !important
                  border-bottom: $borderWidth solid $borderColor !important
                  // +smooth-wave-border( $position: bottom, $wave-height: 5px, $color: rgba(var(--v-theme-primary), 1), $border-width: 1px )
                  // +wavy-border( $position: bottom, $wave-height: 15px, $wave-width: 50%,$border-width: 1px ,$color: rgba(var(--v-theme-primary), 1))
                  // +sine-wave-border( $position: bottom, $wave-height: 20px,$color: $borderColor, $border-width: 1px, $amplitude: 4, $height: 25px, $phase: -5)
            > td, th
              border-bottom: unset !important
              &:not(:first-child)
                // border: 1px solid rgba(var(--v-theme-primary), 0.5 )
                border-left: $borderWidth solid $borderColor
              &:last-child
                border-right: $borderWidth solid $borderColor
            &:last-child
              > td
                &:not(:first-child)
                  border-bottom: $borderWidth solid $borderColor !important

    .v-data-table--fixed-selectable
      .v-table__wrapper
        > table
          > tbody
            // fixed last tr of tbody just like vuetify datatable fixed header
            tr:last-child
              position: sticky
              bottom: 0
              left: 0
              right: 0
              z-index: 1000
              background-color: white
              border-top: $borderWidth solid $borderColor


</style>

<style lang="scss">

</style>
