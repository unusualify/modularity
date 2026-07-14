// editorExtensions.js
// Slash commands, mention extension factories, and interactive extension builder.

import { Extension } from '@tiptap/core'
import Suggestion from '@tiptap/suggestion'
import { PluginKey } from '@tiptap/pm/state'
import Mention from '@tiptap/extension-mention'

export const SLASH_COMMANDS = [
  {
    group: 'Temel',
    items: [
      { id: 'paragraph', label: 'Paragraf', icon: 'mdi-text', desc: 'Normal metin bloğu' },
      { id: 'h1', label: 'Başlık 1', icon: 'mdi-format-header-1', desc: 'Büyük bölüm başlığı' },
      { id: 'h2', label: 'Başlık 2', icon: 'mdi-format-header-2', desc: 'Alt bölüm başlığı' },
      { id: 'h3', label: 'Başlık 3', icon: 'mdi-format-header-3', desc: 'Küçük başlık' },
      { id: 'bulletList', label: 'Madde listesi', icon: 'mdi-format-list-bulleted', desc: 'Sırasız liste' },
      { id: 'orderedList', label: 'Numaralı liste', icon: 'mdi-format-list-numbered', desc: 'Sıralı liste' },
      { id: 'taskList', label: 'Görev listesi', icon: 'mdi-checkbox-marked-outline', desc: 'Checkbox\'lı liste' },
    ],
  },
  {
    group: 'İçerik',
    items: [
      { id: 'blockquote', label: 'Alıntı', icon: 'mdi-format-quote-open', desc: 'Alıntı bloğu' },
      { id: 'codeBlock', label: 'Kod bloğu', icon: 'mdi-code-braces', desc: 'Mono-space kod alanı' },
      { id: 'table', label: 'Tablo', icon: 'mdi-table', desc: '3×3 tablo ekle' },
      { id: 'image', label: 'Görsel', icon: 'mdi-image', desc: 'Görsel yükle' },
      { id: 'horizontalRule', label: 'Ayraç', icon: 'mdi-minus', desc: 'Yatay çizgi' },
      { id: 'pageBreak', label: 'Sayfa sonu', icon: 'mdi-file-document-outline', desc: 'Yazdırma sayfa sonu' },
    ],
  },
]

export function runSlashCommand (id, editor, helpers = {}) {
  const chain = editor.chain().focus()

  switch (id) {
    case 'paragraph':
      chain.setParagraph().run()
      break
    case 'h1':
      chain.toggleHeading({ level: 1 }).run()
      break
    case 'h2':
      chain.toggleHeading({ level: 2 }).run()
      break
    case 'h3':
      chain.toggleHeading({ level: 3 }).run()
      break
    case 'bulletList':
      chain.toggleBulletList().run()
      break
    case 'orderedList':
      chain.toggleOrderedList().run()
      break
    case 'taskList':
      chain.toggleTaskList().run()
      break
    case 'blockquote':
      chain.toggleBlockquote().run()
      break
    case 'codeBlock':
      chain.toggleCodeBlock().run()
      break
    case 'table':
      chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()
      break
    case 'image':
      helpers.openImageUpload?.()
      break
    case 'horizontalRule':
      chain.setHorizontalRule().run()
      break
    case 'pageBreak':
      chain.insertContent('<div data-page-break="true" style="page-break-after:always;"></div>').run()
      break
    default:
      break
  }
}

export function createSlashCommandExtension (renderFns, options = {}) {
  return Extension.create({
    name: 'slashCommand',

    addOptions () {
      return {
        suggestion: {},
        openImageUpload: options.openImageUpload ?? null,
      }
    },

    addProseMirrorPlugins () {
      return [
        Suggestion({
          editor: this.editor,
          pluginKey: new PluginKey('slashCommand'),
          char: '/',
          allowSpaces: false,
          startOfLine: false,
          command: ({ editor, range, props }) => {
            editor.chain().focus().deleteRange(range).run()
            runSlashCommand(props.id, editor, {
              openImageUpload: this.options.openImageUpload,
            })
          },
          items: ({ query }) => {
            const q = query.toLowerCase()

            return SLASH_COMMANDS.flatMap((group) => group.items)
              .filter((item) =>
                item.label.toLowerCase().includes(q)
                || item.id.toLowerCase().includes(q),
              )
              .slice(0, 12)
          },
          ...renderFns,
        }),
      ]
    },
  })
}

export function createMentionExtension (items = [], renderFns = {}) {
  return Mention.configure({
    HTMLAttributes: {
      class: 'ue-mention',
    },
    renderHTML ({ options, node }) {
      return [
        'span',
        {
          ...options.HTMLAttributes,
          'data-id': node.attrs.id,
          'data-label': node.attrs.label,
        },
        `${options.suggestion.char}${node.attrs.label ?? node.attrs.id}`,
      ]
    },
    suggestion: {
      items: async ({ query }) => {
        const list = typeof items === 'function' ? await items(query) : items

        return list
          .filter((item) => item.label.toLowerCase().includes(query.toLowerCase()))
          .slice(0, 8)
      },
      ...renderFns,
    },
  })
}

export function buildInteractiveExtensions ({
  mentionItems = [],
  slashRenderFns = {},
  mentionRenderFns = {},
  openImageUpload = null,
} = {}) {
  return [
    createSlashCommandExtension(slashRenderFns, { openImageUpload }),
    createMentionExtension(mentionItems, mentionRenderFns),
  ]
}
