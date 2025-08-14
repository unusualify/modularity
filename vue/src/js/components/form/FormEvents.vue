<template>
  <template v-for="event in events" :key="event.name">
    <v-tooltip
      :disabled="$isset(event.tooltip) || event.tooltip === false"
      :location="event.tooltipLocation ?? 'top'"
    >
      <template v-slot:activator="tooltipActivatorScope">
        <v-switch v-if="event.type === 'switch'"
          v-bind="{...$lodash.omit(event, 'label'), ...props}"
          hide-details
          :modelValue="input[event.name] ?? event.default ?? false"
          @update:modelValue="updateValue(event.name, $event)"
        />
        <ue-recursive-stuff v-else-if="event.viewOnlyComponent"
          :configuration="event.viewOnlyComponent"
          :bind-data="{
            ...input,
            ...formItem
          }"
          v-bind="tooltipActivatorScope.props"
        />
        <component v-else-if="event.component"
          :is="event.component"
          v-bind="{...tooltipActivatorScope.props, ...getEventProps(event)}"
          :modelValue="input[event.name] ?? event.default ?? false"
          @update:modelValue="updateValue(event.name, $event)"
        />
        <v-menu v-else-if="event.items && Array.isArray(event.items) && event.items.length"
          :close-on-content-click="false"
          transition="scale-transition"
          offset-y
        >
          <template v-slot:activator="menuActivatorScope">
            <v-btn
              variant="outlined"
              append-icon="mdi-chevron-down"
              v-bind="menuActivatorScope.props"
            >
              {{ getEventActiveLabel(event) }}
            </v-btn>
          </template>

          <v-list>
            <v-list-item
              v-for="(item, index) in event.items"
              :key="item.id"
              @click="updateValue(event.name, item[event.itemValue])"
            >
              <v-list-item-title>
                {{ item.name }}
                <v-icon v-if="$isset(modelValue[event.name]) && item[event.itemValue] === modelValue[event.name]"
                  size="small"
                  icon="$check"
                  color="primary"
                >
                </v-icon>
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
      <span>{{ event.tooltip ?? event.label }}</span>
    </v-tooltip>

  </template>
</template>

<script>
export default {
  name: 'FormEvents',
  emits: ['update:modelValue'],
  props: {
    events: {
      type: Array,
      required: true
    },
    modelValue: {
      type: Object,
      required: true
    },
    formItem: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    input: {
      get() {
        return this.modelValue ?? {}
      },
      set(value) {
        this.$emit('update:modelValue', value)
      }
    }
  },
  methods: {
    updateValue(key, value) {
      this.input = { ...this.modelValue, [key]: value };
      // this.$emit('update:modelValue', newModel);
    },
    getEventActiveLabel(event) {
      const item = event.items.find(item => item[event.itemValue] ===  (window.__isset(this.input[event.name]) ? this.input[event.name] : -1))

      return item ? item[event.itemTitle] : event.label
    },
    getEventProps(event) {
      return this.$lodash.omit(event, [
        'type',
        'name',
        'tooltip',
        'conditions',
        'scopeRole',
        'allowedRoles',
        'tooltipLocation',
      ])
    }
  }
}
</script>
