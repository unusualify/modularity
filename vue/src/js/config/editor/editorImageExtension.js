import Image from '@tiptap/extension-image'
import { mergeAttributes } from '@tiptap/core'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import EditorImageNode from '__components/editor/EditorImageNode.vue'

export function normalizeWidth (width) {
  if (!width) {
    return null
  }

  const value = String(width).trim()

  if (value.endsWith('%') || value.endsWith('px')) {
    return value
  }

  const numeric = Number(value)

  if (!Number.isNaN(numeric)) {
    return `${numeric}%`
  }

  return value
}

export function buildImageStyle ({ width, align, float, style, includeFloat = true }) {
  const styles = []

  if (style) {
    styles.push(style)
  }

  const normalizedWidth = normalizeWidth(width)

  if (normalizedWidth) {
    styles.push(`width: ${normalizedWidth}`)
  }

  if (includeFloat && float === 'left') {
    styles.push('float: left', 'margin-right: 1rem', 'margin-bottom: 0.5rem')
  } else if (includeFloat && float === 'right') {
    styles.push('float: right', 'margin-left: 1rem', 'margin-bottom: 0.5rem')
  } else if (align === 'left') {
    styles.push('display: block', 'margin-right: auto', 'margin-left: 0')
  } else if (align === 'right') {
    styles.push('display: block', 'margin-left: auto', 'margin-right: 0')
  } else {
    styles.push('display: block', 'margin-left: auto', 'margin-right: auto')
  }

  styles.push('max-width: 100%', 'height: auto')

  return styles.join('; ')
}

export function buildFloatedFigureStyle ({ width, float }) {
  const styles = []
  const normalizedWidth = normalizeWidth(width)

  if (normalizedWidth) {
    styles.push(`width: ${normalizedWidth}`)
  }

  if (float === 'left') {
    styles.push('float: left', 'margin-right: 1rem', 'margin-bottom: 0.5rem')
  } else if (float === 'right') {
    styles.push('float: right', 'margin-left: 1rem', 'margin-bottom: 0.5rem')
  }

  styles.push('max-width: 100%')

  return styles.join('; ')
}

export function buildFloatedImageStyle () {
  return 'width: 100%; max-width: 100%; height: auto; display: block;'
}

function getImageLayoutStyleSource (element) {
  if (!(element instanceof HTMLImageElement)) {
    return ''
  }

  const figure = element.closest('figure')

  if (figure?.getAttribute('style')) {
    return figure.getAttribute('style')
  }

  return element.getAttribute('style') ?? ''
}

function parseWidthFromStyle (style) {
  if (!style) {
    return null
  }

  const match = style.match(/width:\s*([^;]+)/i)

  return match?.[1]?.trim() ?? null
}

function parseAlignFromStyle (style) {
  if (style?.includes('float: left')) {
    return { align: 'left', float: 'left' }
  }

  if (style?.includes('float: right')) {
    return { align: 'right', float: 'right' }
  }

  if (style?.includes('margin-left: auto') && style?.includes('margin-right: 0')) {
    return { align: 'right', float: 'none' }
  }

  if (style?.includes('margin-right: auto') && style?.includes('margin-left: 0')) {
    return { align: 'left', float: 'none' }
  }

  return { align: 'center', float: 'none' }
}

export const IMAGE_WIDTH_PRESETS = [25, 50, 75, 100]

export const EditorImage = Image.extend({
  name: 'image',

  addOptions () {
    return {
      ...this.parent?.(),
      inline: false,
      allowBase64: true,
    }
  },

  addAttributes () {
    return {
      ...this.parent?.(),
      alt: {
        default: null,
        parseHTML: (element) => element.getAttribute('alt'),
        renderHTML: (attributes) => {
          if (!attributes.alt) {
            return {}
          }

          return { alt: attributes.alt }
        },
      },
      title: {
        default: null,
        parseHTML: (element) => element.getAttribute('title'),
        renderHTML: (attributes) => {
          if (!attributes.title) {
            return {}
          }

          return { title: attributes.title }
        },
      },
      width: {
        default: '100%',
        parseHTML: (element) => parseWidthFromStyle(getImageLayoutStyleSource(element)) ?? '100%',
        renderHTML: () => ({}),
      },
      align: {
        default: 'center',
        parseHTML: (element) => parseAlignFromStyle(getImageLayoutStyleSource(element)).align,
        renderHTML: () => ({}),
      },
      float: {
        default: 'none',
        parseHTML: (element) => parseAlignFromStyle(getImageLayoutStyleSource(element)).float,
        renderHTML: () => ({}),
      },
      href: {
        default: null,
        parseHTML: (element) => element.parentElement?.tagName === 'A'
          ? element.parentElement.getAttribute('href')
          : null,
        renderHTML: () => ({}),
      },
    }
  },

  renderHTML ({ node, HTMLAttributes }) {
    const {
      align = 'center',
      float = 'none',
      width = '100%',
      href = null,
    } = node.attrs

    const {
      align: _align,
      float: _float,
      width: _width,
      height: _height,
      href: _href,
      style: inheritedStyle,
      ...rest
    } = HTMLAttributes

    const imageAttributes = mergeAttributes(this.options.HTMLAttributes, rest, {
      style: float === 'none'
        ? buildImageStyle({
          width,
          align,
          float,
          style: inheritedStyle,
        })
        : buildFloatedImageStyle(),
    })

    if (float !== 'none') {
      const figureAttributes = {
        class: 'ue-editor-figure',
        style: buildFloatedFigureStyle({ width, float }),
      }

      if (href) {
        return [
          'figure',
          figureAttributes,
          [
            'a',
            {
              href,
              target: '_blank',
              rel: 'noopener noreferrer',
            },
            ['img', imageAttributes],
          ],
        ]
      }

      return ['figure', figureAttributes, ['img', imageAttributes]]
    }

    if (href) {
      return [
        'a',
        {
          href,
          target: '_blank',
          rel: 'noopener noreferrer',
        },
        ['img', imageAttributes],
      ]
    }

    return ['img', imageAttributes]
  },

  parseHTML () {
    const parseImageElement = (element) => {
      if (!(element instanceof HTMLImageElement)) {
        return false
      }

      const layoutStyle = getImageLayoutStyleSource(element)
      const parsed = parseAlignFromStyle(layoutStyle)

      return {
        src: element.getAttribute('src'),
        alt: element.getAttribute('alt'),
        title: element.getAttribute('title'),
        width: parseWidthFromStyle(layoutStyle) ?? '100%',
        align: parsed.align,
        float: parsed.float,
        href: element.parentElement?.tagName === 'A'
          ? element.parentElement.getAttribute('href')
          : null,
      }
    }

    return [
      {
        tag: 'figure img[src]',
        getAttrs: (element) => parseImageElement(element),
      },
      {
        tag: 'figure a[href] > img[src]',
        getAttrs: (element) => parseImageElement(element),
      },
      {
        tag: 'a[href] > img[src]',
        getAttrs: (element) => parseImageElement(element),
      },
      {
        tag: 'img[src]',
        getAttrs: (element) => parseImageElement(element),
      },
    ]
  },

  addNodeView () {
    return VueNodeViewRenderer(EditorImageNode)
  },

  addCommands () {
    return {
      ...this.parent?.(),
      setImageAttributes: (attributes) => ({ commands }) => {
        return commands.updateAttributes(this.name, attributes)
      },
      setImageWidth: (width) => ({ commands }) => {
        return commands.updateAttributes(this.name, { width: normalizeWidth(width) })
      },
      setImageAlign: ({ align = 'center', float = 'none' }) => ({ commands }) => {
        return commands.updateAttributes(this.name, { align, float })
      },
      setImageAlt: (alt) => ({ commands }) => {
        return commands.updateAttributes(this.name, { alt })
      },
      setImageLink: (href) => ({ commands }) => {
        return commands.updateAttributes(this.name, { href: href || null })
      },
    }
  },
})
