<script setup>
  import { computed, defineComponent, getCurrentInstance, h, resolveDynamicComponent } from 'vue'

  const props = defineProps({
    subject: {
      type: String,
      default: ''
    }
  })

  const instance = getCurrentInstance()

  const rootBindAttributes = () => {
    return instance?.proxy?.$bindAttributes?.() ?? {}
  }

  const isVueComponentTag = (tagName) => {
    const name = String(tagName || '').toLowerCase()

    return name.startsWith('v-') || name.startsWith('ue-')
  }

  const parseElement = (el) => {
    if (!el || el.nodeType !== 1) {
      return null
    }

    const name = el.tagName.toLowerCase()
    const elementProps = {}

    for (let i = 0; i < el.attributes.length; i++) {
      const attr = el.attributes[i]
      elementProps[attr.name] = attr.value
    }

    const children = []
    let textContent = ''

    for (const node of Array.from(el.childNodes)) {
      if (node.nodeType === 1 && isVueComponentTag(node.tagName)) {
        const parsed = parseElement(node)
        if (parsed) {
          children.push(parsed)
        }
        continue
      }

      if (node.nodeType === 3) {
        textContent += node.textContent || ''
      }
    }

    return {
      name,
      props: elementProps,
      content: textContent.trim(),
      children,
    }
  }

  const parseComponentString = (str) => {
    const parser = new DOMParser()
    const doc = parser.parseFromString(str, 'text/html')

    return parseElement(doc.body.firstElementChild)
  }

  const isVueComponent = computed(() => {
    const subject = props.subject?.trim?.() ?? ''

    return (subject.startsWith('<v-') || subject.startsWith('<ue-')) && subject.endsWith('>')
  })

  const parsedComponent = computed(() => {
    return isVueComponent.value ? parseComponentString(props.subject) : null
  })

  const normalizeElementProps = (elementProps) => {
    const normalized = {}

    for (const [key, value] of Object.entries(elementProps)) {
      normalized[key] = value === '' ? true : value
    }

    return normalized
  }

  const renderParsedNode = (node, extraProps = {}) => {
    if (!node) {
      return null
    }

    const mergedProps = normalizeElementProps({ ...node.props, ...extraProps })
    const hasElementChildren = Boolean(node.children?.length)

    if (node.name === 'v-tooltip' && hasElementChildren) {
      return h(
        resolveDynamicComponent('v-tooltip'),
        mergedProps,
        {
          activator: ({ props: tipProps }) => {
            const activatorProps = normalizeElementProps(tipProps)

            if (node.children.length === 1) {
              return renderParsedNode(node.children[0], activatorProps)
            }

            return node.children.map((child) => renderParsedNode(child))
          },
        },
      )
    }

    const childVNodes = []

    if (hasElementChildren) {
      for (const child of node.children) {
        childVNodes.push(renderParsedNode(child))
      }
    } else if (node.content) {
      childVNodes.push(node.content)
    }

    return h(
      resolveDynamicComponent(node.name),
      mergedProps,
      childVNodes.length ? () => childVNodes : undefined,
    )
  }

  const NestedDynamicTree = defineComponent({
    name: 'NestedDynamicTree',
    props: {
      node: {
        type: Object,
        required: true,
      },
      extra: {
        type: Object,
        default: () => ({}),
      },
    },
    setup(treeProps) {
      return () => renderParsedNode(treeProps.node, treeProps.extra)
    },
  })
</script>

<template>
  <NestedDynamicTree
    v-if="subject && isVueComponent && parsedComponent"
    :node="parsedComponent"
    :extra="rootBindAttributes()"
  />
  <template v-else-if="subject" v-html="subject"></template>
</template>
