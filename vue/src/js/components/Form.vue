<template>
  <div :class="fillHeight ? '' : ''"
    :style="{height: fillHeight ? ($vuetify.display.mdAndDown ? `calc(97vh - 64px)` : `calc(97vh)` ) : ''}">
    <v-form
      :id="id"
      :ref="VForm"
      :action="actionUrl"
      method="POST"
      v-model="validModel"
      @submit="submit"
      :class="formClasses"
      >
      <input v-if="!async" type="hidden" name="_token" :value="csrf"/>

      <!-- Header Section -->
      <div :class="[(hasDivider || title) ? 'pb-6' : '', scrollable ? 'flex-grow-0' : '']">
        <ue-title
          v-if="title"
          padding="b-3"
          color="grey-darken-5"
          align="center"
          justify="space-between"
          v-bind="titleOptions"
        >
          {{ titleSerialized }}
          <template v-slot:right>
            <div class="d-flex align-center">
              <slot name="headerCenter">

              </slot>
              <!-- Form Actions -->
              <template v-if="hasAction && false">
                <div class="d-flex flex-wrap ga-2 mr-2">
                  <template v-for="(action, key) in flattenedActions">
                    <v-tooltip
                      v-if="shouldShowAction(action) && action.type !== 'modal'"
                      :disabled="!action.icon || action.forceLabel"
                      :location="action.tooltipLocation ?? 'top'"
                    >
                      <template v-slot:activator="{ props }">
                        <v-switch
                          v-if="action.type === 'publish'"
                          :modelValue="model[action.key ?? 'published'] ?? action.default ?? false"
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
                    <v-menu v-else-if="shouldShowAction(action) && action.type === 'modal'"
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
                          :action-url="action.endpoint.replace(':id', model.id)"
                          :valid="extraValids[key]"
                          @update:valid="extraValids[key] = $event"
                          has-divider
                          has-submit
                          button-text="Save"
                        />
                      </v-sheet>
                    </v-menu>
                  </template>
                </div>
              </template>

              <FormActions
                v-if="isEditing"
                :modelValue="formItem"
                :actions="actions"
                :is-editing="isEditing"
                @action-complete="$emit('actionComplete', $event)"
              />
              <!-- Input events-->
              <template v-if="topSchema && topSchema.length">
                  <template v-for="topInput in topSchema" :key="topInput.name">
                    <v-tooltip
                      :disabled="topInput.tooltip == ''"
                      :location="topInput.tooltipLocation ?? 'top'"
                    >
                      <template v-slot:activator="{ props }">
                        <v-switch
                          v-if="topInput.type === 'switch'"
                          v-bind="{...$lodash.omit(topInput, 'label'), ...props}"
                          hide-details
                          :modelValue="model[topInput.name] ?? topInput.default ?? false"
                          @update:modelValue="model[topInput.name] = $event"
                          class="mr-2"
                        />
                        <ue-recursive-stuff v-else-if="topInput.viewOnlyComponent"
                          :configuration="topInput.viewOnlyComponent"
                          :bind-data="model"
                          v-bind="props"
                          class="mr-2"
                        />
                        <v-menu v-else
                          :close-on-content-click="false"
                          transition="scale-transition"
                          offset-y
                          v-bind="props"
                        >
                          <template v-slot:activator="{ props }">
                            <v-btn
                              class="mr-2"
                              variant="outlined"
                              append-icon="mdi-chevron-down"
                              v-bind="props"
                            >
                              <!-- {{ topInput.label }} -->
                              {{ getTopInputActiveLabel(topInput) }}
                              <!-- {{ topInput.items.find(item => item[topInput.itemValue] ===  ($isset(model[topInput.name]) ? model[topInput.name] : -1))[topInput.itemTitle] ?? topInput.label }} -->
                            </v-btn>
                          </template>

                          <v-list>
                            <v-list-item
                              v-for="(item, index) in topInput.items"
                              :key="item.id"
                              @click="model[topInput.name] = item.id"
                            >
                              <v-list-item-title>
                                {{ item.name }}
                                <v-icon v-if="$isset(model[topInput.name]) && item[topInput.itemValue] === model[topInput.name]" size="small" icon="$check" color="primary"></v-icon>
                              </v-list-item-title>
                            </v-list-item>
                          </v-list>
                        </v-menu>
                      </template>
                      <span>{{ topInput.tooltip ?? topInput.label }}</span>
                    </v-tooltip>

                  </template>
              </template>

              <!-- Language Selector -->
              <v-chip-group
                v-if="hasTraslationInputs && languages && languages.length && languages.length > 1"
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
              <slot name="headerRight">

              </slot>
            </div>
          </template>
        </ue-title>

        <v-divider v-if="hasDivider"></v-divider>
      </div>

      <!-- {{ $log(model, formItem) }} -->
      <!-- Scrollable Content Section -->
      <div :class="['d-flex', scrollable ? 'flex-grow-1 overflow-hidden mr-n5' : '']">
        <div :class="['w-100', scrollable ? 'overflow-y-auto pr-3' : '']"
        >
          <slot name="top" v-bind="{item: formItem, schema}"></slot>

          <v-custom-form-base
            :id="`ue-wrapper-${id}`"
            v-model="model"
            v-model:schema="inputSchema"
            :row="rowAttribute"

            @update="handleUpdate"
            @input="handleInput"
            @resize="handleResize"
            @blur="handleBlur"
            @click="handleClick"
            >
            <template
              v-for="(_slot, key) in formSlots"
              :key="key"
              v-slot:[`slot-inject-${_slot.name}-key-ue-wrapper-${id}-${_slot.inputName}`]="_slotData"
              >
              <template v-if="_slot.type == 'form'">
                <v-custom-form-base
                  :id="`ue-wrapper-${id}-${_slot.name}`"
                  v-model="model"
                  v-model:schema="_slot.schema"
                  :row="rowAttribute"

                  >

                </v-custom-form-base>
              </template>
              <template v-else-if="_slot.type == 'recursive-stuff'">
                <ue-recursive-stuff
                  v-for="(context, i) in _slot.context.elements"
                  :key="i"
                  :configuration="context"
                  :bindData="_slotData">
                </ue-recursive-stuff>
              </template>
              <!-- <div>
                {{ $log(_slot, _slotData) }}
                Hello
              </div> -->
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
        </div>
        <!-- Sticky Frame Section -->

      </div>

      <!-- <v-spacer></v-spacer> -->

      <!-- Footer Section -->
      <div :class="[scrollable ? 'flex-grow-0' : '']">
        <v-divider v-if="hasSubmit && !stickyButton && hasDivider" class="mt-6"></v-divider>
        <div class="d-flex pt-6" v-if="hasSubmit && !stickyButton">
          <slot name="submit"
            v-bind="{
              validForm: validModel || !serverValid,
              buttonDefaultText
            }">
            <v-btn type="submit" :disabled="!(validModel || !serverValid) || loading" class="ml-auto mb-5">
              {{ buttonDefaultText }}
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
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useI18n } from 'vue-i18n'
import { useForm } from '@/hooks'
import { cloneDeep, omit } from 'lodash-es'
import FormActions from './form/FormActions.vue'

export default {
  name: 'ue-form',
  components: {
    FormActions
  },
  emits: [
    'update:valid',
    'update:modelValue',
    'input',
    'actionComplete',
    'submitted'
  ],
  props: {
    modelValue: {
      type: Object,
      default () {
        return {}
      }
    },
    formClass: {
      type: [Array, String],
      default: ''
    },
    actionUrl: {
      type: String
    },
    title: {
      type: String
    },
    schema: {
      type: Object,
      default () {
        return {}
      }
    },
    async: {
      type: Boolean,
      default: true
    },
    buttonText: {
      type: String
    },
    hasSubmit: {
      type: Boolean,
      default: false
    },
    stickyFrame: {
      type: Boolean,
      default: false
    },
    stickyButton: {
      type: Boolean,
      default: false
    },
    rowAttribute: {
      type: Object,
      default () {
        return {
          noGutters: false,
          class: 'py-4',
          // justify:'center',
          // align:'center'
        }
      }
    },
    slots: {
      type: Object,
      default () {
        return {}
      }
    },
    valid: {
      type: Boolean,
      default: null
    },
    isEditing: {
      type: Boolean,
      default: false
    },
    hasDivider: {
      type: Boolean,
      default: false
    },
    fillHeight: {
      type: Boolean,
      default: false
    },
    scrollable: {
      type: Boolean,
      default: false
    },
    noDefaultFormPadding: {
      type: Boolean,
      default: false
    },
    noDefaultSurface: {
      type: Boolean,
      default: false
    },
    actions: {
      type: [Array, Object],
      default: []
    }
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
    const formHook = useForm(props, context)
    const { t, te, locale } = useI18n({ useScope: 'global' })
    // const i18n = useI18n()

    const formClasses = computed(() => [
      props.noDefaultFormPadding ? '' : 'px-6 py-6',
      props.noDefaultSurface ? '' : 'bg-surface',
      props.fillHeight ? 'd-flex flex-column h-100' : '',
      props.formClass,
    ])

    const formSlots = computed(() => {
      const slots = []

      // Object.values(formHook.inputSchema).forEach((schema, index) => {
      Object.values(formHook.issetSchema.value ? props.schema : store.state.form.inputs).forEach((schema, index) => {
        if (Object.prototype.hasOwnProperty.call(schema, 'slots') && Object.keys(schema.slots).length > 0) {
          Object.keys(schema.slots).forEach((slotName) => {
            slots.push({
              name: slotName,
              inputName: schema.name,
              type: 'recursive-stuff',
              context: schema.slots[slotName]
            })
          })
        } else if (Object.prototype.hasOwnProperty.call(schema, 'slotable')) {
          slots.push({
            name: schema.slotable.name,
            inputName: schema.slotable.slotTo,
            selfName: schema.name,
            type: 'form',
            schema: cloneDeep(formHook.invokeRuleGenerator({
              [schema.name]: omit(schema, ['slotable'])
            }))
          })
        }
      })
      return slots
    })

    const titleOptions = computed(() => {
      let options = {}

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
        ? t(title).toLocaleUpperCase(locale.value.toUpperCase())
        : title.toLocaleUpperCase(locale.value.toUpperCase())
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

    return {
      ...formHook,
      formClasses,
      formSlots,
      titleOptions,
      titleSerialized,
      // formColumnAttrs,
      // stickyColumnAttrs
    }
  }
}
</script>

<style lang="sass" scoped>

</style>
