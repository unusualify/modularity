<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :disabled="disabled"
    class="v-input-checklist"
    >
    <template v-slot:default="defaultSlot">
      <div
        :class="[
          'v-input-checklist__field d-flex',
          flexColumn && $vuetify.display.mdAndUp ? 'flex-wrap' : 'flex-wrap'
        ]"
        style="max-width: 100%;"
      >
        <div v-if="label"
          class="d-flex flex-column"
          :style="[
            'max-width: 100%;',
            (flexColumn && $vuetify.display.mdAndUp) ? 'flex: 0 1 30%;' : 'flex: 1 0 100%;'
          ]"
        >
          <ue-title v-if="label" padding="x-3" data-test="title" :color="labelColor" transform="none">
            {{ label }}
          </ue-title>
          <ue-title v-if="subtitle"
            padding="x-3"
            type="caption"
            weight="medium"
            transform="none"
            :color="subtitleColor"
            data-test="title"
          >
            {{ subtitle }}
          </ue-title>
        </div>

        <v-divider v-if="(flexColumn && $vuetify.display.mdAndUp) && (label || subtitle)" vertical class="mr-4"></v-divider>

        <!-- treeview -->
        <v-row v-if="isTreeview"
          :style="[
            flexColumn && $vuetify.display.mdAndUp ? 'flex: 1 0 70%;' : 'flex: 1 1;'
          ]"
        >
          <v-col v-bind="{...treeviewCols}">

            <v-list v-model:opened="openedGroups">

              <template
                v-for="(group, key) in groupedItems"
                :key="`checkbox-${key}`"
              >
                <!-- group items -->
                <template v-if="$isset(group.items) && group.items.length > 0">
                  <v-list-group
                    class="pl-0"
                    collapse-icon=""
                    expand-icon=""
                    :value="group.name"
                    >

                    <!-- group expand activator -->
                    <template v-slot:activator="{ props, isOpen }">
                      <v-checkbox
                        v-if="!noGroupAllSelectable"
                        class="ue-checklist-checkbox"
                        :label="group[`${itemTitle}`]"
                        color="success"
                        hide-details
                        :indeterminate="isIndeterminateGroup(group)"
                        density="compact"
                        :modelValue="isAllSelected(group)"
                        @update:modelValue="updatedParent($event, group)"
                        :readonly="isMandatoryItem(group) || readonly"
                      >
                        <template v-slot:prepend>
                          <v-icon
                            v-if="!chunkField"
                            v-bind="props"
                            :icon="!isOpen ? '$expand' : '$collapse'"
                            >
                          </v-icon>
                        </template>
                      </v-checkbox>
                      <ue-title v-else
                        :text="group[`${itemTitle}`]"
                        type="body-1"
                        color="grey-darken-5"
                        weight="medium"
                        justify="space-between"
                        v-bind="props"
                      >
                        <!-- {{ titleSerialized }} -->
                        <template v-slot:right>
                          <div class="d-flex align-center">
                            <v-icon
                              :icon="!isOpen ? '$expand' : '$collapse'"
                              >
                            </v-icon>
                          </div>
                        </template>
                      </ue-title>
                      <v-divider v-if="hasGroupBottomDivider" class="mt-0"></v-divider>
                    </template>

                    <!-- list items -->
                    <v-list-item v-if="chunkField"
                      style="padding-inline-start: 0px !important;"
                    >
                      <v-row
                        no-gutters
                        :style="[
                          !flexColumn ? 'flex: 1 0 60%;' : ''
                        ]"
                      >
                        <v-col
                          v-for="(item, index) in group.items"
                          :key="`checkbox-${index}`"
                          v-bind="checkboxCol"
                          class="pb-0 pr-0 "
                          >
                          <div
                            :class="getCheckboxContainerClasses(item)"
                          >
                            <!-- checkbox is on right -->
                            <span v-if="checkboxOnRight" :class="[($attrs.disabled ?? false) || (!canSelectMore() && !input.includes(item[itemValue])) ? 'v-input-checklist__label--disabled' : '']">{{ item[itemTitle] }}</span>
                            <v-spacer v-if="checkboxOnRight"></v-spacer>

                            <!-- checkbox -->
                            <v-checkbox
                              data-test="checkbox"
                              v-model="input"
                              :disabled="($attrs.disabled ?? false) || (!canSelectMore() && !input.includes(item[itemValue]))"
                              :value="item[itemValue]"
                              :color="checkboxColor"
                              hide-details
                              :label="item[itemTitle]"
                              :class="getCheckboxClasses(item)"
                              :readonly="isMandatoryItem(item) || isProtected(item[itemValue]) || readonly"
                            >
                              <!-- checkbox is on right -->
                              <template v-if="checkboxOnRight" #label>
                                <span></span>
                              </template>
                            </v-checkbox>

                          </div>
                        </v-col>
                      </v-row>
                    </v-list-item>

                    <v-list-item v-else
                      v-for="(item, i) in group.items"
                      :key="`checkbox-${i}`"
                      class="pl-0"
                    >
                      <v-checkbox
                        class="ue-checklist-checkbox"
                        v-model="input"
                        :label="item[`${itemTitle}`]"
                        :value="item[`${itemValue}`]"
                        :disabled="!canSelectMore() && !input.includes(item[itemValue])"
                        color="success"
                        hide-details
                        density="compact"
                        :readonly="isMandatoryItem(item) || readonly"
                        >
                      </v-checkbox>
                    </v-list-item>

                  </v-list-group>
                </template>

                <!-- single item -->
                <template v-else>
                  <v-list-item
                    class="pl-0"
                    >
                    <v-checkbox
                      v-model="input"
                      :label="group[`${itemTitle}`]"
                      :value="group[`${itemValue}`]"
                      :disabled="!canSelectMore() && !input.includes(group[itemValue])"
                      :readonly="isMandatoryItem(group) || isProtected(group[itemValue]) || readonly"
                      color="success"
                      hide-details
                      density="compact"
                    />
                  </v-list-item>

                </template>

              </template>

            </v-list>
          </v-col>
        </v-row>

        <!-- standard checkbox list -->
        <v-row v-else
          :style="[
            flexColumn && $vuetify.display.mdAndUp ? 'flex: 1 1 60%;' : 'flex: 1 1;'
          ]"
        >
          <template v-for="(item, index) in flattenedItems"
              :key="`checkbox-${index}`">
              <v-col v-bind="checkboxCol"
                class=""
                >
                <v-input-checkbox-card
                  v-if="isCard"
                  v-model="input"
                  :value="item[itemValue]"
                  :class="[getCheckboxClasses(item), 'h-100']"
                  :color="checkboxColor"
                  :title="item[itemTitle]"
                  :description="item.description"
                  :disabled="($attrs.disabled ?? false) || (!canSelectMore() && !input.includes(item[itemValue]))"
                  :readonly="isMandatoryItem(item) || isProtected(item[itemValue]) || readonly"
                  :checkboxOnRight="checkboxOnRight"
                  :stats="getCardStats(item)"
                />
                <div
                  v-else
                  :class="getCheckboxContainerClasses(item)"
                >
                  <span v-if="checkboxOnRight" :class="[($attrs.disabled ?? false) || (!canSelectMore() && !input.includes(item[itemValue])) ? 'v-input-checklist__label--disabled' : '']">{{ item[itemTitle] }}</span>
                  <v-spacer v-if="checkboxOnRight"></v-spacer>
                  <v-checkbox
                    data-test="checkbox"
                    v-model="input"
                    :value="item[itemValue]"
                    :disabled="($attrs.disabled ?? false) || (!canSelectMore() && !input.includes(item[itemValue]))"
                    :color="checkboxColor"
                    hide-details
                    :label="item[itemTitle]"
                    :class="getCheckboxClasses(item)"
                    :readonly="isMandatoryItem(item) || isProtected(item[itemValue]) || readonly"
                  >
                    <template v-if="checkboxOnRight" #label>
                      <span></span>
                    </template>
                  </v-checkbox>
                </div>
              </v-col>
              <!-- <v-spacer></v-spacer> -->
              <!-- <v-responsive v-if="index % 4 == 3" width="100%"></v-responsive> -->
          </template>
        </v-row>

      </div>
    </template>
  </v-input>
</template>

<script>
  import { computed, ref, toRef } from 'vue'
  import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
  import { cloneDeep } from 'lodash-es'
  export default {
    name: 'v-input-checklist',
    emits: [...makeInputEmits],
    props: {
      ...makeInputProps(),
      color: {
        type: String,
        default: null
      },
      disabled: {
        type: Boolean,
        default: false
      },
      readonly: {
        type: Boolean,
        default: false
      },
      subtitle: {
        type: String,
        default: null
      },
      itemValue: {
        type: String,
        default: 'id'
      },
      itemTitle: {
        type: String,
        default: 'name'
      },
      items: {
        type: Array,
        default: () => []
      },
      orderBy: {
        type: String,
        default: null
      },
      orderByDirection: {
        type: String,
        default: 'asc'
      },
      checkboxColor: {
        type: String,
        default: 'primary'
      },
      isTreeview: {
        type: Boolean,
        default: false
      },
      chunkCharacter: {
        type: String,
        default: '_'
      },
      chunkTitleKey: {
        type: String,
        default: 'name'
      },
      chunkField: {
        type: String,
        default: null
      },
      labelColor: {
        type: String,
        default: 'grey-darken-1'
      },
      subtitleColor: {
        type: String,
        default: 'grey-darken-1'
      },
      flexColumn: {
        type: Boolean,
        default: true
      },
      checkboxHighlighted: {
        type: Boolean,
        default: false
      },
      checkboxPosition: {
        type: String,
        default: 'right'
      },
      checkboxCol: {
        type: Object,
        default: () => ({
          cols: 3
        })
      },
      noGroupAllSelectable: {
        type: Boolean,
        default: false
      },
      hasGroupBottomDivider: {
        type: Boolean,
        default: true
      },
      openAllGroups: {
        type: Boolean,
        default: false
      },
      closeAllGroups: {
        type: Boolean,
        default: false
      },
      rawRules: {
        type: [String, Array],
        default: null
      },
      max: {
        type: [Number, String],
        default: null
      },
      mandatory: {
        type: String,
        default: null
      },
      isCard: {
        type: Boolean,
        default: false
      },
      cardStats: {
        type: Array,
        default: () => []
      },
    },
    setup (props, context) {
      const maxSelectable = computed(() => {
        let max = props.max

        if(window.__isString(max)){
          max = parseInt(max)
        } else if(!max && window.__isString(props.rawRules)){
          max = props.rawRules.match(/max:\d+/)?.[0].split(':')[1]
        }
        return max ?? 999
      })

      const protectedValues = ref(props.protectInitialValue ? props.modelValue : [])

      const isProtected = (id) => {
        return protectedValues.value.includes(id)
      }

      const initializeInput = (input) => {
        if(props.mandatory){
          let mandatoryItems = props.items.filter((item) => __data_get(item, props.mandatory, false))

          if(props.max){
            let max = parseInt(props.max)
            if(mandatoryItems.length > max){
              mandatoryItems = mandatoryItems.slice(0, max)
            }
          }
          if(mandatoryItems.length > 0){
            // Check if mandatory items were not in previous input
            const previous = cloneDeep(input)
            const previousInput = Array.isArray(previous) ? previous : []
            const mandatoryItemsIds = mandatoryItems.map(item => item[props.itemValue])
            const missingMandatoryItems = mandatoryItemsIds.filter(id => !previousInput.includes(id))
            input = [
              ...new Set([
                ...(Array.isArray(input) ? input : []),
                ...mandatoryItemsIds
              ])
            ]

            if (missingMandatoryItems.length > 0) {
              context.emit('update:modelValue', input)
            }

          }
        }

        if(maxSelectable.value > 1 && input.length > maxSelectable.value){
          input = input.sort((a, b) => a - b).slice(0, maxSelectable.value)
          context.emit('update:modelValue', input)
        }
        return input
      }

      const openedGroups = ref([])

      const flattenedItems = computed(() => {
        let items = props.items

        if(props.orderBy){
          items.sort((a, b) => {
            if(props.orderByDirection === 'asc'){
              return a[props.orderBy].localeCompare(b[props.orderBy])
            } else {
              return b[props.orderBy].localeCompare(a[props.orderBy])
            }
          })
        }

        return items
      })

      return {
        ...useInput(props, {
          ...context,
          initializeInput
        }),
        openedGroups: toRef(openedGroups),
        maxSelectable,
        isProtected,
        flattenedItems
      }
    },

    methods: {
      isAllSelected (group) {
        const ids = group.items.map((item) => item.id)

        return ids.every(v => this.input.includes(v))
      },
      isIndeterminateGroup (group) {
        const ids = group.items.map((item) => item.id)

        return !ids.every(v => this.input.includes(v)) && ids.some(v => this.input.includes(v))
      },
      updatedParent (value, group) {
        const ids = group.items.map((item) => item.id)

        if (!value) {
          this.input = this.input.filter(function (id) {
            return !ids.includes(id)
          })
        } else {
          // Check if adding all items would exceed the limit
          if (this.maxSelectable) {
            const newItemsCount = ids.filter(id => !this.input.includes(id)).length;
            if (this.input.length + newItemsCount > this.maxSelectable) {
              return; // Don't add if it would exceed the limit
            }
          }

          ids.forEach((id) => {
            if (!this.input.includes(id)) {
              this.input.push(id)
            }
          })
        }
      },
      getCheckboxContainerClasses(item) {
        const isSelected = Array.isArray(this.input) && this.input.includes(item[this.itemValue]);
        return [
          this.checkboxOnRight ? 'd-flex align-center pl-4 pr-1 rounded-sm' : '',
          this.checkboxOnRight && isSelected ? 'checked' : '',
          this.checkboxOnRight && this.checkboxHighlighted && isSelected ? 'bg-grey-lighten-5 text-primary font-weight-bold' : ''
        ];
      },
      getCheckboxClasses(item) {
        const isSelected = Array.isArray(this.input) && this.input.includes(item[this.itemValue]);
        return [
          this.checkboxOnLeft ? 'rounded-sm' : '',
          this.checkboxOnLeft && isSelected ? 'checked' : '',
          this.checkboxOnLeft && this.checkboxHighlighted && isSelected ? 'bg-grey-lighten-5 text-primary font-weight-bold' : ''
        ];
      },
      canSelectMore() {
        return !this.disabled && (!this.maxSelectable || (Array.isArray(this.input) && this.input.length < this.maxSelectable));
      },
      isMandatoryItem(item) {
        return Boolean(__data_get(item, this.mandatory, false))
      },
      isGroupOpen(index) {
        if(this.openAllGroups){
          return true
        } else if(this.closeAllGroups){
          return false
        } else {
          return index === 0
        }
      },
      getCardStats(item) {
        return this.cardStats.map((stat) => {
          return {
            ...stat,
            value: item[stat.key]
          }
        })
      }
    },

    computed: {
      checkboxOnRight () {
        return this.checkboxPosition === 'right'
      },
      checkboxOnLeft () {
        return this.checkboxPosition === 'left'
      },
      groupedItems () {
        const groups = {}

        for (const i in this.items) {

          if(this.chunkField){
            const groupName = this.items[i][this.chunkField]
            const checklistTitle = this.items[i][this.chunkTitleKey]
            const item = this.items[i]
            if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
              // if (__isset(groups[groupName].id)) delete groups[groupName].id
              groups[groupName].items.unshift({
                id: item.id,
                name: checklistTitle
              })
            }else{
              groups[groupName] = {
                name: this.$lodash.startCase(this.$lodash.camelCase(groupName)),
                items: [{
                  id: item.id,
                  // name: this.$lodash.startCase(this.$lodash.camelCase(checklistTitle))
                  name: checklistTitle
                }]
              }
            }
          } else{
            const splitted = this.items[i][this.chunkTitleKey].split(this.chunkCharacter)

            if (splitted.length > 1) {
              const groupName = splitted[0]
              const checklistTitle = splitted[1]
              if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
                if (__isset(groups[groupName].id)) delete groups[groupName].id

                groups[groupName].items.unshift({
                  id: this.items[i].id,
                  name: this.$lodash.startCase(this.$lodash.camelCase(checklistTitle))
                })
              } else {

                groups[groupName] = {
                  name: this.$lodash.startCase(this.$lodash.camelCase(groupName)),
                  items: [{
                    id: this.items[i].id,
                    name: this.$lodash.startCase(this.$lodash.camelCase(checklistTitle))
                  }]
                }
              }
            } else {
              const groupName = 'alpha'
              if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
                if (__isset(groups[groupName].id)) delete groups[groupName].id
                groups[groupName].items.unshift({
                  id: this.items[i].id,
                  name: this.$lodash.startCase(this.$lodash.camelCase(this.items[i][this.chunkTitleKey]))
                })
              } else {
                groups[groupName] = {
                  name: this.$t('General'),
                  items: [{
                    id: this.items[i].id,
                    name: this.$lodash.startCase(this.$lodash.camelCase(this.items[i][this.chunkTitleKey]))
                  }]
                }
              }

              // groups[this.items[i].name] = {
              //   id: this.items[i].id,
              //   name: this.$lodash.startCase(this.$lodash.camelCase(this.items[i].name))
              // }
            }
          }
        }

        const array = Object.values(groups)

        array.sort(function (left, right) {
          return left.hasOwnProperty('items') ? 1 : (right.hasOwnProperty('items') ? -1 : 0)
        })

        if(this.closeAllGroups){
          this.openedGroups = []
        } else {
          this.openedGroups = !this.openAllGroups ? [array[0].name] : array.map((group) => group.name)
        }

        if(this.orderBy){
          for(const i in array){
            array[i].items.sort((a, b) => {
              if(this.orderByDirection === 'asc'){
                return a[this.orderBy].localeCompare(b[this.orderBy])
              } else {
                return b[this.orderBy].localeCompare(a[this.orderBy])
              }
            })
          }
        }

        return array
      },
      disabledCheckbox() {
        return this.$attrs.disabled || (!this.canSelectMore() && !Array.isArray(this.input));
      },
      hasMandatoryItems() {
        return this.items.some((item) => __data_get(item, this.mandatory, false))
      },
      treeviewCols() {
        return !this.chunkField ? {
          lg: 6,
          md: 8,
          sm: 12
        } : {
          cols: 12
        }
      }
    },

    created () {
    }
  }
</script>

<style lang="sass">
  .v-input-checklist
    .v-input-checklist__field
      width: 100%
    .v-input-checklist__label--disabled
      opacity: 0.5
    .v-input--horizontal .v-input__prepend
        margin-inline-end: 0px

</style>
