import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import TextAlign from '@tiptap/extension-text-align'
import { TableKit } from '@tiptap/extension-table/kit'
import Highlight from '@tiptap/extension-highlight'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import Placeholder from '@tiptap/extension-placeholder'
import HorizontalRule from '@tiptap/extension-horizontal-rule'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import FontFamily from '@tiptap/extension-font-family'
import { Color } from '@tiptap/extension-color'
import { EditorImage } from '@/config/editor/editorImageExtension.js'
import { BackgroundColor, FontSize, TextStyle } from '@/config/editor/editorTextStyleExtensions.js'

/** @typedef {'selectAll'|'undo'|'redo'|'heading'|'bold'|'italic'|'underline'|'strike'|'code'|'subscript'|'superscript'|'clearFormat'|'bulletList'|'orderedList'|'taskList'|'outdent'|'indent'|'fontFamily'|'fontSize'|'fontColor'|'fontBackgroundColor'|'highlight'|'alignLeft'|'alignCenter'|'alignRight'|'alignJustify'|'link'|'image'|'blockquote'|'table'|'mediaEmbed'|'codeBlock'|'htmlEmbed'|'specialCharacters'|'horizontalRule'|'pageBreak'|'source'|string} ToolbarItem */

/** @type {ToolbarItem[][]} */
export const DEFAULT_TOOLBAR_ROWS = [
  [
    'selectAll', '|',
    'heading', '|',
    'bold', 'italic', 'underline', 'strike', 'code', 'subscript', 'superscript', 'clearFormat', '|',
    'bulletList', 'orderedList', 'taskList', '|',
    'outdent', 'indent', '|',
    'undo', 'redo',
  ],
  [
    'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
    'alignLeft', 'alignCenter', 'alignRight', 'alignJustify', '|',
    'link', 'image', 'blockquote', 'table', 'mediaEmbed', 'codeBlock', 'htmlEmbed', '|',
    'specialCharacters', 'horizontalRule', 'pageBreak', '|',
    'source',
  ],
]

/** @type {ToolbarItem[]} */
export const DEFAULT_TOOLBAR_ITEMS = DEFAULT_TOOLBAR_ROWS.flat()

/** Maps legacy CKEditor toolbar ids to TipTap toolbar ids. */
const LEGACY_TOOLBAR_ALIASES = {
  sourceEditing: 'source',
  uploadImage: 'image',
  insertTable: 'table',
  bulletedList: 'bulletList',
  numberedList: 'orderedList',
  todoList: 'taskList',
  strikethrough: 'strike',
  removeFormat: 'clearFormat',
  fontColor: 'fontColor',
  fontBackgroundColor: 'fontBackgroundColor',
  alignment: null,
  horizontalLine: 'horizontalRule',
  ckfinder: 'image',
  findAndReplace: null,
  selectAll: 'selectAll',
  outdent: 'outdent',
  indent: 'indent',
  specialCharacters: 'specialCharacters',
  pageBreak: 'pageBreak',
  mediaEmbed: 'mediaEmbed',
  htmlEmbed: 'htmlEmbed',
}

/**
 * @param {Array<string|Array<string>>} items
 * @returns {ToolbarItem[]}
 */
export function normalizeToolbarItems (items) {
  const flat = items.flat()

  return flat.reduce((acc, item) => {
    if (item === '|' || item === '-') {
      acc.push('|')
      return acc
    }

    const mapped = LEGACY_TOOLBAR_ALIASES[item] ?? item

    if (mapped && mapped !== '|') {
      acc.push(mapped)
    }

    return acc
  }, [])
}

/**
 * @param {Object} [options]
 * @param {string|null} [options.placeholder]
 * @param {Object|null} [options.extraConfig]
 * @returns {import('@tiptap/core').Extensions}
 */
export function buildExtensions ({
  placeholder = null,
  extraConfig = null,
} = {}) {
  const extensions = [
    StarterKit.configure({
      heading: {
        levels: [1, 2, 3, 4, 5, 6],
      },
      horizontalRule: false,
    }),
    Underline,
    Link.configure({
      openOnClick: false,
      autolink: true,
      defaultProtocol: 'https',
    }),
    EditorImage.configure({
      allowBase64: true,
      inline: false,
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    TableKit.configure({
      table: {
        resizable: true,
      },
    }),
    TaskList,
    TaskItem.configure({
      nested: true,
    }),
    TextStyle,
    FontSize,
    FontFamily,
    Color.configure({
      types: ['textStyle'],
    }),
    BackgroundColor,
    Highlight,
    Subscript,
    Superscript,
    HorizontalRule,
  ]

  const resolvedPlaceholder = placeholder
    ?? extraConfig?.placeholder
    ?? null

  if (resolvedPlaceholder) {
    extensions.push(
      Placeholder.configure({
        placeholder: resolvedPlaceholder,
      }),
    )
  }

  return extensions
}

/**
 * @param {Object|null|undefined} toolbar
 * @returns {ToolbarItem[][]}
 */
export function normalizeToolbarRows (toolbar) {
  if (toolbar?.rows?.length) {
    return toolbar.rows.map((row) => normalizeToolbarItems(row))
  }

  const items = toolbar?.items
    ? normalizeToolbarItems(toolbar.items)
    : DEFAULT_TOOLBAR_ROWS.flat()

  const redoIndex = items.indexOf('redo')

  if (redoIndex === -1) {
    return [items]
  }

  let splitAt = redoIndex + 1

  while (splitAt < items.length && items[splitAt] === '|') {
    splitAt += 1
  }

  if (splitAt >= items.length) {
    return [items]
  }

  return [
    items.slice(0, splitAt),
    items.slice(splitAt),
  ]
}

/**
 * Build editor options from schema / component props.
 */
export function buildEditorConfig ({
  height = 300,
  uploadUrl = null,
  language = null,
  toolbar = null,
  extraConfig = null,
} = {}) {
  const toolbarRows = normalizeToolbarRows(toolbar)
  const toolbarItems = toolbarRows.flat()

  return {
    height,
    uploadUrl,
    language,
    toolbarRows,
    toolbarItems,
    placeholder: extraConfig?.placeholder ?? null,
    extraConfig,
    extensions: buildExtensions({
      placeholder: extraConfig?.placeholder ?? null,
      extraConfig,
    }),
  }
}
