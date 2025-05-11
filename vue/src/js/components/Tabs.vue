<template>
  <v-tabs v-model="tab" align-tabs="center">
    <template v-for="element in elements" :key="`tab-${element[tabValue]}`" :value="element[tabValue]">
      <slot :name="`tab.${element[tabValue]}`">
        <v-tab :value="element[tabValue]">
          <span>{{ element[tabTitle] }}</span>
          <template v-slot:prepend>
            <slot :name="`tab.${element[tabValue]}.prepend`"></slot>
          </template>
          <template v-slot:append>
            <slot :name="`tab.${element[tabValue]}.append`"></slot>
          </template>
        </v-tab>
      </slot>
    </template>
  </v-tabs>
  <slot name="windows" v-bind="{active: tab}">
    <v-tabs-window v-model="tab">
      <v-tabs-window-item
        v-for="(item, n) in items"
        :key="`tab-window-${n}`"
        :value="item[tabValue]"
      >
        <slot :name="`window.${n}`" v-bind="{index: n, item: item, model: models[item[tabValue]]}">
          <RowFormat :elements="[{text: `${item[itemTitle]}`, col: {'cols': 4}}]"/>
        </slot>
      </v-tabs-window-item>
    </v-tabs-window>
  </slot>
</template>
<script>
  import RowFormat from '__components/labs/RowFormat.vue'

  export default {
    name: 'ue-tabs',
    emits: ['update:modelValue'],
    components: {
      RowFormat
    },
    props: {
      modelValue: {
        type: [String, Number]
      },
      tabValue: {
        type: String,
        default: 'id',
      },
      tabTitle: {
        type: String,
        default: 'name',
      },
      itemTitle: {
        type: String,
        default: 'name'
      },
      items: {
        type: Object,
        default: () => {
          return {
          }
        }
      }
    },
    data () {
      return {
        tab_: this.modelValue ?? Object.keys(this.items)[0] ?? this.items[0][this.tabValue] ?? null,
        models: {},
        elements: __isObject(this.items)
          ? Object.keys(this.items).map(key => ({
            [this.tabTitle]: key,
            ...this.items[key],
            [this.tabValue]: key,
          }))
          : this.items
      }
    },
    watch: {
      tab_: {
        handler (value, oldValue) {
          this.$emit('update:modelValue', value)
        }
      },
    },
    computed: {
      tab: {
        get(){
          return this.modelValue ?? Object.keys(this.items)[0] ?? this.items[0][this.tabValue] ?? null
        },
        set(val){
          this.$emit('update:modelValue', val)
        }
      },
    },
    created(){
      this.models = Object.keys(this.items).map((key, i) => {
        return []
      })
    }
  }
</script>
