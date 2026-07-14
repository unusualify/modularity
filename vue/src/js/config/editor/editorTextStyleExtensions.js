import { Extension } from '@tiptap/core'
import { TextStyle } from '@tiptap/extension-text-style'

export const FontSize = Extension.create({
  name: 'fontSize',

  addOptions () {
    return {
      types: ['textStyle'],
      sizes: ['10px', '12px', '14px', '16px', '18px', '20px', '22px', '24px'],
    }
  },

  addGlobalAttributes () {
    return [
      {
        types: this.options.types,
        attributes: {
          fontSize: {
            default: null,
            parseHTML: (element) => element.style.fontSize?.replace(/['"]+/g, '') || null,
            renderHTML: (attributes) => {
              if (!attributes.fontSize) {
                return {}
              }

              return { style: `font-size: ${attributes.fontSize}` }
            },
          },
        },
      },
    ]
  },

  addCommands () {
    return {
      setFontSize: (fontSize) => ({ chain }) => {
        return chain().setMark('textStyle', { fontSize }).run()
      },
      unsetFontSize: () => ({ chain }) => {
        return chain().setMark('textStyle', { fontSize: null }).removeEmptyTextStyle().run()
      },
    }
  },
})

export const BackgroundColor = Extension.create({
  name: 'backgroundColor',

  addOptions () {
    return {
      types: ['textStyle'],
    }
  },

  addGlobalAttributes () {
    return [
      {
        types: this.options.types,
        attributes: {
          backgroundColor: {
            default: null,
            parseHTML: (element) => element.style.backgroundColor?.replace(/['"]+/g, '') || null,
            renderHTML: (attributes) => {
              if (!attributes.backgroundColor) {
                return {}
              }

              return { style: `background-color: ${attributes.backgroundColor}` }
            },
          },
        },
      },
    ]
  },

  addCommands () {
    return {
      setBackgroundColor: (backgroundColor) => ({ chain }) => {
        return chain().setMark('textStyle', { backgroundColor }).run()
      },
      unsetBackgroundColor: () => ({ chain }) => {
        return chain().setMark('textStyle', { backgroundColor: null }).removeEmptyTextStyle().run()
      },
    }
  },
})

export { TextStyle }
