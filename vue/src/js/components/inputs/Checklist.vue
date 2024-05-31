<template>
  <div class="ue-checklist">
    <Title v-if="label" :classes="['pl-0 pt-0']" data-test="title">
      {{ label }}
    </Title>
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
                    color="success"
                    hide-details
                    density="compact"
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
    <v-row v-else align="start" justify="start" noGutters>
      <v-checkbox
        v-for="(item, index) in items"
        :key="`checkbox-${index}`"
        data-test="checkbox"
        v-model="input"
        :label="item[`${itemTitle}`]"
        :value="item[`${itemValue}`]"
        :color="checkboxColor"
        hide-details

        :class="[ ( Array.isArray(input) && input.includes(item[`${itemValue}`]) ) ? 'checked' : '']"
        >
      </v-checkbox>
    </v-row>
  </div>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput, makeInputProps } from '@/hooks'
import Title from '__components/Title.vue'

export default {

  name: 'v-custom-input-checklist',
  components: {
    Title
  },
  mixins: [InputMixin],
  props: {
    ...makeInputProps(),
    label: {
      type: String,
      default: ''
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
      default: 'success'
    },
    isTreeview: {
      type: Boolean,
      default: false
    },
    chunkCharacter: {
      type: String,
      default: '_'
    }
  },
  setup (props, context) {
    return {
      ...useInput(props, context)
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
        ids.forEach((id) => {
          if (!this.input.includes(id)) {
            this.input.push(id)
          }
        })
      }
    }
  },

  computed: {
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
    }
  },

  created () {
    // __log(this.items)
  }
}
</script>

<style lang="sass">
.ue-checklist
    .v-input--horizontal .v-input__prepend
        margin-inline-end: 0px

</style>
