<script setup>
import { ref, watch } from 'vue';
import { marked } from 'marked';

const props = defineProps({
  markdown: {
      type: String,
      required: true
  }
});

const html = ref('');
const headings = ref([]);

/**
 * Creates a slug-generating function that ensures unique slugs by appending a
 * number for duplicates, similar to marked's Slugger.
 */
const createSlugger = () => {
  const seen = new Map();
  return (value) => {
    let slug = value
        .toLowerCase()
        .trim()
        // remove punctuation
        .replace(/[\u2000-\u206F\u2E00-\u2E7F\\'!"#$%&()*+,./:;<=>?@[\]^`{|}~]/g, '')
        .replace(/\s/g, '-');

    if (seen.has(slug)) {
        const count = seen.get(slug) + 1;
        seen.set(slug, count);
        slug = `${slug}-${count}`;
    } else {
        seen.set(slug, 1);
    }
    return slug;
  };
};

watch(() => props.markdown, (newValue) => {
  if (!newValue) {
      html.value = '';
      headings.value = [];
      return;
  }

  const renderer = new marked.Renderer();
  const slugger = createSlugger();
  const toc = []; // Use local array first

  // Override the heading renderer to add IDs and populate the table of contents
  renderer.heading = (headingObj) => {
    const level = headingObj.depth;
    const text = headingObj.text;
    const id = slugger(text);
    if (level <= 3) { // Include h1, h2, and h3 in the TOC
      toc.push({
          level,
          id,
          text
      });
    }
    return `<h${level} id="${id}">${text}</h${level}>`;
  };

  html.value = marked(newValue, { renderer });
  headings.value = toc; // Set headings after processing

}, { immediate: true });
</script>

<template>
  <v-container fluid>
    <v-row>
      <v-col :md="headings.length ? 9 : 12" cols="12" class="pr-4">
        <div v-html="html" class="markdown-body"></div>
      </v-col>
      <v-col v-if="headings.length" md="3" class="d-none d-md-block">
        <v-card class="sticky-top" flat>
          <v-card-title class="text-subtitle-1 py-2 px-3 font-weight-medium">On this page</v-card-title>
          <v-list density="compact" nav>
            <v-list-item v-for="heading in headings"
              :class="[
                'text-body-2 py-1',
                `heading-level-${heading.level}`
              ]"
              :key="heading.id"
              :href="`#${heading.id}`"
              :title="heading.text"
              :style="{ 'padding-left': (heading.level - 1) * 16 + 'px' }"
              link
            ></v-list-item>
          </v-list>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.markdown-body {
    font-size: 16px;
    line-height: 1.6;
    color: #24292f;
    padding: 24px 32px;
}

.markdown-body :deep(h1) {
    font-size: 2rem;
    font-weight: 600;
    margin: 24px 0 16px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #d1d9e0;
    scroll-margin-top: 80px;
}

.markdown-body :deep(h2) {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 24px 0 16px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #d1d9e0;
    scroll-margin-top: 80px;
}

.markdown-body :deep(h3) {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 20px 0 12px 0;
    scroll-margin-top: 80px;
}

.markdown-body :deep(h4),
.markdown-body :deep(h5),
.markdown-body :deep(h6) {
    font-size: 1rem;
    font-weight: 600;
    margin: 16px 0 8px 0;
    scroll-margin-top: 80px;
}

.markdown-body :deep(p) {
    margin: 0 0 16px 0;
}

.markdown-body :deep(ul),
.markdown-body :deep(ol) {
    margin: 0 0 16px 0;
    padding-left: 32px;
}

.markdown-body :deep(li) {
    margin: 4px 0;
}

.markdown-body :deep(code) {
    background-color: rgba(175, 184, 193, 0.2);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 85%;
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
}

.markdown-body :deep(pre) {
    background-color: #f6f8fa;
    padding: 16px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 16px 0;
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-size: 85%;
    line-height: 1.45;
}

.markdown-body :deep(pre code) {
    background-color: transparent;
    padding: 0;
    border-radius: 0;
    font-size: inherit;
}

.markdown-body :deep(blockquote) {
    padding: 0 16px;
    margin: 16px 0;
    color: #656d76;
    border-left: 4px solid #d1d9e0;
}

.markdown-body :deep(strong) {
    font-weight: 600;
}

/* Navigation styling */
.sticky-top {
    position: sticky;
    top: 80px;
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

.heading-level-1 {
    padding-left: 12px !important;
    font-weight: 600;
}

.heading-level-2 {
    padding-left: 20px !important;
    font-weight: 500;
}

.heading-level-3 {
    padding-left: 28px !important;
    font-weight: 400;
}

/* Override Vuetify's list item padding */
:deep(.v-list-item) {
    min-height: 32px !important;
    padding-top: 4px !important;
    padding-bottom: 4px !important;
}

:deep(.v-list-item__content) {
    padding: 0 !important;
}
</style>
