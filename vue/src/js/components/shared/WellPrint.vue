<template>
  <div class="ue-well-print d-flex justify-start" :class="attrs.class">
    <span v-if="text || attrs.text" v-html="formattedText" />
    <span v-else-if="processedContent" v-html="processedContent" />
    <span v-else ref="slotContainerRef" style="display: none;"><slot /></span>
  </div>
</template>

<script setup>
import { computed, useAttrs, ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
  text: {
    type: String,
    default: ''
  },
  /**
   * The full text of the content.
   * This is used to extract full URLs from the content.
   * This is important for truncated text where URLs might be cut off.
   */
  fullText: {
    type: String,
    default: null
  },
  /**
   * Whether to linkify the text.
   * If true, the text will be linkified.
   * If false, the text will not be linkified.
   */
  noLinkify: {
    type: Boolean,
    default: false,
  }
})

const attrs = useAttrs()
const slotContainerRef = ref(null)
const processedContent = ref('')

const URL_REGEX = /(https?:\/\/[^\s<>"{}|\\^`\[\]]+|www\.[^\s<>"{}|\\^`\[\]]+|[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.([a-zA-Z]{2,})(\/[^\s<>"{}|\\^`\[\]]*)?)/gi

const escapeHtml = (str) => str.replace(/"/g, '&quot;')
const normalizeUrl = (url) => url.match(/^https?:\/\//i) ? url : 'https://' + url

const formatTextOnly = (text) => text ? text.replace(/\n/g, '<br>') : ''

const extractFullUrls = (text) => {
  if (!text) return []
  const urls = []
  let match
  URL_REGEX.lastIndex = 0
  while ((match = URL_REGEX.exec(text)) !== null) {
    urls.push(normalizeUrl(match[0]))
  }
  return urls
}

const linkifyAndFormat = (text, fullTextForUrls = null) => {
  if (!text) return ''

  const fullUrls = (fullTextForUrls && fullTextForUrls !== text) ? extractFullUrls(fullTextForUrls) : null
  let formattedText = text.replace(URL_REGEX, (match) => {
    const url = match.trim()
    const href = normalizeUrl(url)
    return `<a href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer" class="ue-well-print__link">${escapeHtml(url)}</a>`
  })

  if (fullUrls?.length) {
    let urlIndex = 0
    formattedText = formattedText.replace(/href="[^"]*"/g, () => {
      if (urlIndex < fullUrls.length) {
        return `href="${escapeHtml(fullUrls[urlIndex++])}"`
      }
    })
  }

  return formattedText.replace(/\n/g, '<br>')
}

const getTextContent = () => props.text || attrs.text || ''
const getFullText = () => props.fullText || attrs.fullText || null
const shouldLinkify = () => !props.noLinkify && !attrs.noLinkify

const formatContent = (textContent, fullText = null) => {
  return shouldLinkify() ? linkifyAndFormat(textContent, fullText) : formatTextOnly(textContent)
}

const formattedText = computed(() => {
  const textContent = getTextContent()
  return textContent ? formatContent(textContent, getFullText()) : ''
})

const processSlotContent = async () => {
  if (props.text || attrs.text) {
    processedContent.value = ''
    return
  }

  await nextTick()
  if (!slotContainerRef.value) return

  const textContent = (slotContainerRef.value.textContent || slotContainerRef.value.innerText || '').trim()
  if (textContent && !processedContent.value) {
    processedContent.value = formatContent(textContent, getFullText())
  }
}

onMounted(processSlotContent)

watch(() => [props.text, attrs.text, props.fullText, props.noLinkify], () => {
  processedContent.value = ''
  if (!props.text && !attrs.text) processSlotContent()
})
</script>

<style lang="sass" scoped>
.ue-well-print
  white-space: pre-line

  :deep(.ue-well-print__link)
    color: rgb(var(--v-theme-primary)) !important
    text-decoration: underline
    cursor: pointer
    word-break: break-all

    &:hover
      color: rgb(var(--v-theme-primary-darken-1)) !important

    &:visited
      color: rgb(var(--v-theme-primary-darken-2)) !important
</style>
