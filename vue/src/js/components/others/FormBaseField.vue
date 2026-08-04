<!--
  FormBaseField - Renders a single form field based on schema type.
  Extracted from CustomFormBase for the refactored FormBase.vue.
-->
<template>
  <!-- PREVIEW -->
  <ue-recursive-stuff
    v-if="obj.schema.type === 'preview' && obj.schema.configuration"
    :configuration="obj.schema.configuration"
    :bind-data="{ ...ctx.valueIntern, ...formItem }"
  />

  <!-- DYNAMIC COMPONENT -->
  <ue-dynamic-component-renderer
    v-else-if="obj.schema.type === 'dynamic-component'"
    :subject="obj.schema.subject ?? ctx.setValue(obj)"
  />

  <!-- TITLE -->
  <component
    v-else-if="obj.schema.type === 'title'"
    :is="ctx.mapTypeToComponent(obj.schema.type)"
    v-bind="ctx.bindSchema(obj)"
  />

  <!-- RADIO -->
  <component
    v-else-if="obj.schema.type === 'radio'"
    :is="ctx.mapTypeToComponent(obj.schema.type)"
    v-bind="ctx.bindSchema(obj)"
    :model-value="ctx.setValue(obj)"
    :options="obj.schema.options"
    :obj="obj"
    :id="ctx.id"
    :index="index"
    @update:model-value="ctx.onInput($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" #[s]="slotData">
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index, ...slotData }" />
    </template>
  </component>

  <!-- DATE, TIME, COLOR TEXT-MENU -->
  <v-menu
    v-else-if="ctx.isDateTimeColorTypeAndExtensionText(obj)"
    v-bind="ctx.bindSchemaMenu(obj)"
  >
    <template v-slot:activator="{ props: menuProps }">
      <v-text-field
        v-bind="{ ...ctx.bindSchemaText(obj), ...menuProps }"
        :model-value="ctx.setValue(obj)"
        @[ctx.suspendClickAppend(obj)]="ctx.onEvent($event, obj, 'append')"
        @click:append-inner="ctx.onEvent($event, obj, 'appendInner')"
        @click:prepend="ctx.onEvent($event, obj, 'prepend')"
        @click:prepend-inner="ctx.onEvent($event, obj, 'prependInner')"
      />
    </template>
    <component
      :is="ctx.mapTypeToComponent(obj.schema.type)"
      v-bind="ctx.bindSchema(obj)"
      :type="ctx.checkInternType(obj)"
      :model-value="ctx.setValue(obj)"
      @update:model-value="ctx.onInput($event, obj)"
      @click:hour="ctx.onEvent({ type: 'click' }, obj, 'hour')"
      @click:minute="ctx.onEvent({ type: 'click' }, obj, 'minute')"
      @click:second="ctx.onEvent({ type: 'click' }, obj, 'second')"
    />
  </v-menu>

  <!-- ARRAY -->
  <template v-else-if="obj.schema.type === 'array'">
    <div
      v-for="(item, idx) in ctx.setValue(obj)"
      :key="ctx.getKeyForArray(ctx.id, obj, item, idx)"
      v-bind="ctx.bindSchema(obj)"
    >
      <slot :name="ctx.getArrayTopSlot(obj)" v-bind="{ obj, id: ctx.id, index, idx, item }" />
      <slot :name="ctx.getArrayItemSlot(obj)" v-bind="{ obj, id: ctx.id, index, idx, item }">
        <v-form-base
          :id="`${ctx.id}-${obj.key}-${idx}`"
          :model-value="item"
          v-model:schema="obj.schema.schema"
          :row="ctx.getRowGroupOrArray(obj)"
          :col="ctx.getColGroupOrArray(obj)"
          :class="`${ctx.id}-${obj.key}`"
          @update:model-value="item = $event"
        >
          <template v-for="(_, name) in $slots" #[name]="slotData">
            <slot :name="name" v-bind="{ id: ctx.id, obj, index, idx, item, ...slotData }" />
          </template>
        </v-form-base>
      </slot>
      <slot :name="ctx.getArrayBottomSlot(obj)" v-bind="{ obj, id: ctx.id, index, idx, item }" />
    </div>
  </template>

  <!-- GROUP | WRAP -->
  <template v-else-if="/^(wrap|group)$/.test(obj.schema.type)">
    <component
      v-if="obj.schema?.schema"
      :is="ctx.checkInternGroupType(obj)"
      v-bind="ctx.bindSchema(obj)"
      :title="obj.schema.typeIntTitle ?? null"
      :model-value="obj.schema.typeIntModelValue || false"
      @click="ctx.onEvent($event, obj)"
    >
      <div v-if="obj.schema.noLabel !== true && (obj.schema.title || obj.schema.subtitle)" class="mb-3">
        <ue-title v-if="obj.schema.title" padding="a-0" margin="b-0" transform="none" weight="bold" class="text-body-1">{{ obj.schema.title }}</ue-title>
        <ue-title v-if="obj.schema.subtitle" padding="a-0" margin="b-4" transform="none" weight="regular" class="text-caption">
          <span v-html="obj.schema.subtitle"></span>
        </ue-title>
      </div>
      <v-form-base
        :id="`${ctx.id}-${obj.key}`"
        :model-value="ctx.setValue(obj)"
        v-model:schema="obj.schema.schema"
        :row="ctx.getRowGroupOrArray(obj)"
        :col="ctx.getColGroupOrArray(obj)"
        :class="`${ctx.id}-${obj.key}`"
        @input="ctx.onInputWrapper($event, obj)"
      >
        <template v-for="(_, name) in $slots" #[name]="slotData">
          <slot :name="name" v-bind="{ id: ctx.id, obj, index, ...slotData }" />
        </template>
      </v-form-base>
    </component>
  </template>

  <!-- TREEVIEW -->
  <component
    v-else-if="obj.schema.type === 'treeview'"
    :is="vTreeview"
    v-model:open="obj.schema.open"
    v-bind="ctx.bindSchema(obj)"
    :items="obj.schema.items"
    :model-value="ctx.setValue(obj)"
    @update:open="ctx.onEvent({ type: 'click' }, obj, 'open')"
    @update:active="ctx.onEvent({ type: 'click' }, obj, 'selected')"
    @update:model-value="ctx.onInput($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" v-slot:[s]="slotData">
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index, ...slotData }" />
    </template>
  </component>

  <!-- LIST -->
  <template v-else-if="obj.schema.type === 'list'">
    <v-list>
      <slot :name="ctx.getKeyInjectSlot(obj, 'label')" v-bind="{ id: ctx.id, obj, index }">
        <v-toolbar v-if="obj.schema.label" v-bind="ctx.bindSchema(obj)" dark>
          <v-toolbar-title>{{ obj.schema.label }}</v-toolbar-title>
        </v-toolbar>
      </slot>
      <v-list v-model="obj.schema.model" v-bind="ctx.bindSchema(obj)" light>
        <template v-for="(item, idx) in ctx.setValue(obj)" :key="idx">
          <v-list-item @click="ctx.onEvent($event, obj, 'list')">
            <slot :name="ctx.getArrayItemSlot(obj)" v-bind="{ obj, id: ctx.id, index, idx, item }">
              <v-list-item :icon="obj.schema.icon" :title="obj.schema.item ? item[obj.schema.item] : item" />
            </slot>
          </v-list-item>
        </template>
      </v-list>
    </v-list>
  </template>

  <!-- CHECKBOX | SWITCH -->
  <component
    v-else-if="/(switch|checkbox)/.test(obj.schema.type)"
    :is="ctx.mapTypeToComponent(obj.schema.type)"
    :model-value="ctx.setValue(obj)"
    v-bind="ctx.bindSchema(obj)"
    @update:model-value="ctx.onInput($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" #[s]>
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index }" />
    </template>
  </component>

  <!-- FILE -->
  <v-file-input
    v-else-if="obj.schema.type === 'file'"
    v-bind="ctx.bindSchema(obj)"
    :model-value="ctx.setValue(obj)"
    @focus="ctx.onEvent($event, obj)"
    @blur="ctx.onEvent($event, obj)"
    @change="ctx.onInput($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" #[s]="scopeData">
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index, ...scopeData }" />
    </template>
  </v-file-input>

  <!-- ICON -->
  <v-icon
    v-else-if="obj.schema.type === 'icon'"
    v-bind="ctx.bindSchema(obj)"
    v-text="ctx.getIconValue(obj)"
    @click="ctx.onEvent($event, obj)"
  />

  <!-- SLIDER -->
  <v-slider
    v-else-if="obj.schema.type === 'slider'"
    v-bind="ctx.bindSchema(obj)"
    :model-value="ctx.setValue(obj)"
    @update:model-value="ctx.onInput($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" #[s]>
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index }" />
    </template>
  </v-slider>

  <!-- IMG -->
  <v-img
    v-else-if="obj.schema.type === 'img'"
    :src="ctx.getImageSource(obj)"
    v-bind="ctx.bindSchema(obj)"
    @click="ctx.onEvent($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" #[s]>
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index }" />
    </template>
  </v-img>

  <!-- BTN-TOGGLE -->
  <v-btn-toggle
    v-else-if="obj.schema.type === 'btn-toggle'"
    v-bind="ctx.bindSchema(obj)"
    :model-value="ctx.setValue(obj)"
    @change="ctx.onInput($event, obj)"
  >
    <v-btn
      v-for="(option, oidx) in obj.schema.options"
      :key="oidx"
      v-bind="ctx.bindOptions(option)"
      :icon="!!option.icon"
    >
      <v-icon :dark="obj.schema.dark">{{ ctx.bindOptions(option).icon }}</v-icon>
      {{ ctx.bindOptions(option).label }}
    </v-btn>
  </v-btn-toggle>

  <!-- BTN -->
  <v-btn
    v-else-if="obj.schema.type === 'btn'"
    v-bind="ctx.bindSchema(obj)"
    type="button"
    @click="ctx.onEvent($event, obj, 'button')"
  >
    {{ ctx.setValue(obj) }}
    <v-icon v-if="obj.schema.iconCenter" :dark="obj.schema.dark">{{ obj.schema.iconCenter }}</v-icon>
    {{ obj.schema.label }}
  </v-btn>

  <!-- v-input-locale (translated) -->
  <v-input-locale
    v-else-if="obj.schema.translated"
    :type="ctx.mapTypeToComponent(obj.schema.type)"
    :attributes="obj.schema"
    :obj="obj"
    :model-value="ctx.setValue(obj)"
    @update:model-value="ctx.onInput($event, obj)"
  />

  <!-- MASK -->
  <component
    v-else-if="obj.schema.mask"
    :is="ctx.mapTypeToComponent(obj.schema.type)"
    v-bind="ctx.bindSchema(obj)"
    v-mask="obj.schema.mask"
    :type="ctx.checkExtensionType(obj)"
    :model-value="ctx.setValue(obj)"
    :obj="obj"
    v-model:[ctx.searchInputSync(obj)]="obj.schema.searchInput"
    @focus="ctx.onEvent($event, obj)"
    @blur="ctx.onEvent($event, obj)"
    @[ctx.suspendClickAppend(obj)]="ctx.onEvent($event, obj, 'append')"
    @click:append-inner="ctx.onEvent($event, obj, 'appendInner')"
    @click:prepend="ctx.onEvent($event, obj, 'prepend')"
    @click:prepend-inner="ctx.onEvent($event, obj, 'prependInner')"
    @click:clear="ctx.onEvent($event, obj, 'clear')"
    @click:hour="ctx.onEvent({ type: 'click' }, obj, 'hour')"
    @click:minute="ctx.onEvent({ type: 'click' }, obj, 'minute')"
    @click:second="ctx.onEvent({ type: 'click' }, obj, 'second')"
    @update:model-value="ctx.onInput($event, obj)"
    @update:input="ctx.updateInput($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" #[s]>
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index, model: ctx.valueIntern }" />
    </template>
  </component>

  <!-- DEFAULT -->
  <component
    v-else
    :is="ctx.mapTypeToComponent(obj.schema.type)"
    v-bind="ctx.bindSchema(obj)"
    :type="ctx.checkExtensionType(obj)"
    :model-value="ctx.setValue(obj)"
    :obj="obj"
    v-model:[ctx.searchInputSync(obj)]="obj.schema.searchInput"
    @focus="ctx.onEvent($event, obj)"
    @blur="ctx.onEvent($event, obj)"
    @[ctx.suspendClickAppend(obj)]="ctx.onEvent($event, obj, 'append')"
    @click:append-inner="ctx.onEvent($event, obj, 'appendInner')"
    @click:prepend="ctx.onEvent($event, obj, 'prepend')"
    @click:prepend-inner="ctx.onEvent($event, obj, 'prependInner')"
    @click:clear="ctx.onEvent($event, obj, 'clear')"
    @click:hour="ctx.onEvent({ type: 'click' }, obj, 'hour')"
    @click:minute="ctx.onEvent({ type: 'click' }, obj, 'minute')"
    @click:second="ctx.onEvent({ type: 'click' }, obj, 'second')"
    @update:model-value="ctx.onInput($event, obj)"
    @update:input="ctx.updateInput($event, obj)"
  >
    <template v-for="s in ctx.getInjectedScopedSlots(ctx.id, obj)" v-slot:[s]="slotData">
      <slot :name="ctx.getKeyInjectSlot(obj, s)" v-bind="{ id: ctx.id, obj, index, ...slotData, model: ctx.valueIntern }" />
    </template>
  </component>
</template>

<script>
// import { VTreeview } from 'vuetify/labs/VTreeview'

export default {
  name: 'FormBaseField',
  // components: { vTreeview: VTreeview },
  props: {
    obj: { type: Object, required: true },
    ctx: { type: Object, required: true },
    index: { type: Number, required: true },
    formItem: { type: Object, default: () => ({}) }
  }
}
</script>
