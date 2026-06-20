export const FONT_FAMILIES = [
  { title: 'Default', value: '' },
  { title: 'Arial', value: 'Arial, Helvetica, sans-serif' },
  { title: 'Courier New', value: 'Courier New, Courier, monospace' },
  { title: 'Georgia', value: 'Georgia, serif' },
  { title: 'Tahoma', value: 'Tahoma, Geneva, sans-serif' },
  { title: 'Times New Roman', value: 'Times New Roman, Times, serif' },
  { title: 'Trebuchet MS', value: 'Trebuchet MS, Helvetica, sans-serif' },
  { title: 'Verdana', value: 'Verdana, Geneva, sans-serif' },
]

export const FONT_SIZES = [
  { title: '10', value: '10px' },
  { title: '12', value: '12px' },
  { title: '14', value: '14px' },
  { title: '16', value: '16px' },
  { title: '18', value: '18px' },
  { title: '20', value: '20px' },
  { title: '22', value: '22px' },
  { title: '24', value: '24px' },
]

export const COLOR_SWATCHES = [
  '#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#efefef', '#f3f3f3', '#ffffff',
  '#980000', '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#4a86e8', '#0000ff', '#9900ff', '#ff00ff',
  '#e6b8af', '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#c9daf8', '#cfe2f3', '#d9d2e9', '#ead1dc',
]

export const SPECIAL_CHARACTERS = [
  '©', '®', '™', '€', '£', '¥', '§', '¶', '•', '…', '—', '–', '°', '±', '×', '÷', '½', '¼', '¾', 'α', 'β', 'π', 'Ω', '→', '←', '↑', '↓', '✓', '✗',
]

/**
 * @typedef {'action'|'menu'|'dialog'} ToolbarButtonType
 */

/**
 * @typedef {Object} ToolbarButtonDefinition
 * @property {string} icon
 * @property {string} label
 * @property {ToolbarButtonType} [type]
 * @property {Array<{ title: string, value?: string }>} [items]
 */

/** @type {Record<string, ToolbarButtonDefinition>} */
export const TOOLBAR_BUTTONS = {
  selectAll: { icon: 'mdi-select-all', label: 'Select all', type: 'action' },
  undo: { icon: 'mdi-undo', label: 'Undo', type: 'action' },
  redo: { icon: 'mdi-redo', label: 'Redo', type: 'action' },
  heading: { icon: 'mdi-format-header-1', label: 'Heading', type: 'menu' },
  bold: { icon: 'mdi-format-bold', label: 'Bold', type: 'action' },
  italic: { icon: 'mdi-format-italic', label: 'Italic', type: 'action' },
  underline: { icon: 'mdi-format-underline', label: 'Underline', type: 'action' },
  strike: { icon: 'mdi-format-strikethrough', label: 'Strikethrough', type: 'action' },
  code: { icon: 'mdi-code-tags', label: 'Inline code', type: 'action' },
  subscript: { icon: 'mdi-format-subscript', label: 'Subscript', type: 'action' },
  superscript: { icon: 'mdi-format-superscript', label: 'Superscript', type: 'action' },
  clearFormat: { icon: 'mdi-format-clear', label: 'Remove formatting', type: 'action' },
  bulletList: { icon: 'mdi-format-list-bulleted', label: 'Bulleted list', type: 'action' },
  orderedList: { icon: 'mdi-format-list-numbered', label: 'Numbered list', type: 'action' },
  taskList: { icon: 'mdi-format-list-checkbox', label: 'To-do list', type: 'action' },
  outdent: { icon: 'mdi-format-indent-decrease', label: 'Decrease indent', type: 'action' },
  indent: { icon: 'mdi-format-indent-increase', label: 'Increase indent', type: 'action' },
  fontFamily: { icon: 'mdi-format-font', label: 'Font family', type: 'menu', items: FONT_FAMILIES },
  fontSize: { icon: 'mdi-format-size', label: 'Font size', type: 'menu', items: FONT_SIZES },
  fontColor: { icon: 'mdi-format-color-text', label: 'Text color', type: 'menu', items: COLOR_SWATCHES.map((value) => ({ title: value, value })) },
  fontBackgroundColor: { icon: 'mdi-format-color-fill', label: 'Background color', type: 'menu', items: COLOR_SWATCHES.map((value) => ({ title: value, value })) },
  highlight: { icon: 'mdi-marker', label: 'Highlight', type: 'action' },
  alignLeft: { icon: 'mdi-format-align-left', label: 'Align left', type: 'action' },
  alignCenter: { icon: 'mdi-format-align-center', label: 'Align center', type: 'action' },
  alignRight: { icon: 'mdi-format-align-right', label: 'Align right', type: 'action' },
  alignJustify: { icon: 'mdi-format-align-justify', label: 'Justify', type: 'action' },
  link: { icon: 'mdi-link-variant', label: 'Link', type: 'dialog' },
  image: { icon: 'mdi-image', label: 'Insert image', type: 'action' },
  blockquote: { icon: 'mdi-format-quote-close', label: 'Block quote', type: 'action' },
  table: { icon: 'mdi-table', label: 'Insert table', type: 'action' },
  mediaEmbed: { icon: 'mdi-video', label: 'Media embed', type: 'dialog' },
  codeBlock: { icon: 'mdi-code-braces', label: 'Code block', type: 'action' },
  htmlEmbed: { icon: 'mdi-xml', label: 'HTML embed', type: 'dialog' },
  specialCharacters: { icon: 'mdi-omega', label: 'Special characters', type: 'menu', items: SPECIAL_CHARACTERS.map((value) => ({ title: value, value })) },
  horizontalRule: { icon: 'mdi-minus', label: 'Horizontal line', type: 'action' },
  pageBreak: { icon: 'mdi-format-page-break', label: 'Page break', type: 'action' },
  source: { icon: 'mdi-code-brackets', label: 'Source code', type: 'action' },
}

export const HEADING_LEVELS = [
  { value: 1, title: 'Heading 1' },
  { value: 2, title: 'Heading 2' },
  { value: 3, title: 'Heading 3' },
  { value: 4, title: 'Heading 4' },
  { value: 5, title: 'Heading 5' },
  { value: 6, title: 'Heading 6' },
]

/**
 * @param {string} item
 * @returns {ToolbarButtonDefinition}
 */
export function getToolbarButton (item) {
  return TOOLBAR_BUTTONS[item] ?? { icon: 'mdi-circle-small', label: item, type: 'action' }
}
