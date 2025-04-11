<script setup>
  import { useAttrs, useSlots } from 'vue'
  import { useItemActions } from '@/hooks'
  import useGenerate from '@/hooks/utils/useGenerate.js'
  import useBadge from '@/hooks/utils/useBadge.js'

  const props = defineProps({
    actions: {
      type: Array,
      required: true
    },
  })

  const emits = defineEmits(['actionComplete'])
  const attrs = useAttrs()
  const slots = useSlots()

  const { generateButtonProps } = useGenerate()
  const { isBadge, badgeProps } = useBadge()

  const { handleAction, allActions, hasActions } = useItemActions(props, {
    ...attrs,
    ...slots,
    ...emits,
    actionItem: props.modelValue
  })
</script>

<template>
    <div :class="[
      (hasActions || $slots.prepend) ? 'd-flex flex-wrap ga-2' : ''
    ]"
  >
    <slot name="prepend"></slot>
    <template v-for="(action, key) in allActions">
      <v-tooltip
        :disabled="!action.icon || action.forceLabel"
        :location="action.tooltipLocation ?? 'top'"
      >
        <template v-slot:activator="{ props }">
          <v-switch v-if="action.type === 'publish'"
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
              />
            </v-badge>
            <v-btn v-else
              v-bind="{...generateButtonProps(action), ...props}"

              @click="() => console.log(generateButtonProps(action))"
            />
          </template>
        </template>
        <span>{{ action.tooltip ?? action.label }}</span>
      </v-tooltip>
    </template>
  </div>
</template>

