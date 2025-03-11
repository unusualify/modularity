<template>
  <div :class="[
      hasActions ? 'd-flex flex-wrap ga-4 py-4' : ''
    ]"
  >
    <template v-for="(action, key) in formattedActions">
      <v-tooltip
        v-if="action.type !== 'modalx'"
        :disabled="!action.icon || action.forceLabel"
        :location="action.tooltipLocation ?? 'top'"
      >
        <template v-slot:activator="{ props }">
          <v-switch
            v-if="action.type === 'publish'"
            :modelValue="editedItem[action.key ?? 'published'] ?? action.default ?? false"
            @update:modelValue="handleAction(action)"
            v-bind="{...action.componentProps, ...props}"
          />
          <ue-modal v-else-if="action.type === 'modal' && action.endpoint && action.schema"
            :close-on-content-click="false"
            transition="scale-transition"
            widthType="md"
            :use-model-value="false"
            v-bind="action.modalAttributes ?? {}"
          >
            <template v-slot:activator="modalActivatorScope">
              <v-badge v-if="isBadge(action)"
                :content="action.badge"
                :color="action.badgeColor ?? 'warning'"
                :text-color="action.badgeTextColor ?? 'white'"
              >
                <v-btn
                  v-bind="{
                    ...generateButtonProps(action),
                    ...modalActivatorScope.props,
                    ...props
                  }"
                />
              </v-badge>
              <v-btn v-else
                v-bind="{
                  ...generateButtonProps(action),
                  ...modalActivatorScope.props,
                  ...props
                }"
              />
            </template>

            <template v-slot:body="formModalBodyScope">

              <ue-form
                :ref="`extra-form-${key}`"

                :modelValue="createModel(action.schema)"
                :title="action.formTitle ?? null"
                :schema="action.schema"
                :action-url="action.endpoint.replace(':id', modelValue.id)"
                :valid="valids[key]"
                :is-editing="isEditing"

                :style="formModalBodyScope.isFullActive ? 'height: 90vh !important;' : 'height: 70vh !important;'"

                has-divider
                has-submit
                button-text="Save"

                @submitted="$emit('actionComplete', { action })"
                @update:valid="valids[key] = $event"

                @updatex:modelValue="$log($event)"

                v-bind="action.formAttributes ?? {}"
              />
            </template>

          </ue-modal>
          <template v-else-if="action.type !== 'modal'">
            <v-badge v-if="isBadge(action)"
              :content="action.badge"
              :color="action.badgeColor ?? 'warning'"
              :text-color="action.badgeTextColor ?? 'white'"
            >
              <v-btn
                v-bind="{...generateButtonProps(action), ...props}"
                @click="handleAction(action)"
              />
            </v-badge>
            <v-btn v-else
              v-bind="{...generateButtonProps(action), ...props}"
              @click="handleAction(action)"
            />
          </template>
        </template>
        <span>{{ action.tooltip ?? action.label }}</span>
      </v-tooltip>
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
    const { handleAction, formattedActions, hasActions } = useItemActions(props, {
      ...context,
      actionItem: props.modelValue
    })

    const valids = computed(() => formattedActions.value.map(action => true))

    const generateButtonProps = (action) => {
      return {
        ...(action.componentProps ?? {}),
        icon: !action.forceLabel ? action.icon : null,
        text: action.forceLabel ? action.label : null,
        color: action.color,
        variant: action.variant,
        density: action.density ?? 'comfortable',
        size: action.size ?? 'default',
        rounded: action.forceLabel ? null : true
      }
    }

    const isBadge = (action) => {
      if(!window.__isset(action.badge)){
        return false
      }

      let badge = action.badge

      if(window.__isString(badge)){
        badge = parseInt(badge)
      }

      return badge > 0
    }

    const createModel = (schema) => {
      return getModel(schema, props.modelValue)
    }

    return {
      hasActions,
      formattedActions,
      handleAction,

      valids,

      createModel,
      generateButtonProps,
      isBadge
    }
  },
}
</script>

