<script setup>
  import { computed } from 'vue'
  import { shorten } from '@/utils/helpers'

  /** Max length for `shorten` formatter in table group header row only (default column behavior unchanged). */
  const GROUP_TITLE_SHORTEN_FORMATTER_MAX = 40
  /** Upper bound for any shorten length on mobile (`disableTooltip` / data-table mobile layout). */
  const MOBILE_SHORTEN_MAX_CHARS = 15

  defineOptions({
    name: 'TableFormatterCell'
  })

  const props = defineProps({
    col: {
      type: Object,
      required: true
    },
    item: {
      type: Object,
      required: true
    },
    cellValue: {
      default: undefined
    },
    cellOptions: {
      type: Object,
      default: () => ({})
    },
    handleFormatter: {
      type: Function,
      required: true
    },
    itemAction: {
      type: Function,
      required: true
    },
    clickableRow: {
      type: Boolean,
      default: false
    },
    groupContext: {
      type: Boolean,
      default: false
    },
    /** When true (e.g. `v-data-table` mobile layout), skip `v-tooltip` — no hover on touch UIs. */
    disableTooltip: {
      type: Boolean,
      default: false
    }
  })

  const resolvedValue = computed(() => {
    if (props.cellValue !== undefined && props.cellValue !== null) {
      return props.cellValue
    }
    return props.item[props.col.key]
  })

  /**
   * Plain-text display of `resolvedValue`; on mobile (`disableTooltip`) shortened to {@link MOBILE_SHORTEN_MAX_CHARS}.
   * Objects are left unchanged (same as raw interpolation).
   */
  const displayResolvedValue = computed(() => {
    const v = resolvedValue.value
    if (!props.disableTooltip) {
      return v
    }
    if (v === undefined || v === null || v === '') {
      return v
    }
    if (typeof v === 'object') {
      return v
    }
    return shorten(String(v), MOBILE_SHORTEN_MAX_CHARS)
  })

  const formatterName = computed(() => {
    if (props.col.formatterName) {
      return props.col.formatterName
    }
    if (Array.isArray(props.col.formatter) && props.col.formatter.length) {
      return props.col.formatter[0]
    }
    return props.col.formatter
  })

  const isEditOrActivate = computed(() => {
    return formatterName.value === 'edit' || formatterName.value === 'activate'
  })

  /** Matches legacy `col.formatter == 'edit' || col.formatter == 'activate'` (string or array[0]). */
  const isLegacyEditOrActivate = computed(() => {
    const f = props.col.formatter
    const head = Array.isArray(f) ? f[0] : f
    return head === 'edit' || head === 'activate'
  })

  const formatterSpread = computed(() => {
    const f = props.col.formatter
    return Array.isArray(f) ? f : [f]
  })

  /** Legacy clickable row shorten length; capped on mobile. */
  const legacyClickableShortenLength = computed(() => {
    const base = resolvedValue.value?.textLength ?? 8
    if (!props.disableTooltip) {
      return base
    }
    return Math.min(base, MOBILE_SHORTEN_MAX_CHARS)
  })

  /** First `__shorten` pass uses `cellOptions.maxChars`; on mobile never above MOBILE_SHORTEN_MAX_CHARS. */
  const effectivePreShortenMaxChars = computed(() => {
    const n = props.cellOptions.maxChars
    if (!props.disableTooltip) {
      return n
    }
    if (n === undefined || n === null) {
      return MOBILE_SHORTEN_MAX_CHARS
    }
    return Math.min(Number(n), MOBILE_SHORTEN_MAX_CHARS)
  })

  /**
   * `shorten` formatter arg (e.g. `['shorten', 50]`) capped on mobile so `shorten()` length is at most 10.
   * Used for recursive cells and for edit/activate + `col.isFormatting` (including group header).
   */
  const colFormatterForShortenPass = computed(() => {
    const fmt = props.col.formatter
    if (!props.disableTooltip || !Array.isArray(fmt) || fmt[0] !== 'shorten' || fmt.length < 2) {
      return fmt
    }
    const n = Number(fmt[1])
    if (Number.isNaN(n)) {
      return fmt
    }
    return ['shorten', Math.min(n, MOBILE_SHORTEN_MAX_CHARS)]
  })

  /**
   * Group header + `shorten` formatter: single shorten pass (40 desktop; ≤10 mobile).
   * Other cells: pre-shorten via effectivePreShortenMaxChars then handleFormatter.
   */
  const recursiveFormatterVBind = computed(() => {
    const raw = String(resolvedValue.value ?? '')
    const fmt = props.col.formatter
    const head = Array.isArray(fmt) ? fmt[0] : fmt
    if (props.groupContext && head === 'shorten') {
      const maxLen = props.disableTooltip
        ? Math.min(GROUP_TITLE_SHORTEN_FORMATTER_MAX, MOBILE_SHORTEN_MAX_CHARS)
        : GROUP_TITLE_SHORTEN_FORMATTER_MAX
      return props.handleFormatter(['shorten', maxLen], raw)
    }
    return props.handleFormatter(
      colFormatterForShortenPass.value,
      window.__shorten(raw, effectivePreShortenMaxChars.value)
    )
  })
</script>

<template>
  <div
    :class="groupContext
      ? 'ue-table-formatter-cell--group d-flex min-width-0 flex-nowrap justify-start align-center'
      : 'd-flex'"
  >
    <!-- Legacy clickable rows: edit/activate use spread formatter + shorten -->
    <template v-if="clickableRow && isLegacyEditOrActivate">
      <v-tooltip v-if="!disableTooltip" :text="String(resolvedValue)">
        <template v-slot:activator="{ props: tipProps }">
          <span
            v-bind="tipProps"
            class="pa-0 justify-start text-none text-wrap text-primary darken-1 cursor-pointer"
            @click="itemAction(item, ...formatterSpread)"
          >
            {{ window.__isset(resolvedValue) ? window.__shorten(resolvedValue, legacyClickableShortenLength) : '' }}
          </span>
          <template v-if="col.key.match(/^id|uuid$/)">
            <ue-copy-text :text="resolvedValue" />
          </template>
        </template>
      </v-tooltip>
      <template v-else>
        <span
          class="pa-0 justify-start text-none text-wrap text-primary darken-1 cursor-pointer"
          @click="itemAction(item, ...formatterSpread)"
        >
          {{ window.__isset(resolvedValue) ? window.__shorten(resolvedValue, legacyClickableShortenLength) : '' }}
        </span>
        <template v-if="col.key.match(/^id|uuid$/)">
          <ue-copy-text :text="resolvedValue" />
        </template>
      </template>
    </template>
    <!-- Standard formatter columns: edit/activate -->
    <template v-else-if="!clickableRow && isEditOrActivate && !groupContext">
      <v-tooltip
        v-if="!disableTooltip"
        :text="String(resolvedValue)"
        :disabled="col.isFormatting"
      >
        <template v-slot:activator="{ props: tipProps }">
          <template v-if="(col.hasCopy ?? false) || col.key.match(/^id|uuid$/)">
            <ue-copy-text :text="resolvedValue" class="mr-2" />
          </template>
          <div
            v-bind="tipProps"
            class="justify-start text-none text-wrap text-primary darken-1 cursor-pointer text-truncate"
            @click="itemAction(item, { name: col.formatterName, target: col.target ?? '_blank' })"
          >
            <template v-if="col.isFormatting">
              <ue-recursive-stuff
                v-bind="handleFormatter(colFormatterForShortenPass, resolvedValue)"
              />
            </template>
            <template v-else>
              {{ displayResolvedValue }}
            </template>
          </div>
        </template>
      </v-tooltip>
      <template v-else>
        <template v-if="(col.hasCopy ?? false) || col.key.match(/^id|uuid$/)">
          <ue-copy-text :text="resolvedValue" class="mr-2" />
        </template>
        <div
          class="justify-start text-none text-wrap text-primary darken-1 cursor-pointer text-truncate"
          @click="itemAction(item, { name: col.formatterName, target: col.target ?? '_blank' })"
        >
          <template v-if="col.isFormatting">
            <ue-recursive-stuff
              v-bind="handleFormatter(colFormatterForShortenPass, resolvedValue)"
            />
          </template>
          <template v-else>
            {{ displayResolvedValue }}
          </template>
        </div>
      </template>
    </template>
    <!-- Group header: same visual as cells, no row actions -->
    <template v-else-if="groupContext && isEditOrActivate">
      <div class="ue-table-formatter-cell__group-edit d-inline-flex min-width-0 text-no-wrap text-truncate">
        <template v-if="col.isFormatting">
          <ue-recursive-stuff
            v-bind="handleFormatter(colFormatterForShortenPass, resolvedValue)"
          />
        </template>
        <template v-else>
          {{ displayResolvedValue }}
        </template>
      </div>
    </template>
    <template v-else-if="formatterName === 'switch'">
      <v-switch
        :model-value="resolvedValue"
        color="success"
        :true-value="$lodash.isBoolean(resolvedValue) ? true : 0"
        :false-value="$lodash.isBoolean(resolvedValue) ? false : 0"
        hide-details
        :readonly="groupContext"
        :disabled="groupContext"
        @update:model-value="(v) => !groupContext && itemAction(item, 'switch', v, col.key)"

        v-bind="col?.formatter[1] ?? {}"
      >
        <template v-slot:label />
      </v-switch>
    </template>
    <template v-else-if="formatterName === 'dynamic'">
      <ue-dynamic-component-renderer
        :subject="resolvedValue"
        :key="String(resolvedValue)"
      />
    </template>
    <template v-else>
      <component
        :is="groupContext ? 'span' : 'div'"
        :class="[
          groupContext ? 'ue-table-formatter-cell__group-recursive d-inline-flex min-width-0' : '',
        ]"
      >
        <ue-recursive-stuff
          v-bind="recursiveFormatterVBind"
          :key="String(resolvedValue)"
        />
      </component>
    </template>
  </div>
</template>
