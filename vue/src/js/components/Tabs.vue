<template>
  <v-tabs v-model="tab" align-tabs="center">
    <v-tab v-if="tabValue=='_key'" v-for="key in Object.keys(items)" :key="`tab-object-${key}`" :value="key">{{ key }}</v-tab>
    <v-tab v-else v-for="item in items" :key="`tab-array-${item[tabValue]}`" :value="item[tabValue]">{{ item[tabTitle] }}</v-tab>
  </v-tabs>
  <slot name="windows" v-bind="{active: tab}">
    <v-tabs-window v-model="tab">
      <v-tabs-window-item
        v-if="tabValue=='_key'"
        v-for="(key, n) in Object.keys(items)"
        :key="`tab-window-object-${key}`"
        :value="key"
        class="pa-theme"
      >
        <slot name="window" v-bind="{index: n, key: key, items: items[key], model: models[key]}">
          <template v-for="(item, i) in items[key]" :key="`window-row-${i}]`">
            <slot name="window-item" v-bind="{index: n, item: item}">
              <RowFormat :elements="[{text: `${item[itemTitle]}`, col: {'cols': 4}}]"/>
            </slot>
          </template>
        </slot>
      </v-tabs-window-item>
      <v-tabs-window-item
        v-else
        v-for="(item, n) in items"
        :key="`tab-window-array-${key}`"
        :value="item[tabValue]"
      >
        <slot :name="`window.${n}`" v-bind="{index: n, item: item}">
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
        default: '_key',
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
      }
    },
    watch: {
      tab_: {
        handler (value, oldValue) {
          __log('tab watcher', value)
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
