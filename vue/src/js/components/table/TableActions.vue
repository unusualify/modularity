<script setup>
  import { computed, useAttrs, useSlots, watch } from 'vue'
  import { useItemActions } from '@/hooks'
  import useGenerate from '@/hooks/utils/useGenerate.js'
  import useBadge from '@/hooks/utils/useBadge.js'

  const props = defineProps({
    actions: {
      type: Array,
      required: true
    },
  })

  const emit = defineEmits(['actionComplete', 'update:loading'])
  const attrs = useAttrs()
  const slots = useSlots()

  const { generateButtonProps } = useGenerate()

  // First, create references to all the generated props at the top level
  const generatedPropsMap = {}
  props.actions.forEach((action, key) => {
    // if(generatedPropsMap[key]) return
    const { generatedButtonProps } = useGenerate(action)
    generatedPropsMap[key] = generatedButtonProps
  })
  // Then use the references in the computed property
  const buttonsProps = computed(() => {
    return Object.keys(generatedPropsMap).reduce((acc, key) => {
      acc[key] = generatedPropsMap[key].value
      return acc
    }, {})
  })

  const { isBadge, badgeProps } = useBadge()

  const { handleAction, allActions, hasActions, loading, isActionLoading } = useItemActions(props, {
    attrs,
    slots,
    emit,
    actionItem: props.modelValue
  })

  watch(loading, (value) => {
    emit('update:loading', value)
  }, { immediate: true })

  const getActionButtonProps = (action, key, tooltipProps = {}) => ({
    ...buttonsProps.value[key],
    ...tooltipProps,
    loading: isActionLoading(action),
    disabled: (buttonsProps.value[key]?.disabled ?? false) || loading.value,
  })

  const getModalButtonProps = (action, modalActivatorScope, tooltipProps = {}) => ({
    ...generateButtonProps(action),
    ...modalActivatorScope.props,
    ...tooltipProps,
    disabled: (action.disabled ?? action.componentProps?.disabled ?? false) || loading.value,
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
                  v-bind="getModalButtonProps(action, modalActivatorScope, props)"
                />
              </v-badge>
              <v-btn v-else
                v-bind="getModalButtonProps(action, modalActivatorScope, props)"
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
                v-bind="getActionButtonProps(action, key, props)"
                @click="handleAction(action)"
              />
            </v-badge>
            <v-btn v-else
              v-bind="getActionButtonProps(action, key, props)"
              @click="handleAction(action)"
            />
          </template>
        </template>
        <span>
          <template v-if="action.tooltipItems?.length">
            <div class="text-caption mb-1 font-weight-medium">
              {{ action.tooltip ?? action.label }}
            </div>
            <ul class="text-caption ps-3 mb-0">
              <li
                v-for="(f, i) in action.tooltipItems"
                :key="i"
              >
                {{ f.label }}
              </li>
            </ul>
          </template>
          <template v-else>
            {{ action.tooltip ?? action.label }}
          </template>
        </span>
      </v-tooltip>
    </template>
    <slot name="append"></slot>
  </div>
</template>

