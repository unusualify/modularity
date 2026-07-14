<template>
  <div class="ue-input-editor__toolbar">
    <div
      v-for="(row, rowIndex) in resolvedRows"
      :key="`toolbar-row-${rowIndex}`"
      class="ue-input-editor__toolbar-row d-flex flex-wrap align-center ga-1 px-1 py-1"
    >
      <template v-for="(item, index) in row" :key="`${rowIndex}-${item}-${index}`">
        <v-divider
          v-if="item === '|'"
          vertical
          class="ue-toolbar-divider mx-1 align-self-stretch"
          thickness="1"
        />

        <v-menu
          v-else-if="item === 'heading'"
          location="bottom"
        >
          <template #activator="{ props: menuProps }">
            <v-btn
              v-bind="menuProps"
              size="small"
              :variant="headingMenuVariant"
              :color="headingMenuColor"
              :disabled="disabled"
              class="ue-toolbar-heading-btn text-none"
            >
              {{ currentHeadingLabel }}
              <v-icon end size="16">
                mdi-chevron-down
              </v-icon>
            </v-btn>
          </template>
          <v-list density="compact">
            <v-list-item
              title="Paragraf"
              :active="isParagraphActive"
              @click="setParagraph"
            />
            <v-list-item
              v-for="level in headingLevels"
              :key="level.value"
              :title="level.title"
              :active="isHeadingLevelActive(level.value)"
              @click="setHeading(level.value)"
            />
          </v-list>
        </v-menu>

        <v-menu
          v-else-if="getToolbarButton(item).type === 'menu'"
          location="bottom"
        >
          <template #activator="{ props: menuProps }">
            <v-tooltip location="top" :text="getToolbarButton(item).label">
              <template #activator="{ props: tooltipProps }">
                <v-btn
                  v-bind="{ ...menuProps, ...tooltipProps }"
                  size="x-small"
                  :variant="buttonVariant(item)"
                  :color="buttonColor(item)"
                  :disabled="disabled || isDisabled(item)"
                  :icon="getToolbarButton(item).icon"
                />
              </template>
            </v-tooltip>
          </template>
          <v-list density="compact" class="editor-toolbar-menu">
            <template v-if="item === 'fontColor' || item === 'fontBackgroundColor'">
              <v-list-item
                v-for="swatch in getToolbarButton(item).items"
                :key="swatch.value"
                @click="runMenuAction(item, swatch.value)"
              >
                <template #prepend>
                  <span class="editor-toolbar-menu__swatch" :style="{ backgroundColor: swatch.value }" />
                </template>
                <v-list-item-title>{{ swatch.value }}</v-list-item-title>
              </v-list-item>
            </template>
            <template v-else>
              <v-list-item
                v-for="menuItem in getToolbarButton(item).items"
                :key="`${item}-${menuItem.title}-${menuItem.value}`"
                :title="menuItem.title"
                @click="runMenuAction(item, menuItem.value)"
              />
            </template>
          </v-list>
        </v-menu>

        <v-tooltip
          v-else-if="item === 'source'"
          location="top"
          :text="getToolbarButton(item).label"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              size="x-small"
              :variant="buttonVariant(item)"
              :color="buttonColor(item)"
              :disabled="disabled || isDisabled(item)"
              prepend-icon="mdi-code-brackets"
              class="ue-toolbar-source-btn text-none"
              @click="runAction(item)"
            >
              Source
            </v-btn>
          </template>
        </v-tooltip>

        <v-tooltip
          v-else
          location="top"
          :text="getToolbarButton(item).label"
        >
          <template #activator="{ props: tooltipProps }">
            <v-btn
              v-bind="tooltipProps"
              size="x-small"
              :variant="buttonVariant(item)"
              :color="buttonColor(item)"
              :disabled="disabled || isDisabled(item)"
              :icon="getToolbarButton(item).icon"
              @click="runAction(item)"
            />
          </template>
        </v-tooltip>
      </template>
    </div>

    <div
      v-if="showWordCount && wordCount"
      class="ue-input-editor__toolbar-footer text-caption text-medium-emphasis px-2 pb-1"
    >
      {{ wordCount.words }} kelime · {{ wordCount.chars }} karakter
    </div>

    <input
      ref="imageInput"
      type="file"
      accept="image/*"
      class="d-none"
      @change="onImageSelected"
    >

    <v-dialog v-model="linkDialog" max-width="480">
      <v-card>
        <v-card-title>Bağlantı ekle</v-card-title>
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
          <v-btn variant="text" @click="linkDialog = false">
            İptal
          </v-btn>
          <v-btn
            v-if="editor?.isActive('link')"
            variant="text"
            color="error"
            @click="removeLink"
          >
            Kaldır
          </v-btn>
          <v-btn color="primary" variant="flat" @click="applyLink">
            Uygula
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="mediaEmbedDialog" max-width="520">
      <v-card>
        <v-card-title>Medya gömme</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="mediaEmbedUrl"
            label="Video veya embed URL"
            variant="outlined"
            density="compact"
            hide-details="auto"
            autofocus
            @keyup.enter="applyMediaEmbed"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="mediaEmbedDialog = false">
            İptal
          </v-btn>
          <v-btn color="primary" variant="flat" @click="applyMediaEmbed">
            Ekle
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="htmlEmbedDialog" max-width="640">
      <v-card>
        <v-card-title>HTML gömme</v-card-title>
        <v-card-text>
          <v-textarea
            v-model="htmlEmbedContent"
            label="HTML"
            variant="outlined"
            density="compact"
            rows="8"
            hide-details="auto"
            autofocus
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="htmlEmbedDialog = false">
            İptal
          </v-btn>
          <v-btn color="primary" variant="flat" @click="applyHtmlEmbed">
            Ekle
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import {
  createEditorUploadFolderName,
  uploadEditorImage,
} from '@/config/editor/editorUpload.js'
import { getToolbarButton, HEADING_LEVELS } from '@/config/editor/editorToolbar.js'
import { DEFAULT_TOOLBAR_ROWS } from '@/config/editor/editorConfig.js'

export default {
  name: 'EditorToolbar',
  props: {
    editor: { type: Object, default: null },
    rows: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    disabled: { type: Boolean, default: false },
    uploadUrl: { type: String, default: null },
    sourceMode: { type: Boolean, default: false },
    showWordCount: { type: Boolean, default: false },
    wordCount: { type: Object, default: null },
  },
  emits: ['toggle-source', 'upload-error'],
  data () {
    return {
      linkDialog: false,
      mediaEmbedDialog: false,
      htmlEmbedDialog: false,
      linkUrl: '',
      mediaEmbedUrl: '',
      htmlEmbedContent: '',
      uploadFolderName: null,
      headingLevels: HEADING_LEVELS,
      editorRevision: 0,
      detachEditorListeners: null,
    }
  },
  computed: {
    resolvedRows () {
      if (this.rows?.length) {
        return this.rows
      }

      if (this.items?.length) {
        return [this.items]
      }

      return DEFAULT_TOOLBAR_ROWS
    },
    currentHeadingLabel () {
      void this.editorRevision

      if (!this.editor) {
        return 'Paragraf'
      }

      for (const level of this.headingLevels) {
        if (this.editor.isActive('heading', { level: level.value })) {
          return level.title
        }
      }

      return 'Paragraf'
    },
    isParagraphActive () {
      void this.editorRevision

      return this.editor?.isActive('paragraph') ?? false
    },
    headingMenuVariant () {
      return this.isParagraphActive || this.isAnyHeadingActive() ? 'tonal' : 'outlined'
    },
    headingMenuColor () {
      return this.isParagraphActive || this.isAnyHeadingActive() ? 'primary' : undefined
    },
  },
  watch: {
    editor: {
      immediate: true,
      handler (editor) {
        this.detachEditorListeners?.()
        this.detachEditorListeners = null

        if (!editor?.on) {
          return
        }

        const bumpRevision = () => {
          this.editorRevision += 1
        }

        editor.on('transaction', bumpRevision)
        editor.on('selectionUpdate', bumpRevision)

        this.detachEditorListeners = () => {
          editor.off('transaction', bumpRevision)
          editor.off('selectionUpdate', bumpRevision)
        }
      },
    },
  },
  beforeUnmount () {
    this.detachEditorListeners?.()
  },
  methods: {
    getToolbarButton,
    isAnyHeadingActive () {
      void this.editorRevision

      if (!this.editor) {
        return false
      }

      return this.headingLevels.some((level) => this.editor.isActive('heading', { level: level.value }))
    },
    isHeadingLevelActive (level) {
      void this.editorRevision

      return this.editor?.isActive('heading', { level }) ?? false
    },
    buttonVariant (item) {
      return this.isActive(item) ? 'tonal' : 'text'
    },
    buttonColor (item) {
      return this.isActive(item) ? 'primary' : undefined
    },
    isActive (item) {
      void this.editorRevision

      if (!this.editor) {
        return false
      }

      if (item === 'source') {
        return this.sourceMode
      }

      const activeMap = {
        bold: 'bold',
        italic: 'italic',
        underline: 'underline',
        strike: 'strike',
        code: 'code',
        subscript: 'subscript',
        superscript: 'superscript',
        bulletList: 'bulletList',
        orderedList: 'orderedList',
        taskList: 'taskList',
        blockquote: 'blockquote',
        codeBlock: 'codeBlock',
        highlight: 'highlight',
        link: 'link',
        alignLeft: { name: 'textAlign', attrs: { textAlign: 'left' } },
        alignCenter: { name: 'textAlign', attrs: { textAlign: 'center' } },
        alignRight: { name: 'textAlign', attrs: { textAlign: 'right' } },
        alignJustify: { name: 'textAlign', attrs: { textAlign: 'justify' } },
      }

      const active = activeMap[item]

      if (!active) {
        return false
      }

      if (typeof active === 'string') {
        return this.editor.isActive(active)
      }

      return this.editor.isActive(active.name, active.attrs)
    },
    isDisabled (item) {
      if (!this.editor) {
        return true
      }

      if (item === 'undo') {
        return !this.editor.can().undo()
      }

      if (item === 'redo') {
        return !this.editor.can().redo()
      }

      if (item === 'outdent') {
        return !this.editor.can().liftListItem('listItem')
      }

      if (item === 'indent') {
        return !this.editor.can().sinkListItem('listItem')
      }

      return false
    },
    runMenuAction (item, value) {
      if (!this.editor) {
        return
      }

      switch (item) {
        case 'fontFamily':
          value
            ? this.editor.chain().focus().setFontFamily(value).run()
            : this.editor.chain().focus().unsetFontFamily().run()
          break
        case 'fontSize':
          value
            ? this.editor.chain().focus().setFontSize(value).run()
            : this.editor.chain().focus().unsetFontSize().run()
          break
        case 'fontColor':
          value
            ? this.editor.chain().focus().setColor(value).run()
            : this.editor.chain().focus().unsetColor().run()
          break
        case 'fontBackgroundColor':
          value
            ? this.editor.chain().focus().setBackgroundColor(value).run()
            : this.editor.chain().focus().unsetBackgroundColor().run()
          break
        case 'specialCharacters':
          this.editor.chain().focus().insertContent(value).run()
          break
        default:
          break
      }
    },
    runAction (item) {
      if (!this.editor && item !== 'source') {
        return
      }

      switch (item) {
        case 'source':
          this.$emit('toggle-source')
          break
        case 'selectAll':
          this.editor.chain().focus().selectAll().run()
          break
        case 'undo':
          this.editor.chain().focus().undo().run()
          break
        case 'redo':
          this.editor.chain().focus().redo().run()
          break
        case 'bold':
          this.editor.chain().focus().toggleBold().run()
          break
        case 'italic':
          this.editor.chain().focus().toggleItalic().run()
          break
        case 'underline':
          this.editor.chain().focus().toggleUnderline().run()
          break
        case 'strike':
          this.editor.chain().focus().toggleStrike().run()
          break
        case 'code':
          this.editor.chain().focus().toggleCode().run()
          break
        case 'subscript':
          this.editor.chain().focus().toggleSubscript().run()
          break
        case 'superscript':
          this.editor.chain().focus().toggleSuperscript().run()
          break
        case 'clearFormat':
          this.editor.chain().focus().clearNodes().unsetAllMarks().run()
          break
        case 'bulletList':
          this.editor.chain().focus().toggleBulletList().run()
          break
        case 'orderedList':
          this.editor.chain().focus().toggleOrderedList().run()
          break
        case 'taskList':
          this.editor.chain().focus().toggleTaskList().run()
          break
        case 'outdent':
          this.editor.chain().focus().liftListItem('listItem').run()
          break
        case 'indent':
          this.editor.chain().focus().sinkListItem('listItem').run()
          break
        case 'alignLeft':
          this.editor.chain().focus().setTextAlign('left').run()
          break
        case 'alignCenter':
          this.editor.chain().focus().setTextAlign('center').run()
          break
        case 'alignRight':
          this.editor.chain().focus().setTextAlign('right').run()
          break
        case 'alignJustify':
          this.editor.chain().focus().setTextAlign('justify').run()
          break
        case 'link':
          this.openLinkDialog()
          break
        case 'image':
          this.openImageUpload()
          break
        case 'blockquote':
          this.editor.chain().focus().toggleBlockquote().run()
          break
        case 'table':
          this.editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
          break
        case 'mediaEmbed':
          this.mediaEmbedDialog = true
          break
        case 'codeBlock':
          this.editor.chain().focus().toggleCodeBlock().run()
          break
        case 'htmlEmbed':
          this.htmlEmbedDialog = true
          break
        case 'horizontalRule':
          this.editor.chain().focus().setHorizontalRule().run()
          break
        case 'pageBreak':
          this.editor.chain().focus().insertContent('<div data-page-break="true" style="page-break-after: always;"></div>').run()
          break
        case 'highlight':
          this.editor.chain().focus().toggleHighlight().run()
          break
        default:
          break
      }
    },
    setHeading (level) {
      this.editor?.chain().focus().toggleHeading({ level }).run()
    },
    setParagraph () {
      this.editor?.chain().focus().setParagraph().run()
    },
    openLinkDialog () {
      this.linkUrl = this.editor?.getAttributes('link').href ?? ''
      this.linkDialog = true
    },
    applyLink () {
      const url = this.linkUrl.trim()

      if (!url) {
        this.linkDialog = false
        return
      }

      this.editor?.chain().focus().extendMarkRange('link').setLink({ href: url, target: '_blank' }).run()
      this.linkDialog = false
    },
    removeLink () {
      this.editor?.chain().focus().unsetLink().run()
      this.linkDialog = false
    },
    applyMediaEmbed () {
      const url = this.mediaEmbedUrl.trim()

      if (!url || !this.editor) {
        this.mediaEmbedDialog = false
        return
      }

      const embedUrl = this.normalizeEmbedUrl(url)
      this.editor.chain().focus().insertContent(
        `<div class="media-embed"><iframe src="${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>`,
      ).run()
      this.mediaEmbedUrl = ''
      this.mediaEmbedDialog = false
    },
    applyHtmlEmbed () {
      const html = this.htmlEmbedContent.trim()

      if (!html || !this.editor) {
        this.htmlEmbedDialog = false
        return
      }

      this.editor.chain().focus().insertContent(html).run()
      this.htmlEmbedContent = ''
      this.htmlEmbedDialog = false
    },
    normalizeEmbedUrl (url) {
      const yt = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/)

      return yt ? `https://www.youtube.com/embed/${yt[1]}` : url
    },
    openImageUpload () {
      this.$refs.imageInput?.click()
    },
    async onImageSelected (event) {
      const file = event.target.files?.[0]
      event.target.value = ''

      if (!file || !this.editor) {
        return
      }

      if (!this.uploadUrl) {
        this.$emit('upload-error', new Error('Image upload endpoint is not configured.'))
        return
      }

      try {
        this.uploadFolderName = this.uploadFolderName || createEditorUploadFolderName()
        const url = await uploadEditorImage(file, this.uploadUrl, { uniqueFolderName: this.uploadFolderName })
        this.editor.chain().focus().setImage({ src: url, width: '100%', align: 'center', float: 'none' }).run()
      } catch (error) {
        this.$emit('upload-error', error)
      }
    },
  },
}
</script>

<style lang="scss">
.ue-input-editor__toolbar {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  background: rgba(var(--v-theme-on-surface), 0.03);
}

.ue-input-editor__toolbar-row + .ue-input-editor__toolbar-row {
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.ue-input-editor__toolbar-row {
  min-width: 0;
  row-gap: 2px;
}

.ue-toolbar-divider {
  align-self: stretch;
  min-height: 24px;
}

.ue-toolbar-heading-btn {
  min-width: 112px;
  justify-content: space-between;
  font-size: 13px;
  letter-spacing: normal;
}

.ue-toolbar-source-btn {
  min-width: 72px;
  font-size: 12px;
  letter-spacing: normal;
}

.ue-input-editor__toolbar-footer {
  border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  font-size: 11px !important;
  opacity: 0.65;
  text-align: right;
}

.editor-toolbar-menu {
  &__swatch {
    display: inline-block;
    width: 16px;
    height: 16px;
    border-radius: 3px;
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  }
}
</style>
