<template>
  <div
    :class="[
      'ue-form',
    ]"
    :style="{height: fillHeight ? ($vuetify.display.mdAndDown ? `calc(97vh - 64px)` : `calc(97vh)` ) : ''}">
    <v-form
      :id="id"
      ref="VForm"
      :action="actionUrl"
      method="POST"
      v-model="validModel"
      @submit="submit"
      :class="formClasses"
      >
      <input v-if="!async" type="hidden" name="_token" :value="$csrf"/>

      <!-- Header Section -->
      <div :class="[
        (hasDivider || title) ? 'px-1' : '',
        scrollable ? 'flex-grow-0' : '',
        'd-flex flex-row pb-2'
      ]">
        <ue-title v-if="!noTitle && title"
          padding="a-0"
          align="start"
          justify="start"
          class="flex-1-1-100"
          v-bind="titleOptions"
        >
          <template v-slot:default>
            <slot name="header.left" v-bind="{title: titleSerialized, subtitle: subtitle ?? null, model: model, schema: inputSchema, formItem}">
              <span>
                {{ titleSerialized }}
                <ue-title v-if="subtitle"
                  :text="subtitle"
                  type="caption"
                  weight="medium"
                  color="grey-darken-1"
                  transform="none"
                  padding="a-0"
                />
              </span>
              <!-- subtitle -->
            </slot>
          </template>
          <template v-slot:right>
            <div class="d-flex mt-2 mt-md-0 ">
              <!-- Title Center Form Actions -->
              <FormActions v-if="actionsPosition == 'title-center' && isEditing"
                :modelValue="formItem"
                :actions="actions"
                :is-editing="isEditing"
                @action-complete="$emit('actionComplete', $event)"
              >
                <template #prepend>
                  <slot name="actions.prepend"></slot>
                </template>
                <template #append>
                  <slot name="actions.append"></slot>
                </template>
              </FormActions>

              <!-- Slot for headerCenter -->
              <slot name="headerCenter">

              </slot>

              <!-- Title Right Form Actions -->
              <FormActions v-if="actionsPosition == 'title-right' && isEditing"
                :modelValue="formItem"
                :actions="actions"
                :is-editing="isEditing"
                @action-complete="$emit('actionComplete', $event)"
              >
                <template #prepend="actionsScope">
                  <slot name="actions.prepend" v-bind="actionsScope"></slot>
                </template>
                <template #append="actionsScope">
                  <slot name="actions.append" v-bind="actionsScope"></slot>
                </template>
              </FormActions>

              <FormEvents v-if="formEventSchema && formEventSchema.length && model"
                :events="formEventSchema"
                v-model="model"
                :form-item="formItem"
              />
            </div>
          </template>
        </ue-title>
        <div :class="[
          'flex-1-0 d-flex',
          (hasTraslationInputs && languages && languages.length && languages.length > 1
            || (hasAdditionalSection && $vuetify.display.mdAndDown)
            || ($slots['header.right'])
          ) ? 'pl-2' : ''
        ]">
          <!-- Language Selector -->
          <v-chip-group v-if="hasTraslationInputs && languages && languages.length && languages.length > 1"
            :modelValue="currentLocale.value"
            @update:modelValue="updateLocale($event)"
            selected-class="bg-primary"
            mandatory
          >
            <v-chip
              v-for="language in languages"
              :key="language.value"
              :text="language.shortlabel"
              :value="language.value"
              variant="outlined"
            ></v-chip>
          </v-chip-group>

          <!-- Mobile dialog/modal for right section -->
          <v-btn v-if="hasAdditionalSection && $vuetify.display.mdAndDown"
            density="compact"
            :rounded="true"
            size="default"
            icon="mdi-book-information-variant"
            class=""
            @click="showAdditionalSectionDialog = true"
          >
          </v-btn>
          <!-- Slot for headerRight -->
          <slot name="header.right">

          </slot>
        </div>
      </div>

      <v-divider v-if="hasDivider" class="pb-2"></v-divider>

      <!-- Scrollable Content Section -->
      <div :class="['d-flex', scrollable ? 'flex-grow-1 overflow-hidden mr-n5' : '']">
        <div :class="['w-100 d-flex', scrollable ? 'overflow-y-auto pr-3' : '']"
        >
          <div class="flex-grow-1 px-1">
            <!-- Top Form Actions -->
            <FormActions v-if="actionsPosition == 'top' && isEditing"
              :modelValue="formItem"
              :actions="actions"
              :is-editing="isEditing"
              @action-complete="$emit('actionComplete', $event)"
            >
              <template #prepend="actionsScope">
                <slot name="actions.prepend" v-bind="actionsScope"></slot>
              </template>
              <template #append="actionsScope">
                <slot name="actions.append" v-bind="actionsScope"></slot>
              </template>
            </FormActions>

            <slot name="top" v-bind="{item: formItem, schema}"></slot>

            <!-- Middle Form Actions -->
            <FormActions v-if="actionsPosition == 'middle' && isEditing"
              :modelValue="formItem"
              :actions="actions"
              :is-editing="isEditing"
              @action-complete="$emit('actionComplete', $event)"
            >
              <template #prepend="actionsScope">
                <slot name="actions.prepend" v-bind="actionsScope"></slot>
              </template>
              <template #append="actionsScope">
                <slot name="actions.append" v-bind="actionsScope"></slot>
              </template>
            </FormActions>

            <div v-if="hasSchemaInputSourceLoading && !noWaitSourceLoading" class="d-flex justify-center align-center h-100 pa-16">
              <v-progress-circular
                indeterminate
                bg-color="primary-lighten-3"
                color="primary"
                :size="60"
                :width="6"
              />
            </div>

            <v-custom-form-base
              :class="hasSchemaInputSourceLoading && !noWaitSourceLoading ? 'd-none' : ''"
              :id="formBaseId"

              v-model="model"
              :schema="inputSchema"
              :row="rowAttribute"
              :form-item="formItem"
              no-auto-generate-schema

              @update="handleUpdate"
              @input="handleInput"
              @resize="handleResize"
              @blur="handleBlur"
              @click="handleClick"
            >
              <template
                v-for="(_slot, key) in formSlots"
                :key="key"
                v-slot:[`slot-inject-${_slot.name}-key-${formBaseId}-${_slot.slotPath}`]="_slotScope"
                >
                <template v-if="_slot.type == 'form'">
                  <v-custom-form-base
                    :id="`${formBaseId}-${_slot.name}`"
                    v-model="model"
                    v-model:schema="_slot.schema"
                    :row="rowAttribute"

                    >
                  </v-custom-form-base>
                </template>
                <template v-else-if="_slot.type == 'recursive-stuff'">
                  <ue-recursive-stuff
                    :configuration="_slot.context"
                    :bindData="_slotScope">
                  </ue-recursive-stuff>

                </template>
              </template>
              <!-- <template v-slot:[`slot-inject-prepend-key-treeview-slot-permissions`]="{open}" >
                <v-icon color="blue">
                    {{open ? 'mdi-folder-open' : 'mdi-folder'}}
                </v-icon>
              </template>
              <template #slot-inject-label-key-treeview-slot-permissions="{item}" >
                <span class="caption" >
                  {{item.name.toUpperCase()}}
                </span>
              </template> -->
            </v-custom-form-base>

            <!-- Bottom Form Actions -->
            <FormActions v-if="actionsPosition == 'bottom' && isEditing"
              :modelValue="formItem"
              :actions="actions"
              :is-editing="isEditing"
              @action-complete="$emit('actionComplete', $event)"
            >
              <template #prepend="actionsScope">
                <slot name="actions.prepend" v-bind="actionsScope"></slot>
              </template>
              <template #append="actionsScope">
                <slot name="actions.append" v-bind="actionsScope"></slot>
              </template>
            </FormActions>
          </div>

          <div v-if="hasAdditionalSection && $vuetify.display.lgAndUp"
            :class="[
              `flex-grow-0 ml-${rightSlotGap}`,
            ]"
          >
            <div
              :class="[
                `pt-2 gr-4`,
                $vuetify.display.smAndDown ? 'd-none' : 'd-flex flex-column',
              ]"
              :style="{
                ...(rightSlotWidth ? {width: `${rightSlotWidth}px`} : {}),
                ...(rightSlotMinWidth ? {minWidth: `${rightSlotMinWidth}px`} : {}),
                ...(rightSlotMaxWidth ? {maxWidth: `${rightSlotMaxWidth}px`} : {})
              }"
            >
              <slot name="right" v-bind="{isEditing, item: formItem, schema: inputSchema, chunkedRawSchema}">
                <AdditionalSectionContent
                  :actions-position="actionsPosition"
                  :is-editing="isEditing"
                  :form-item="formItem"
                  :actions="actions"
                  @action-complete="$emit('actionComplete', $event)"
                >
                  <template #right-top>
                    <slot name="right.top" v-bind="{isEditing, item: formItem, schema: inputSchema, chunkedRawSchema}"></slot>
                  </template>
                  <template #right-middle>
                    <slot name="right.middle" v-bind="{isEditing, item: formItem, schema: inputSchema, chunkedRawSchema}"></slot>
                  </template>
                  <template #right-bottom>
                    <slot name="right.bottom" v-bind="{isEditing, item: formItem, schema: inputSchema, chunkedRawSchema}"></slot>
                  </template>
                </AdditionalSectionContent>
              </slot>
            </div>
          </div>

          <v-dialog
            v-if="hasAdditionalSection"
            v-model="showAdditionalSectionDialog"
            max-width="500px"
          >
            <v-card>
              <v-card-title class="d-flex align-center">
                <span>{{ additionalSectionDialogTitle || 'Additional Options' }}</span>
                <v-spacer></v-spacer>
                <v-btn variant="text" size="default" icon @click="showAdditionalSectionDialog = false">
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </v-card-title>
              <v-card-text>
                <slot name="right" v-bind="{item: formItem, schema: inputSchema, chunkedRawSchema}">
                  <AdditionalSectionContent
                    :actions-position="actionsPosition"
                    :is-editing="isEditing"
                    :form-item="formItem"
                    :actions="actions"
                    @action-complete="$emit('actionComplete', $event)"
                  >
                    <template #right-top>
                      <slot name="right.top" v-bind="{isEditing, item: formItem, schema: inputSchema, chunkedRawSchema}"></slot>
                    </template>
                    <template #right-middle>
                      <slot name="right.middle" v-bind="{isEditing, item: formItem, schema: inputSchema, chunkedRawSchema}"></slot>
                    </template>
                    <template #right-bottom>
                      <slot name="right.bottom" v-bind="{isEditing, item: formItem, schema: inputSchema, chunkedRawSchema}"></slot>
                    </template>
                  </AdditionalSectionContent>
                </slot>
              </v-card-text>
            </v-card>
          </v-dialog>
        </div>


        <!-- Sticky Frame Section -->

      </div>

      <v-spacer v-if="pushButtonToBottom"></v-spacer>

      <!-- Bottom Section -->
      <div :class="['px-1',scrollable ? 'flex-grow-0' : '']" v-if="(hasSubmit && isSubmittable) || $slots.submit || $slots.options || $slots.bottom">
        <v-divider v-if="hasSubmit && !stickyButton && hasDivider" class=""></v-divider>
        <div class="d-flex flex-wrap justify-center justify-md-start pt-6 w-100 ga-4 flex-md-row" v-if="hasSubmit && !stickyButton">
          <slot name="submit"
            v-bind="{
              isSubmittable,
              validForm: validModel || !serverValid,
              buttonDefaultText,
              loading
            }">
            <div class="d-flex justify-center justify-md-start flex-wrap flex-1-1-100 flex-md-1-1-0" >
              <slot name="options" v-bind="{
                isSubmittable,
                validForm: validModel || !serverValid,
                loading
              }"></slot>
            </div>
            <v-btn v-if="isSubmittable"
              type="submit"
              :disabled="!(validModel || !serverValid) || loading || !isSubmittable"
              class="ml-auto flex-1-1-100 flex-md-1-1-0 flex-lg-0-1-0 order-md-last order-first"
              :block="$vuetify.display.smAndDown"
              :loading="loading"
              :color="!isSubmittable ? 'warning' : 'primary'"
              >
              {{ !isSubmittable ? 'No Operation To Perform' : buttonDefaultText }}
            </v-btn>
          </slot>
        </div>

        <div v-if="hasSubmit && !stickyButton">
          <v-progress-linear
            v-if="loading"
            indeterminate
            color="green"
          />
        </div>

        <div class="ue-form__bottom">
          <slot name="bottom" v-bind="{}"></slot>
        </div>
      </div>

    </v-form>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { useForm, makeFormProps } from '@/hooks'
import { cloneDeep, omit, isObject } from 'lodash-es'
import FormActions from './form/FormActions.vue'
import FormEvents from './form/FormEvents.vue'

// Create a new component for the right section content
const AdditionalSectionContent = {
  props: {
    actionsPosition: String,
    isEditing: Boolean,
    formItem: Object,
    actions: [Array, Object]
  },
  emits: ['action-complete'],
  template: `
    <div>
      <!-- Right Top Form Actions -->
      <FormActions v-if="actionsPosition == 'right-top' && isEditing"
        :modelValue="formItem"
        :actions="actions"
        :is-editing="isEditing"
        @action-complete="$emit('actionComplete', $event)"
      >
        <template #prepend="actionsScope">
          <slot name="actions.prepend" v-bind="actionsScope"></slot>
        </template>
        <template #append="actionsScope">
          <slot name="actions.append" v-bind="actionsScope"></slot>
        </template>
      </FormActions>

      <slot name="right-top"></slot>

      <!-- Right Middle Form Actions -->
      <FormActions v-if="actionsPosition == 'right-middle' && isEditing"
        :modelValue="formItem"
        :actions="actions"
        :is-editing="isEditing"
        @action-complete="$emit('actionComplete', $event)"
      >
        <template #prepend="actionsScope">
          <slot name="actions.prepend" v-bind="actionsScope"></slot>
        </template>
        <template #append="actionsScope">
          <slot name="actions.append" v-bind="actionsScope"></slot>
        </template>
      </FormActions>

      <slot name="right-middle"></slot>

      <!-- Right Bottom Form Actions -->
      <FormActions v-if="actionsPosition == 'right-bottom' && isEditing"
        :modelValue="formItem"
        :actions="actions"
        :is-editing="isEditing"
        @action-complete="$emit('actionComplete', $event)"
      >
        <template #prepend="actionsScope">
          <slot name="actions.prepend" v-bind="actionsScope"></slot>
        </template>
        <template #append="actionsScope">
          <slot name="actions.append" v-bind="actionsScope"></slot>
        </template>
      </FormActions>

      <slot name="right-bottom"></slot>
    </div>
  `,
  components: {
    FormActions
  }
}

export default {
  name: 'ue-form',
  components: {
    FormActions,
    FormEvents,
    AdditionalSectionContent
  },
  emits: [
    'update:valid',
    'update:modelValue',
    'update:schema',
    'input',
    'actionComplete',
    'submitted'
  ],
  props: {
    ...makeFormProps(),
  },

  provide() {
      // use function syntax so that we can access `this`
      return {
        manualValidation: computed(() => this.manualValidation),
        submitForm: computed(() => this.submit)
      }
  },
  setup(props, context) {
    const store = useStore()
    const useFormInstance = useForm(props, context)
    const { t, te, locale } = useI18n({ useScope: 'global' })
    // const i18n = useI18n()

    const formClasses = computed(() => [
      props.noDefaultFormPadding ? '' : 'pa-4',
      props.noDefaultSurface ? '' : 'bg-surface',
      props.fillHeight ? 'd-flex flex-column h-100' : '',
      props.formClass,
    ])

    const formSlots = computed(() => {
      const slots = []


      // Object.values(formHook.inputSchema).forEach((schema, index) => {
      function getSlots(input, inputName, slots, parentSlotPath = '') {
        let slotPath = parentSlotPath != '' ? `${parentSlotPath}-${inputName}` : inputName

        if (Object.prototype.hasOwnProperty.call(input, 'slots') && Object.keys(input.slots).length > 0) {
          Object.keys(input.slots).forEach((slotName) => {
            slots.push({
              type: 'recursive-stuff',
              name: slotName,
              inputName,
              slotPath,
              context: input.slots[slotName]
            });
          });
        } else if (Object.prototype.hasOwnProperty.call(input, 'slotable')) {
          slots.push({
            type: 'form',
            name: input.slotable.name,
            inputName: input.slotable.slotTo,
            selfName: inputName,
            slotPath,
            schema: cloneDeep(useFormInstance.invokeRuleGenerator({
              [inputName]: omit(input, ['slotable'])
            }))
          });
        }

        if (input.schema && isObject(input.schema)) {
          Object.keys(input.schema).forEach((subInputName) => {
            let subInput = input.schema[subInputName]
            getSlots(subInput, subInputName, slots, slotPath);
          });
        }
      }

      Object.keys(useFormInstance.inputSchema.value).forEach((inputName) => {
        let input = useFormInstance.inputSchema.value[inputName]

        getSlots(input, inputName, slots);
      });

      return slots
    })

    const titleOptions = computed(() => {
      let options = {}

      console.log(props.title)

      if(__isObject(props.title)){
        options = {
          tag: props.title.tag || 'div',
          type: props.title.type || 'body-1',
          weight: props.title.weight || 'regular',
          transform: props.title.transform || 'none',
          color: props.title.color,
          padding: props.title.padding || 'a-0',
          margin: props.title.margin || 'a-0',
          align: props.title.align || 'left',
          justify: props.title.justify || 'start',
          ...(props.title.class ? {class: props.title.class} : {class: 'flex-md-row flex-column justify-md-space-between'})
        }
      } else {
        options = {
          class: 'flex-md-row flex-column justify-md-space-between'
        }
      }
      return options
    })

    const titleSerialized = computed(() => {
      let title = props.title

      if(__isObject(props.title)){
        title = props.title.text
      }

      return te(title)
        ? t(title)
        : title
    })

    const formColumnAttrs = computed(() => {
      return props.hasStickyFrame
        ? {
            cols: '12',
            sm: '12',
            md: '12',
            lg: '8',
            xl: '6',
            'order-lg': '0',
            'order-xl': '0'
          }
        : {
            cols: '12'
          }
    })

    const stickyColumnAttrs = computed(() => {
      return {
        cols: '12',
        sm: '12',
        md: '12',
        lg: '4',
        xl: '6',
        'order-lg': '1',
        'order-xl': '1'
      }
    })

    onMounted(() => {
      let timezoneInput = document.getElementById('timezone_session')
      if (timezoneInput && useFormInstance.model.value && useFormInstance.model.value._timezone) {
        useFormInstance.model.value._timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
      }
    })

    return {
      ...useFormInstance,
      formClasses,
      formSlots,
      titleOptions,
      titleSerialized
      // formColumnAttrs,
      // stickyColumnAttrs
    }
  }
}
</script>

<style lang="sass" scoped>

</style>
