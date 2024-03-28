import { get, isPlainObject, isFunction, isString, isNumber, isEmpty, orderBy, delay, find, findIndex } from 'lodash'

const typeToComponent = {
  // maps schema.type to prop 'type' in v-text-field  - https://www.wufoo.com/html5/
  text: 'v-text-field',
  password: 'v-text-field',
  email: 'v-text-field',
  tel: 'v-text-field',
  url: 'v-text-field',
  search: 'v-text-field',
  number: 'v-text-field',
  /*
    { type:'text, ext:'typeOfTextField', ...}
    For native <INPUT> type use alternative schema prop ext  -> schema:{ type:'text, ext:'date', ...}
    correspond to <input type="number" >
    number: 'v-text-field',   //  { type:'text, ext:'number', ...}
    range: 'v-text-field',   //  { type:'text, ext:'range', ...}
    date: 'v-text-field',    //  { type:'text, ext:'date', ...}
    time: 'v-text-field',    //  { type:'text, ext:'time', ...}
    color: 'v-text-field',   //  { type:'text, ext:'color', ...}
  */

  date: 'v-date-picker',
  time: 'v-time-picker',
  color: 'v-color-picker',
  /*
    INFO: 3 Types of PICKER DATE / TIME / COLOR
    Date-Native Input    - schema:{ type:'text, ext:'date', ...}
    Date-Picker          - schema:{ type:'date', ...}
    Date-Picker-Textmenu     - schema:{ type:'date', ext:'text'...}
  */

  // map schema.type to vuetify-control (vuetify 2.0)
  img: 'v-img',
  textarea: 'v-textarea',
  range: 'v-slider',
  file: 'v-file-input',
  switch: 'v-switch',
  checkbox: 'v-checkbox',
  card: 'v-card'
  /*
    HOW TO USE CUSTOM Components
    1)
      Name and Register your Custom-Control Component globally in 'main.js'
      but avoid collision with registered names of Vuetify - Controls
      See: https://vuejs.org/v2/guide/components-registration.html

      Vue.component('custom-component', () => import('@/components/custom-component.vue') )

    2)
      use it in Schema

      mySchema: { myCustom: { type: 'custom-component' }

    3) // custom-component.vue
      <template>
        <v-text-field v-model="inp"  label="Basic"></v-text-field>
      </template>
      <script>
        export default {
          props: ['type','value', 'obj'],
          computed:{
            inp:{
              get(){  return this.value},
              set(v){ this.$emit('input', v)}
            }
          }
        }
      < /script>
  */

}
const orderDirection = 'ASC'
const pathDelimiter = '.'
const classKeyDelimiter = '-'
const defaultID = 'form-base'
const isPicker = 'date|time|color'
const onEventDelay = 1 // ms

const mouse = 'mouseenter|mouseleave'
const change = 'input|click' // event change collects events 'input|click'
const watch = 'focus|input|click|blur' // event watch collects events 'focus|input|click|blur'
const display = 'resize|swipe|intersect' // event watch collects events 'resize|swipe|intersect'

const topAppendix = 'top'
const bottomAppendix = 'bottom'
const slotAppendix = 'slot'
const tooltipAppendix = 'tooltip'
const injectAppendix = 'inject'
const itemClassAppendix = 'item'
const typeClassAppendix = 'type'
const keyClassAppendix = 'key'
const arrayClassAppendix = 'array'
const propertyClassAppendix = 'prop'

const injectSlotAppendix = `${slotAppendix}-${injectAppendix}`
const arraySlotAppendix = `${slotAppendix}-${arrayClassAppendix}`
const topSlotAppendix = `${slotAppendix}-${topAppendix}`
const itemSlotAppendix = `${slotAppendix}-${itemClassAppendix}`
const bottomSlotAppendix = `${slotAppendix}-${bottomAppendix}`
const tooltipSlotAppendix = `${slotAppendix}-${tooltipAppendix}`

// const clear = 'clear'
// const button = 'button'
// const treeview = 'treeview'
// const list = 'list'
// const focus = 'focus'
// const blur = 'blur'
// const append = 'append'
// const appendInner = 'append-inner'
// const prepend = 'prepend'
// const prependInner = 'prepend-inner'

// const hour = 'hour'
// const minute = 'minute'
// const second = 'second'

// symbol on drop
const dropEffect = 'move' // 'copy, link, move
// Default row setting if no row-attribute defined
// const rowDefault = { noGutters: true } // { noGutters:true, justify:'center', align:'center' }

// Default col setting, overrideable by prop col or by schema.col definition
// Default col setting, overrideable by prop flex or by schema.flex definition (flex is DEPRECATED use col instead)
const colDefault = { cols: 'auto' } // { cols:12, sm: 6, md:4, lg:3, xl:2}

// Mapper for Autogeneration of Schema from Value
const defaultSchemaIfValueIsNullOrUndefined = key => ({ type: 'text', label: key })
const defaultSchemaIfValueIsString = key => ({ type: 'text', label: key })
const defaultSchemaIfValueIsNumber = key => ({ type: 'number', label: key })
const defaultSchemaIfValueIsBoolean = key => ({ type: 'checkbox', label: key })
// Menu triggered DateTimePicker Default
const defaultPickerSchemaText = { type: 'text', readonly: true }
const defaultPickerSchemaMenu = { closeOnContentClick: false, transition: 'scale-transition', nudgeRight: 32, maxWidth: '290px', minWidth: '290px' }
// type wrap or group - if no typeInt defined take default
const defaultInternGroupType = 'v-card'

export default {
  props: {

  },
  methods: {
    // MAP TYPE
    mapTypeToComponent (type) {
      // merge global registered components into typeToComponent Object
      const allTypeComponents = { ...typeToComponent, ...this.vueInstance.components }
      // const typeToComponent -> maps type to according v-component
      // ie. schema:{ type:'password', ... } to specific vuetify-control or default to v-text-field'
      return allTypeComponents[type] ? allTypeComponents[type] : `v-${type}`
    },
    // CHECK FOR TYPE: DATE, TIME OR COLOR and EXT: TEXT
    isDateTimeColorTypeAndExtensionText (obj) {
      return isPicker.includes(obj.schema.type) && obj.schema.ext === 'text'
    },
    // CHECK FOR EXT: DATE, TIME OR COLOR
    isDateTimeColorExtension (obj) {
      return isPicker.includes(obj.schema.ext)
    },
    // BIND SCHEMA TEXT OPTIONS
    bindOptions (b) {
      // schema.options in RADIO/BUTTON
      return isString(b) ? { value: b, label: b } : b
    },
    bindSchemaText (obj) {
      return { ...defaultPickerSchemaText, ...obj.schema.text }
    },
    bindSchemaMenu (obj) {
      return { ...defaultPickerSchemaMenu, ...obj.schema.menu }
    },
    bindSchema (obj) {
      return obj.schema
    },
    suspendClickAppend (obj) {
      // select|combobox|autocomplete -> suspend 'click:append' for working down arrow
      return /(select|combobox|autocomplete)/.test(obj.schema.type) ? '' : 'click:append'
    },
    searchInputSync (obj) {
      // schema.searchInput ->   bind 'search-input'
      return (typeof obj.schema.searchInput !== 'undefined') ? 'search-input' : ''
    },

    // EXT TYPE
    checkExtensionType (obj) {
      // For native <INPUT> type use prop 'ext'
      // { type:'text', ext:'range', ... } -> use native Input Type 'range' instead of slider
      // { type:'text', ext:'number', ...} -> use native Input Type 'number'
      return obj.schema.ext || obj.schema.type
    },
    // V-INTERN TYPE
    checkInternType (obj) {
      // If vuetify component needs a 'type' prop for working  - ie. datepicker uses type:'month'
      // { type:'date', ext:'text', typeInt:'month' ...} -> use v-date-picker menu with intern Type 'month'
      return obj.schema.typeInt || obj.schema.type
    },
    checkInternGroupType (obj) {
      //  in type 'wrap|group' you can define with typeInt: a component as group - schema: { group1: { type:'wrap', typeInt:'v-card', ... } ...}
      const typeInt = obj.schema.typeInt || defaultInternGroupType
      return typeInt.startsWith('v-') ? typeInt : `v-${typeInt}`
    },
    // GET ITERATION KEY FOR TYPE ARRAY
    getKeyForArray (id, obj, item, index) {
      // IMPORTANT if you want to add or remove items in type:'array'
      // more Info ->
      // https://forum.vuejs.org/t/after-splicing-an-object-wrong-item-disappears-from-view/9247/4
      // https://stackoverflow.com/questions/45655090/vue-array-splice-removing-wrong-item-from-list

      // create for iteration v-for an uniqe key from each object in array using index and time.hash
      // or define your key index by defining a key property
      // MODEL
      // arrayTasks: [{ trace:'100', label:'A'}, ...  ]
      // SCHEMA
      // arrayTasks: { type:'array', schema:{ ... } }                                                      DEFAULT KEY -> KEY id-key-index   'arrayTasks-0'
      // arrayTasks: { type:'array', key:'trace', schema:{ trace: { type:'text'}, ... } }                              -> KEY trace            100
      // arrayTasks: { type:'array', key:['trace','label'], schema:{ trace: { type:'text'}, label: { type:'text'} } }  -> KEY trace_label      100_A

      // IMPORTANT! Key should not contain an EDITABLE prop, because of re-iteration on any change

      const k = obj.schema.key
      return k ? Array.isArray(k) ? k.map(i => item[i]).join('_') : item[k] : (!isNaN(index)) ? `${id}-${obj.key}-${index}` : index
    },
    //
    // GET IMG SOURCE
    getImageSource (obj) {
      // if exist get source from src otherwise join schema.base & value & schema.tail
      return obj.schema.src ? obj.schema.src : `${obj.schema.base}${obj.value}${obj.schema.tail}`
    },
    //
    // ICON
    getIconValue (obj) {
      // icon: try schema.label or if undefined use value
      return obj.schema.label ? obj.schema.label : this.setValue(obj)
    },
    //
    // TOOLTIP
    getShorthandTooltip (schemaTooltip) {
      // check if tooltip is typeof string ->  shorthand { bottom:true, label: obj.schema.tooltip} otherwise take original object
      return isString(schemaTooltip) ? { bottom: true, label: schemaTooltip } : schemaTooltip
    },
    getShorthandTooltipLabel (schemaTooltip) {
      // check if tooltip is typeof string ->  return Label
      return isString(schemaTooltip) ? schemaTooltip : schemaTooltip && schemaTooltip.label
    },
    //
    // FORM SLOTS
    getFormTopSlot () {
      // Slot for Top Line in Formbase -> 'slot-formbase-top'
      return `${topSlotAppendix}-${this.id}`
    },
    getFormBottomSlot () {
      // Slot for Bottom Line in Formbase -> 'slot-formbase-bottom'
      return `${bottomSlotAppendix}-${this.id}`
    },
    //
    // KEY SLOTS
    getKeyInjectSlot (obj, inject) {
      // get slot starting with 'slot-inject' and inject verb 'thumb-label'   -> 'slot-inject-thumb-label-key-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${injectSlotAppendix}-${inject}-${keyClassAppendix}`)
    },
    getKeyTopSlot (obj) {
      // get Key specific name by replacing '.' with '-' and prepending 'slot-top'  -> 'slot-top-key-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${topSlotAppendix}-${keyClassAppendix}`)
    },
    getKeyItemSlot (obj) {
      // get Key specific name by replacing '.' with '-' and prepending 'slot-item'  -> 'slot-item-key-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${itemSlotAppendix}-${keyClassAppendix}`)
    },
    getKeyBottomSlot (obj) {
      // get Key specific name by replacing '.' with '-' and prepending 'slot-bottom'  -> 'slot-bottom-key-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${bottomSlotAppendix}-${keyClassAppendix}`)
    },
    getKeyTooltipSlot (obj) {
      // matches Key specific Tooltip | name by replacing '.' with '-' and prepending 'slot-bottom'  -> 'slot-tooltip-key-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${tooltipSlotAppendix}-${keyClassAppendix}`)
    },
    getTooltipSlot (obj) {
      // default tooltip slot matches all keys
      return `${tooltipSlotAppendix}`
    },
    //
    // ARRAY SLOTS
    getArrayTopSlot (obj) {
      // slot each item from array  -> 'slot-top-array-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${topSlotAppendix}-${arrayClassAppendix}`)
    },
    getArrayItemSlot (obj) {
      // slot each item from array  -> 'slot-top-array-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${itemSlotAppendix}-${arrayClassAppendix}`)
    },
    getArrayBottomSlot (obj) {
      // slot each item from array   -> 'slot-bottom-array-formbase-address-city'
      return this.getKeyClassNameWithAppendix(obj, `${bottomSlotAppendix}-${arrayClassAppendix}`)
    },
    //
    // TYPE SLOTS
    getTypeTopSlot (obj) {
      // get Type specific slot name  -> 'slot-top-type-radio'
      return this.getTypeClassNameWithAppendix(obj, `${topSlotAppendix}-${typeClassAppendix}`)
    },
    getTypeItemSlot (obj) {
      // get Type specific slot name  -> 'slot-item-type-radio'
      return this.getTypeClassNameWithAppendix(obj, `${itemSlotAppendix}-${typeClassAppendix}`)
    },
    getTypeBottomSlot (obj) {
      // get Type specific slot name  -> 'slot-bottom-type-radio'
      return this.getTypeClassNameWithAppendix(obj, `${bottomSlotAppendix}-${typeClassAppendix}`)
    },
    //
    // CLASS Names
    getPropertyClassNameWithAppendix (obj, appendix) {
      // get PROP specific name by app-/prepending 'appendix-' and replacing '.' with '-' in nested key path  -> 'controls switch'
      return obj.key ? obj.key.split(pathDelimiter).map(s => `${appendix ? appendix + classKeyDelimiter : ''}${s}`).join(' ') : ''
    },
    getPropertyClassName (obj) {
      return this.getPropertyClassNameWithAppendix(obj, propertyClassAppendix)
    },
    getKeyClassNameWithAppendix (obj, appendix) {
      // get KEY specific name by app-/prepending 'appendix-' and replacing '.' with '-' in nested key path  -> 'top-slot-address-city'
      return `${appendix ? appendix + classKeyDelimiter : ''}${this.id ? this.id + classKeyDelimiter : ''}${obj.key.replace(/\./g, '-')}`
      // return `${appendix ? appendix + classKeyDelimiter : ''}${obj.key.replace(/\./g, '-')}`
    },
    getKeyClassName (obj) {
      return this.getKeyClassNameWithAppendix(obj, keyClassAppendix)
    },
    getTypeClassNameWithAppendix (obj, appendix) {
      // get TYPE specific class name by prepending '-type' -> 'type-checkbox'
      return `${appendix ? appendix + classKeyDelimiter : ''}${this.id ? this.id + classKeyDelimiter : ''}${obj.schema.type}`
      // return `${appendix + classKeyDelimiter}${obj.schema.type}`
    },
    getTypeClassName (obj) {
      return this.getTypeClassNameWithAppendix(obj, typeClassAppendix)
    },
    getClassName (obj) {
      // combines all into a single classname
      // class => ie. 'item type-checkbox key-address-zip prop-adress prop-zip'
      return `${itemClassAppendix} ${this.getTypeClassName(obj)} ${this.getKeyClassName(obj)} ${this.getPropertyClassName(obj)}`
    },
    //
    // GRID
    gridMapper (obj, prepender) {
      if (obj) {
        ['sm', 'md', 'lg', 'xl'].map(k => {
          if (obj[k]) { obj[prepender + k] = obj[k]; delete obj[k] }
        })
      }
    },
    gridReplaceXS (obj, replacer) {
      // see vuetify Grid - replace Prop XS -V1.5 with COLS, ORDER, OFFSET - V2.0
      // xs must be replaced in new Vuetify 2.0 Grid with cols, offset, order
      if (obj && obj.xs) { obj[replacer] = obj.xs; delete obj.xs }
    },
    getGridAttributes (obj) {
      // FLEX DEPRECATED use COL instead of FLEX
      // flex:{ xs|sm|md|lg } - value:number|string

      // col:{ cols|sm|md|lg|xl } - value:number|string
      // order:{ order|sm|md|lg|xl|order-sm|order-md|order-lg|order-xl } - value:number|string
      // offset:{ offset|sm|md|lg|xl|offset-sm|offset-md|offset-lg|offset-xl } - value:number|string

      const colSchema = obj.schema.col || obj.schema.flex

      const colAttr = this.col || this.flex || colDefault

      const colObject = colSchema
      // if available use schema definition of cols
        ? (isPlainObject(colSchema) ? colSchema : isNumber(colSchema) || isString(colSchema) ? { cols: colSchema } : { cols: 'auto' })
      // else use formbase attribute definition of cols
        : colAttr ? (isPlainObject(colAttr) ? colAttr : isNumber(colAttr) || isString(colAttr) ? { cols: colAttr } : { cols: 'auto' })
        // if no definition set cols to 'auto'
          : { cols: 'auto' }

      this.gridReplaceXS(colObject, 'cols')

      // schema definition of offset
      const offset = obj.schema.offset
      const offsetObject = offset ? (isPlainObject(offset) ? offset : { offset }) : offset
      this.gridMapper(offsetObject, 'offset-')
      this.gridReplaceXS(offsetObject, 'offset')

      // schema definition of offset
      const order = obj.schema.order
      const orderObject = order ? (isPlainObject(order) ? order : { order }) : order
      this.gridMapper(orderObject, 'order-')
      this.gridReplaceXS(orderObject, 'order')

      return { ...colObject, ...offsetObject, ...orderObject }
    },
    getRowGroupOrArray (obj) {
      return obj.schema.row || this.row || rowDefault
    },
    getColGroupOrArray (obj) {
      return obj.schema.col || this.col || colDefault
    },
    //
    // SANITIZE SLOTS
    getInjectedScopedSlots (id, obj) {
      // <template #slot-inject-thumb-label-key-formbase-path-to-mykey />
      // extract the verb 'thumb-label' from Slots starting with 'slot-inject' and matching [component-id] and [key]
      const rx = new RegExp(`${injectSlotAppendix}-(.*?)-${keyClassAppendix}`)
      return Object.keys(this.$slots)
        .filter(s => (s.includes(`${id}${classKeyDelimiter}${obj.key.replace(/\./g, '-')}`) && s.includes(injectSlotAppendix)))
        .map(i => i.match(rx)[1])
    },
    //
    // Map Values coming FROM Control, TO Control or DROP on Control
    toCtrl (params) {
      // signature params { value, obj, data, schema }
      //
      // manipulate value going to control, function must return a (modified) value
      // schema:{ name: { type:'text', toCtrl: ( {value} ) => value && value.toUpperCase, ... }, ... }
      // return isFunction(params.obj.schema && params.obj.schema.toCtrl) ? params.obj.schema.toCtrl(params) : params.value
      return params.obj.schema && isFunction(params.obj.schema.toCtrl) ? params.obj.schema.toCtrl(params) : params.value
    },
    fromCtrl (params) {
      // signature params { value, obj, data, schema }
      //
      // manipulate updated value from control, function must return a (modified) value
      // schema:{ name: { type:'text', fromCtrl: ( {value} ) => value && value.toUpperCase, ... }, ... }
      return params.obj.schema && isFunction(params.obj.schema.fromCtrl) ? params.obj.schema.fromCtrl(params) : params.value
    },
    dropCtrl (params) {
      // signature params { value, obj, dragObject, dragEvent, event,data, schema }
      //
      // manipulate dropped value from control, function must return a (modified) value
      // schema:{ name: { type:'text', drop: ( {value} ) => value && value.toUpperCase, ... }, ... }
      return params.obj.schema && isFunction(params.obj.schema.drop) ? params.obj.schema.drop(params) : params.value
    },
    //
    // Drag / Drop / DropValue
    dragstart (event, obj) {
      if (!obj.schema.drag) return

      event.dataTransfer.dropEffect = dropEffect
      event.dataTransfer.effectAllowed = dropEffect

      const dragEvent = this.onEvent(event, obj)
      event.dataTransfer.setData('text', JSON.stringify(dragEvent))
    },

    dragover (event, obj) { return obj.schema.drop ? event.preventDefault() : null },

    drop (event, obj) {
      if (!obj.schema.drop) return event.preventDefault()
      // get dragEvent and dragEvent.obj
      obj.dragEvent = JSON.parse(event.dataTransfer.getData('text'))

      // no drop on drag object
      if (obj.key === obj.dragEvent.obj.key && obj.id === obj.dragEvent.id) return event.preventDefault()
      // handle schema.drop function
      if (isFunction(obj.schema.drop)) obj.value = this.dropValue(obj, event)

      this.onEvent(event, obj)

      event.preventDefault()
    },

    dropValue (obj, event) {
      return this.dropCtrl({ value: obj.dragEvent.value, obj, event, data: this.storeStateData, schema: this.storeStateSchema })
    },
    //
    // Set Value
    setValue (obj, type) {
      // Use 'schema.toCtrl' Function for setting a modified Value
      // __log(
      //     obj.schema.name,
      //     this.storeStateData,
      //     // obj.value
      // )
      return obj.schema.type === 'wrap'
        ? this.toCtrl({ value: this.storeStateData, obj, data: this.storeStateData, schema: this.storeStateSchema })
        : this.toCtrl({ value: obj.value, obj, data: this.storeStateData, schema: this.storeStateSchema })
    },
    //
    // EVENTS Get Value from Input & other Events
    onInput (value, obj, type = 'input') {
      // Value after change in Control
      value = this.fromCtrl({ value, obj, data: this.storeStateData, schema: this.storeStateSchema })
      // harmonize undefined or empty strings => null, because 'clearable' in vuetify controls resets to null and not to empty string!
      value = !value || value === '' ? null : value
      // if schema type is number convert to number
      value = obj.schema.type === 'number' ? Number(value) : value
      // update deep nested prop(key) with value
      this.setObjectByPath(this.storeStateData, obj.key, value)
      obj.value = obj.value !== value ? value : obj.value
      // __log(value, obj.key)

      // when cascade select changed
      this.setCascadeSelect(obj)

      const emitObj = {
        on: type,
        id: this.id,
        index: this.index,
        params: { index: this.index, lastValue: obj.value },
        key: obj.key,
        value,
        obj,
        data: this.storeStateData,
        schema: this.storeStateSchema,
        parent: this.parent
      }

      // __log(
      //   'v-custom-form-base onInput',
      //   type,
      //   value,
      //   this.storeStateData.name,
      //   this.model.name
      // )
      // __log(
      //   'v-custom-form-base onInput',
      //   obj
      // )
      // __log('onInput', type, emitObj)
      this.emitValue(type, emitObj)

      return emitObj
    },
    onEvent (event = {}, obj, tag) {
      const text = event && event.srcElement && event.srcElement.innerText
      const model = obj.schema.model
      const open = obj.schema.open
      const index = this.index
      // avoid circular JSON in dragstart
      const parent = event.type !== 'dragstart' ? this.parent : undefined

      const emitObj = {
        on: event.type,
        id: this.id,
        index,
        params: { text, tag, model, open, index },
        key: obj.key,
        value: obj.value,
        obj,
        event,
        data: this.storeStateData,
        schema: this.storeStateSchema,
        parent
      }

      delay(() => { this.emitValue(event.type, emitObj), onEventDelay })
      // __log(emitObj)
      return emitObj
    },
    onClickOutside (event, obj) {
      if (!obj.schema || !obj.schema.clickOutside) return
      if (isFunction(obj.schema.clickOutside)) return obj.schema.clickOutside(obj, event)
      this.emitValue('clickOutside', { on: 'clickOutside', id: this.id, key: obj.key, value: obj.value, obj, params: { x: event.clientX, y: event.clientY }, event, data: this.storeStateData, schema: this.storeStateSchema })
    },
    onIntersect (isIntersecting, entries, observer, obj) {
      //   const isIntersecting = entries[0].isIntersecting
      const index = this.index
      this.emitValue('intersect', { on: 'intersect', id: this.id, index, key: obj.key, value: obj.value, obj, params: { isIntersecting, entries, observer }, data: this.storeStateData, schema: this.storeStateSchema })
    },
    onSwipe (tag, obj) {
      this.emitValue('swipe', { on: 'swipe', id: this.id, key: obj.key, value: obj.value, obj, params: { tag }, data: this.storeStateData, schema: this.storeStateSchema })
    },
    onResize (event) {
      this.emitValue('resize', { on: 'resize', id: this.id, params: { x: window.innerWidth, y: window.innerHeight }, event, data: this.storeStateData, schema: this.storeStateSchema })
    },
    //
    // EMIT EVENT
    emitValue (event, val) {
      // const mouse = 'mouseenter|mouseleave'
      // const change = 'input|click' // event change collects events 'input|click'
      // const watch = 'focus|input|click|blur' // event watch collects events 'focus|input|click|blur'
      // const display = 'resize|swipe|intersect' // event watch collects events 'resize|swipe|intersect'

      // const emitEvent = change.includes(event) ? 'change' : watch.includes(event) ? 'watch' : mouse.includes(event) ? 'mouse' : display.includes(event) ? 'display' : event
      // const listener = event

      const emitEvent = change.includes(event) ? 'onChange' : watch.includes(event) ? 'onWatch' : mouse.includes(event) ? 'onMouse' : display.includes(event) ? 'onDisplay' : event
      const listener = 'on' + this.$lodash.startCase(this.$lodash.camelCase(event)).replace(/ /g, '')

      // __log(event, emitEvent, listener, this.id, this.$attrs)
      if (this.$attrs[`${emitEvent}:${this.id}`]) {
        this.deprecateEventCustomID(emitEvent)
        this.deprecateCombinedEvents(emitEvent, event)
        this.$emit(`${emitEvent}:${this.id}`, val) // listen to specific event only
      } else if (this.$attrs[`${emitEvent}`]) {
        this.deprecateCombinedEvents(emitEvent, event)
        this.$emit(emitEvent, val) // listen to specific event only
      } else if (this.$attrs[`${listener}:${this.id}`]) {
        this.deprecateEventCustomID(event)
        this.$emit(`${event}:${this.id}`, val) // listen to specific event only
      } else if (this.$attrs[`${listener}`]) {
        // __log(event, listener, this.$attrs)
        // __log(listener, event, val, this.storeStateData)
        this.$emit(event, val) // listen to specific event only
        this.$emit(listener, val) // listen to specific event only
        this.$emit('update:modelValue', this.storeStateData) // listen to specific event only
        this.$emit('update:schema', this.storeStateSchema) // listen to specific event only
      }
    },
    deprecateEventCustomID (ev) {
      console.warn(`--- DEPRECATION ${ev}:${this.id}: ----------------------------------------------------------------------------`)
      console.warn(`<v-form-base  @${ev}:${this.id}="handler" /> is deprecated use simplified version <v-form-base  @${ev}="handler" />`)
      console.warn('---------------------------------------------------------------------------------------------')
    },
    deprecateCombinedEvents (emitEvent, event) {
      if (emitEvent !== event) {
        console.warn('--- DEPRECATION Combined Listener:  --------------------------------------------------------------------------')
        console.warn(`Combined Event-Listener '${emitEvent}' have been removed for better comprehensibility and simplification`)
        console.warn('Please use separate listener for each event like <v-form-base  @focus="handler" @input="handler" @blur="handler"/>')
        console.warn('---------------------------------------------------------------------------------------------')
      }
    },
    //
    // PREPARE ARRAYS DATA & SCHEMA
    setObjectByPath (object, path, value) {
      // resolves chained keys (like 'user.address.street') on an object and set the value
      const pathArray = path.split(pathDelimiter)
      pathArray.forEach((p, ix) => {
        // if (ix === pathArray.length - 1) this.$set(object, p, value)
        if (ix === pathArray.length - 1) object[p] = value
        object = object[p]
      })
      // __log(object)
    },
    updateArrayFromState (data, schema) {
      // __log(this.flatCombinedArray)

      this.flatCombinedArray.forEach(obj => {
        obj.value = get(data, obj.key, null) // get - lodash
        obj.schema = get(schema, obj.key, null) // get - lodash
      })
    },
    sanitizeShorthandType (key, schema) {
      // if key in schema is string only, then handle shorthand definition
      // schema:{ name:'text' }  => schema:{ name: { type:'text', label: 'name' }
      return isString(schema) ? { type: schema, label: key } : schema
    },
    flattenObjects (dat = {}, sch) {
      const data = {}
      const schema = {}
      // Organize Formular using Schema not Data
      Object.keys(sch).forEach(key => {
        // convert string definition of name:'text' into object name:{type:'text'}
        sch[key] = this.sanitizeShorthandType(key, sch[key])

        const bothArray = Array.isArray(dat[key]) && Array.isArray(sch[key])
        const datObjectWithoutSchemaType = isPlainObject(dat[key]) && !sch[key].type
        const datObjectContainsTypeKey = (dat[key] && dat[key].type && (sch[key] && sch[key].type))
        const notInstanceOfFileObject = !(dat[key] instanceof File)

        if (bothArray || datObjectWithoutSchemaType || (datObjectContainsTypeKey && notInstanceOfFileObject)) {
          const { data: flatData, schema: flatSchema } = this.flattenObjects(dat[key], sch[key])
          Object.keys(flatData).forEach(ii => {
            data[key + pathDelimiter + ii] = flatData[ii]
            schema[key + pathDelimiter + ii] = flatSchema[ii]
          })
        } else {
          data[key] = dat[key]
          schema[key] = sch[key]
        }
      })
      return { data, schema }
    },
    combineObjectsToArray ({ data, schema }) {
      const arr = []
      Object.keys(schema).forEach(key => {
        if (!isPlainObject(schema[key])) {
          console.warn(`Schema '${JSON.stringify(schema)}' of Prop '${key}' must be a string with value of type key:'text' or a plainobject with at least key:{ type:'text'} definition.  Prop '${key}' will be ignored!`)
          return
        }
        arr.push({ key, value: data[key], schema: schema[key] })
      })
      return arr
    },
    flattenAndCombineToArray (data, schema) {
      // flatten nested structure of both objects 'data' & 'schema' ...
      const flattenedObjects = this.flattenObjects(data, schema)
      // ... and combine them to an array
      return this.combineObjectsToArray(flattenedObjects)
    },
    autogenerateSchema (value) {
      // generate a minimal default schema from value
      let schema = JSON.stringify(value, (key, val) => val === undefined ? null : val)
      schema = JSON.parse(schema, (key, val) => {
        if (val === null || val === undefined) return defaultSchemaIfValueIsNullOrUndefined(key)
        if (typeof val === 'string') return defaultSchemaIfValueIsString(key)
        if (typeof val === 'number') return defaultSchemaIfValueIsNumber(key)
        if (typeof val === 'boolean') return defaultSchemaIfValueIsBoolean(key)
        return val
      })
      // assign root props to avoid manipulating prop: schema
      Object.keys(schema).forEach(key => this.formSchema[key] = schema[key])
    },

    tryAutogenerateModelStructure (model, schema) {
      // generate or complete an empty model based on schema structure

      Object.keys(schema).forEach(key => {
        // model must be at least an empty Object. It doesn't work with 'null', 'undefined' or any 'primitive value'
        // Autogeneration from Schema works only if model is an empty Object
        // if model is NOT an empty Object, no action is applied, otherwise data in model could be changed or modified
        if (!isEmpty(model[key])) return

        const val = schema[key]
        if (val.type === 'group') {
          model[key] = {}
          this.tryAutogenerateModelStructure(model[key], val.schema)
        } else
          if (val.type === 'array') {
            model[key] = {}
            this.tryAutogenerateModelStructure(model[key], val.schema)
          } else
            if (val.type === 'list') {
              model[key] = {}
            } else
              if (isPlainObject(val) && !val.type) {
                model[key] = {}
                this.tryAutogenerateModelStructure(model[key], val)
              }
        if (Array.isArray(val) && !val.type) {
          model[key] = {}
          this.tryAutogenerateModelStructure(model[key], val)
        }
      })
    },
    rebuildArrays (model, schema) {
      // undefined, null or primitive value for model can't work because you need reference to object for working on
      if (!model) throw 'Property \'model\' is null or undefined. Use \'<v-form-base :model="myModel" :schema="mySchema" />\'. myModel must be at least an empty Object.'

      // noise if async loading
      if (isEmpty(schema) && isEmpty(model)) {
        console.warn('At least one of the properties \'model\' or \'schema\' in <v-form-base /> must be at least an empty Object. Ignore this Warning on async loading \'model\' or \'schema\' ')
        // return
      }
      // generate/complete structure of model
      this.tryAutogenerateModelStructure(model, schema)

      // no schema defined or empty -> autogenerate basic schema
      if (isEmpty(schema)) this.autogenerateSchema(model)

      // create flatted working array from schema and value
      // __log(
      //   this.storeStateData,
      //   this.storeStateSchema
      //   // this.flattenAndCombineToArray(this.storeStateData, this.storeStateSchema)
      // )
      // __log(this.storeStateData, this.storeStateSchema)
      this.flatCombinedArray = this.flattenAndCombineToArray(this.storeStateData, this.storeStateSchema)
    },

    setCascadeSelect (obj) {
      if (obj.schema.type === 'select' && obj.schema.hasOwnProperty('cascade')) {
        const cascadedSelectName = obj.schema.cascade
        const selectItemValue = obj.schema.itemValue ?? 'id'

        this.formSchema[cascadedSelectName].items = find(obj.schema.items, [selectItemValue, this.valueIntern[obj.key]]).items ?? []

        const sortIndex = findIndex(this.flatCombinedArraySorted, ['key', cascadedSelectName])
        // this.onInput(this.formSchema[obj.schema.cascade].items[0].value, this.flatCombinedArraySorted[sortedIndex], 'change')

        this.storeStateData[cascadedSelectName] = this.formSchema[cascadedSelectName].items.length > 0 ? this.formSchema[cascadedSelectName].items[0].value : null
        this.flatCombinedArraySorted[sortIndex].value = this.valueIntern[cascadedSelectName]

        // __log(
        //   'setCascadeSelect()',
        //   this.$lodash.pick(this.storeStateData, ['country_id', 'city_id', 'district_id']),
        //   this.$lodash.pick(this.valueIntern, ['country_id', 'city_id', 'district_id']),
        //   this.$lodash.pick(this.model, ['country_id', 'city_id', 'district_id']),
        //   this.$lodash.pick(this.modelValue, ['country_id', 'city_id', 'district_id'])
        // )
        this.setCascadeSelect(this.flatCombinedArraySorted[sortIndex])
      }
    }
  }

}
