<template>
  <div :class="[
      hasActions ? 'd-flex flex-wrap ga-4 py-4' : ''
    ]"
  >
    <slot name="prepend" v-bind="{item: modelValue, isEditing}"></slot>
    <template v-for="(action, key) in allActions">
      <v-tooltip
        v-if="action.type !== 'modalx'"
        :disabled="!action.icon || action.forceLabel"
        :location="action.tooltipLocation ?? 'top'"
      >
        <template v-slot:activator="tooltipActivatorScope">
          <v-switch
            v-if="action.type === 'publish'"
            :modelValue="editedItem[action.key ?? 'published'] ?? action.default ?? false"
            v-bind="{...action.componentProps, ...tooltipActivatorScope.props}"

            :disabled="action.disabled ?? false"
            @update:modelValue="handleAction(action)"
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
                v-bind="badgeProps(action)"
              >
                <v-btn
                  v-bind="{
                    ...generateButtonProps(action),
                    ...modalActivatorScope.props,
                    ...tooltipActivatorScope.props
                  }"
                />
              </v-badge>
              <v-btn v-else
                v-bind="{
                  ...generateButtonProps(action),
                  ...modalActivatorScope.props,
                  ...tooltipActivatorScope.props
                }"
              />
            </template>

            <template v-slot:body="formModalBodyScope">
              <v-card class="fill-height d-flex flex-column">
                <ue-form
                  :ref="`extra-form-${key}`"

                  :modelValue="createModel(action.schema, action)"
                  :title="action.formTitle ?? null"
                  :schema="action.schema"
                  :action-url="action.endpoint.replace(':id', modelValue.id)"
                  :valid="valids[key]"
                  :is-editing="action.isEditing ?? isEditing"

                  :style="formModalBodyScope.isFullActive ? 'height: 90vh !important;' : 'height: 70vh !important;'"

                  fill-height
                  scrollable

                  has-divider
                  has-submit
                  button-text="Save"

                  @submitted="$emit('actionComplete', { action })"
                  @update:valid="valids[key] = $event"

                  @updatex:modelValue="$log($event)"

                  v-bind="action.formAttributes ?? {}"
                />
              </v-card>
            </template>

          </ue-modal>
          <template v-else-if="action.type !== 'modal'">
            <v-badge v-if="isBadge(action)"
              v-bind="badgeProps(action)"
            >
              <v-btn
                v-bind="{
                  ...generateButtonProps(action),
                  ...tooltipActivatorScope.props,
                }"
                @click="handleAction(action)"
              />
            </v-badge>
            <v-btn v-else
              v-bind="{...generateButtonProps(action), ...tooltipActivatorScope.props}"
              @click="handleAction(action)"
            />
          </template>
        </template>
        <span>{{ action.tooltip ?? action.label }}</span>
      </v-tooltip>
    </template>
    <slot name="append" v-bind="{item: modelValue, isEditing}"></slot>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useItemActions } from '@/hooks'
import { getModel } from '@/utils/getFormData'
import useGenerate from '@/hooks/utils/useGenerate'
import useBadge from '@/hooks/utils/useBadge'

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
    const { handleAction, allActions, hasActions } = useItemActions(props, {
      ...context,
      actionItem: props.modelValue
    })
    const { generateButtonProps } = useGenerate()
    const { isBadge, badgeProps } = useBadge()
    const valids = computed(() => allActions.value.map(action => true))


    const createModel = (schema, action) => {
      const model = getModel(schema, props.modelValue)
      if (action.isEditing === false) {
        delete model.id
      }
      return model
    }

    return {
      hasActions,
      allActions,
      handleAction,

      valids,

      createModel,
      generateButtonProps,
      isBadge,
      badgeProps
    }
  },
}
</script>

