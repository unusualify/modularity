<template>
  <v-input
    v-model="input"
    hideDetails="auto"
    :disabled="disabled"
    class="v-input-checklist"
    >
    <template v-slot:default="defaultSlot">
      <div class="v-input-checklist__field d-flex">
        <div v-if="label"
          class="d-flex flex-column"
          :style="[
            !flexColumn ? 'flex: 0 1 25%;' : ''
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
        <v-divider v-if="label || subtitle" vertical class="mr-4"></v-divider>
        <v-row v-if="isTreeview">
          <v-col lg="6" md="8" sm="12">
            <v-list >
              <template
                v-for="(group, key) in groupedItems"
                :key="`checkbox-${key}`">
                <template v-if="$isset(group.items) && group.items.length > 0">
                  <v-list-group
                    class="pl-0"
                    collapse-icon=""
                    expand-icon=""
                    >
                    <template v-slot:activator="{ props, isOpen }">
                      <v-checkbox
                        class="ue-checklist-checkbox"
                        :label="group[`${itemTitle}`]"
                        color="success"
                        hide-details
                        :indeterminate="isIndeterminateGroup(group)"
                        density="compact"
                        :modelValue="isAllSelected(group)"
                        @update:modelValue="updatedParent($event, group)"
                        :readonly="isMandatoryItem(group)"
                        >
                        <template v-slot:prepend>
                          <v-icon
                            v-bind="props"
                            :icon="!isOpen ? '$expand' : '$collapse'"
                            >
                          </v-icon>
                        </template>
                      </v-checkbox>
                      <!-- <v-list-item
                        class="pl-0"
                      >
                        <template v-slot:default="{isActive, isSelected, isIndeterminate, select}">

                        </template>
                      </v-list-item> -->
                    </template>

                    <v-list-item
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
                        :readonly="isMandatoryItem(item)"
                        >
                      </v-checkbox>
                    </v-list-item>

                  </v-list-group>
                </template>
                <template v-else>
                  <v-list-item
                    class="pl-0"
                    >
                    <v-checkbox
                      v-model="input"
                      :label="group[`${itemTitle}`]"
                      :value="group[`${itemValue}`]"
                      :disabled="!canSelectMore() && !input.includes(group[itemValue])"
                      :readonly="isMandatoryItem(group)"
                      color="success"
                      hide-details
                      density="compact"
                    />
                    <!-- <template v-slot:default="{isActive, isSelected, isIndeterminate, select}">
                    </template> -->
                  </v-list-item>

                </template>
              </template>
            </v-list>
          </v-col>
        </v-row>
        <v-row v-else
            :style="[
              !flexColumn ? 'flex: 1 0 60%;' : ''
            ]"
          >
          <template v-for="(item, index) in items"
              :key="`checkbox-${index}`">
              <v-col v-bind="checkboxCol"
                class="pb-0 pr-0 "
                >
                <div
                  :class="getCheckboxContainerClasses(item)"
                >
                  <span v-if="checkboxOnRight" :class="[($attrs.disabled ?? false) || (!canSelectMore() && !input.includes(item[itemValue])) ? 'v-input-checklist__label--disabled' : '']">{{ item[itemTitle] }}</span>
                  <v-spacer v-if="checkboxOnRight"></v-spacer>
                  <v-checkbox
                    data-test="checkbox"
                    v-model="input"
                    :disabled="($attrs.disabled ?? false) || (!canSelectMore() && !input.includes(item[itemValue]))"
                    :value="item[itemValue]"
                    :color="checkboxColor"
                    hide-details
                    :label="item[itemTitle]"
                    :class="getCheckboxClasses(item)"
                    :readonly="isMandatoryItem(item)"
                  >
                    <template v-if="checkboxOnRight" #label>
                      <span></span>
                    </template>
                  </v-checkbox>
                </div>
              </v-col>
              <!-- <v-spacer></v-spacer> -->
              <v-responsive v-if="index % 4 == 3" width="100%"></v-responsive>
          </template>
        </v-row>
      </div>
    </template>
  </v-input>
</template>

<script>
  import { computed } from 'vue'
  import { useInput, makeInputProps, makeInputEmits } from '@/hooks'

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
      }
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
            input = [
              ...new Set([
                ...(Array.isArray(input) ? input : []),
                ...mandatoryItems.map((item) => item[props.itemValue])
              ])
            ]
          }
        }

        if(maxSelectable.value > 1 && input.length > maxSelectable.value){
          input = input.sort((a, b) => a - b).slice(0, maxSelectable.value)
          context.emit('update:modelValue', input)
        }
        return input
      }

      return {
        ...useInput(props, {
          ...context,
          initializeInput
        }),
        maxSelectable
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
        return __data_get(item, this.mandatory, false)
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
          const splitted = this.items[i].name.split(this.chunkCharacter)
          if (splitted.length > 1) {
            const groupName = splitted[0]
            const permissionName = splitted[1]
            if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
              if (__isset(groups[groupName].id)) delete groups[groupName].id
              groups[groupName].items.unshift({
                id: this.items[i].id,
                name: this.$lodash.startCase(this.$lodash.camelCase(permissionName))
              })
            } else {
              groups[groupName] = {
                name: this.$lodash.startCase(this.$lodash.camelCase(groupName)),
                items: [{
                  id: this.items[i].id,
                  name: this.$lodash.startCase(this.$lodash.camelCase(permissionName))
                }]
              }
            }
          } else {
            const groupName = 'alpha'
            if (Object.prototype.hasOwnProperty.call(groups, groupName)) {
              if (__isset(groups[groupName].id)) delete groups[groupName].id
              groups[groupName].items.unshift({
                id: this.items[i].id,
                name: this.$lodash.startCase(this.$lodash.camelCase(this.items[i].name))
              })
            } else {
              groups[groupName] = {
                name: this.$t('General'),
                items: [{
                  id: this.items[i].id,
                  name: this.$lodash.startCase(this.$lodash.camelCase(this.items[i].name))
                }]
              }
            }

            // groups[this.items[i].name] = {
            //   id: this.items[i].id,
            //   name: this.$lodash.startCase(this.$lodash.camelCase(this.items[i].name))
            // }
          }
        }

        const array = Object.values(groups)
        array.sort(function (left, right) {
          return left.hasOwnProperty('items') ? 1 : right.hasOwnProperty('items') ? -1 : 0
        })

        return array
      },
      disabledCheckbox() {
        return this.$attrs.disabled || (!this.canSelectMore() && !Array.isArray(this.input));
      },
      hasMandatoryItems() {
        return this.items.some((item) => __data_get(item, this.mandatory, false))
      },
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
