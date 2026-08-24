<template>
  <div class="editor-image-bubble d-flex flex-wrap align-center ga-1 pa-1">
    <v-tooltip location="top" text="Alternative text">
      <template #activator="{ props: tooltipProps }">
        <v-btn
          v-bind="tooltipProps"
          size="x-small"
          variant="text"
          icon="mdi-image-text"
          @click="altDialog = true"
        />
      </template>
    </v-tooltip>

    <v-divider vertical class="mx-1 align-self-stretch" thickness="1" />

    <v-tooltip
      v-for="option in alignOptions"
      :key="option.id"
      location="top"
      :text="option.label"
    >
      <template #activator="{ props: tooltipProps }">
        <v-btn
          v-bind="tooltipProps"
          size="x-small"
          variant="text"
          :color="isAlignActive(option) ? 'primary' : undefined"
          :icon="option.icon"
          @click="applyAlign(option)"
        />
      </template>
    </v-tooltip>

    <v-divider vertical class="mx-1 align-self-stretch" thickness="1" />

    <v-menu location="bottom">
      <template #activator="{ props: menuProps }">
        <v-btn
          v-bind="menuProps"
          size="x-small"
          variant="tonal"
          class="text-body-small px-2"
        >
          {{ currentWidthLabel }}
        </v-btn>
      </template>
      <v-list density="compact">
        <v-list-item
          v-for="preset in widthPresets"
          :key="preset"
          :title="`${preset}%`"
          @click="setWidth(`${preset}%`)"
        />
        <v-divider />
        <v-list-item title="Custom width..." @click="customWidthDialog = true" />
      </v-list>
    </v-menu>

    <v-divider vertical class="mx-1 align-self-stretch" thickness="1" />

    <v-tooltip location="top" text="Image link">
      <template #activator="{ props: tooltipProps }">
        <v-btn
          v-bind="tooltipProps"
          size="x-small"
          variant="text"
          :color="imageAttributes.href ? 'primary' : undefined"
          icon="mdi-link-variant"
          @click="linkDialog = true"
        />
      </template>
    </v-tooltip>

    <v-tooltip location="top" text="Remove image">
      <template #activator="{ props: tooltipProps }">
        <v-btn
          v-bind="tooltipProps"
          size="x-small"
          variant="text"
          color="error"
          icon="mdi-delete-outline"
          @click="removeImage"
        />
      </template>
    </v-tooltip>

    <v-dialog v-model="altDialog" max-width="480">
      <v-card>
        <v-card-title>Alternative text</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="altText"
            label="Alt text"
            variant="outlined"
            density="compact"
            hide-details="auto"
            autofocus
            @keyup.enter="applyAlt"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="altDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" @click="applyAlt">Apply</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="linkDialog" max-width="480">
      <v-card>
        <v-card-title>Image link</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="linkUrl"
            label="URL"
            variant="outlined"
            density="compact"
            hide-details="auto"
            autofocus
            @keyup.enter="applyLink"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="linkDialog = false">Cancel</v-btn>
          <v-btn
            v-if="imageAttributes.href"
            variant="text"
            color="error"
            @click="removeLink"
          >
            Remove
          </v-btn>
          <v-btn color="primary" variant="flat" @click="applyLink">Apply</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="customWidthDialog" max-width="360">
      <v-card>
        <v-card-title>Custom image width</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="customWidth"
            label="Width (%)"
            type="number"
            min="10"
            max="100"
            suffix="%"
            variant="outlined"
            density="compact"
            hide-details="auto"
            autofocus
            @keyup.enter="applyCustomWidth"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="customWidthDialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" @click="applyCustomWidth">Apply</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { IMAGE_WIDTH_PRESETS } from '@/config/editor/editorImageExtension.js'

export default {
  name: 'EditorImageBubble',
  props: {
    editor: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      altDialog: false,
      linkDialog: false,
      customWidthDialog: false,
      altText: '',
      linkUrl: '',
      customWidth: '100',
      widthPresets: IMAGE_WIDTH_PRESETS,
      alignOptions: [
        { id: 'inline-left', label: 'Wrap left', icon: 'mdi-format-float-left', align: 'left', float: 'left' },
        { id: 'center', label: 'Center', icon: 'mdi-format-align-center', align: 'center', float: 'none' },
        { id: 'inline-right', label: 'Wrap right', icon: 'mdi-format-float-right', align: 'right', float: 'right' },
        { id: 'block-left', label: 'Align left', icon: 'mdi-format-align-left', align: 'left', float: 'none' },
        { id: 'block-right', label: 'Align right', icon: 'mdi-format-align-right', align: 'right', float: 'none' },
      ],
    }
  },
  computed: {
    imageAttributes () {
      return this.editor?.getAttributes('image') ?? {}
    },
    currentWidthLabel () {
      const width = this.imageAttributes.width ?? '100%'

      return String(width).includes('%') ? String(width) : `${width}%`
    },
  },
  watch: {
    altDialog (open) {
      if (open) {
        this.altText = this.imageAttributes.alt ?? ''
      }
    },
    linkDialog (open) {
      if (open) {
        this.linkUrl = this.imageAttributes.href ?? ''
      }
    },
    customWidthDialog (open) {
      if (open) {
        const width = String(this.imageAttributes.width ?? '100').replace('%', '')
        this.customWidth = width
      }
    },
  },
  methods: {
    isAlignActive (option) {
      const { align, float } = this.imageAttributes

      return align === option.align && float === option.float
    },
    applyAlign (option) {
      this.editor.chain().focus().setImageAlign({
        align: option.align,
        float: option.float,
      }).run()
    },
    setWidth (width) {
      this.editor.chain().focus().setImageWidth(width).run()
    },
    applyCustomWidth () {
      const value = Number(this.customWidth)

      if (!Number.isFinite(value)) {
        return
      }

      this.setWidth(`${Math.min(100, Math.max(10, value))}%`)
      this.customWidthDialog = false
    },
    applyAlt () {
      this.editor.chain().focus().setImageAlt(this.altText.trim()).run()
      this.altDialog = false
    },
    applyLink () {
      this.editor.chain().focus().setImageLink(this.linkUrl.trim()).run()
      this.linkDialog = false
    },
    removeLink () {
      this.editor.chain().focus().setImageLink(null).run()
      this.linkDialog = false
    },
    removeImage () {
      this.editor.chain().focus().deleteSelection().run()
    },
  },
}
</script>

<style lang="scss">
.editor-image-bubble {
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}
</style>
