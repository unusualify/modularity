<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    subject: {
      type: String,
      default: ''
    }
  })

  const parseComponentString = (str) => {
    const parser = new DOMParser()
    const doc = parser.parseFromString(str, 'text/html')
    const el = doc.body.firstChild

    const name = el.tagName.toLowerCase()
    let props = {}
    const content = el.textContent

    for (let i = 0; i < el.attributes.length; i++) {
      const attr = el.attributes[i]
      props[attr.name] = attr.value
    }

    props = { ...props }

    return { name, props, content }
  }

  const isVueComponent = computed(() => {
    return (props.subject.startsWith('<v-') || props.subject.startsWith('<ue-') ) && props.subject.endsWith('>')
  })

  const parsedComponent = computed(() => {
    return isVueComponent.value ? parseComponentString(props.subject) : null
  })
</script>

<template>
  <component
    v-if="subject && isVueComponent"
    :is="parsedComponent.name"
    v-bind="{...parsedComponent.props, ...$bindAttributes()}"
  >
    {{ parsedComponent.content }}
  </component>
  <template v-else-if="subject" v-html="subject"></template>
</template>

