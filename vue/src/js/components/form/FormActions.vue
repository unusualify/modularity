<template>
  <div class="d-flex flex-wrap ga-2 mr-2">
    <template v-for="(action, key) in showedActions">
      <v-tooltip
        v-if="action.type !== 'modal'"
        :disabled="!action.icon || action.forceLabel"
        :location="action.tooltipLocation ?? 'top'"
      >
        <template v-slot:activator="{ props }">
          <v-switch
            v-if="action.type === 'publish'"
            :modelValue="editedItem[action.key ?? 'published'] ?? action.default ?? false"
            @update:modelValue="handleAction(action)"
          />
          <v-btn
            v-else
            :icon="!action.forceLabel ? action.icon : null"
            :text="action.forceLabel ? action.label : null"
            :color="action.color"
            :variant="action.variant"
            :density="action.density ?? 'comfortable'"
            :size="action.size ?? 'default'"
            :rounded="action.forceLabel ? null : true"
            v-bind="props"
            @click="handleAction(action)"
          />
        </template>
        <span>{{ action.tooltip ?? action.label }}</span>
      </v-tooltip>
      <v-menu v-else-if="action.type === 'modal' && action.endpoint && action.schema"
        :close-on-content-click="false"
        open-on-hoverx
        transition="scale-transition"
      >
        <template v-slot:activator="{ props }">
          <v-btn
            :icon="!action.forceLabel ? action.icon : null"
            :text="action.forceLabel ? action.label : null"
            :color="action.color"
            :variant="action.variant"
            :density="action.density ?? 'comfortable'"
            :size="action.size ?? 'default'"
            :rounded="action.forceLabel ? null : true"
            v-bind="props"
          />
        </template>
        <v-sheet :style="$vuetify.display.mdAndDown ? {width: '70vw'} : {width: '40vw'}">
          <ue-form
            :ref="`extra-form-${key}`"
            :modelValue="createModel(action.schema)"
            @updatex:modelValue="$log($event)"
            :title="action.formTitle ?? null"
            :schema="action.schema"
            :action-url="action.endpoint.replace(':id', modelValue.id)"
            :valid="valids[key]"
            @update:valid="valids[key] = $event"
            has-divider
            has-submit
            button-text="Save"
            @submitted="$emit('actionComplete', { action })"
          />
        </v-sheet>
      </v-menu>
    </template>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useItemActions } from '@/hooks'
import { getModel } from '@/utils/getFormData'

export default {
  name: 'FormActions',
  emits: ['actionComplete'],
  props: {
    modelValue: {
      type: Object,
      required: true
    },
    isEditing: {
      type: Boolean,
      required: false
    },
    actions: {
      type: Object,
      required: true
    },

  },
  setup(props, context) {
    const { handleAction, showedActions } = useItemActions(props, {
      ...context,
      actionItem: props.modelValue
    })

    const valids = computed(() => showedActions.value.map(action => true))

    // __log(showedActions)
    const createModel = (schema) => {
      return getModel(schema, props.modelValue)
    }
    return {
      showedActions,
      handleAction,
      createModel,
      valids
    }
  },
}
</script>

