<template>
  <component
    v-if="isVueComponent"
    :is="parsedComponent.name"
    v-bind="parsedComponent.props"
  >
    {{ parsedComponent.content }}
  </component>
  <template v-else>{{ subject }}</template>
</template>

<script>
export default {
  name: 'ue-dynamic-component-renderer',
  props: {
    subject: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      parsedComponent: null,
      isVueComponent: false
    }
  },
  mounted() {
    this.parseSubject()
  },
  methods: {
    parseSubject() {
      if ( (this.subject.startsWith('<v-') || this.subject.startsWith('<ue-') ) && this.subject.endsWith('>')) {
        this.isVueComponent = true
        this.parsedComponent = this.parseComponentString(this.subject)
      } else {
        this.isVueComponent = false
      }
    },
    parseComponentString(str) {
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

      props = { ...props, ...this.$bindAttributes() }

      return { name, props, content }
    }
  },
  watch: {
    subject() {
      this.parseSubject()
    }
  }
}
</script>
