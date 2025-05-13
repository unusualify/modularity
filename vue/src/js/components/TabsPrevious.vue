<template>
  <v-tabs v-model="activeTab" align-tabs="center">
    <v-tab v-for="key in Object.keys(items)" :key="`tab-${key}`" :value="key">{{ key }}</v-tab>
  </v-tabs>
  <v-tabs-window v-model="activeTab">
    <v-tabs-window-item
      v-for="(key, n) in Object.keys(items)"
      :key="`tab-window-${key}`"
      :value="key"
      class="pa-theme"
    >
      <slot name="window" v-bind="{index: n, key: key, items: items[key], model: models[key]}">
        <template v-for="(item, i) in items[key]" :key="`window-row-${i}]`">
          <RowFormat :elements="[{text: `${item[itemTitle]}`, col: {'cols': 4}}]"/>
        </template>
        <slot name="windowItem">
        </slot>
      </slot>
    </v-tabs-window-item>
  </v-tabs-window>
</template>
<script>
  import RowFormat from '__components/labs/RowFormat.vue'
  export default {
    name: 'ue-tabs',
    components: {
      RowFormat
    },
    props: {
      modelValue: {
        type: String,
        default: null
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
        // tab: Object.keys(this.items)[0] ?? null,
        models: {},
      }
    },
    computed: {
      activeTab: {
        get() {
          return this.modelValue ?? Object.keys(this.items)[0] ?? null
        },
        set(val) {
          this.$emit('update:modelValue', val)
        }
      }
    },
    created(){
      this.models = Object.keys(this.items).map((key, i) => {
        return []
      })
    }
  }
</script>
