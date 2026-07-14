<!-- EditorTableBubble.vue -->
<!-- NEW FILE -->
<!-- Tiptap BubbleMenu ile tablo hücrelerinde gösterilir -->
<!-- Gerekli extensions: Table, TableRow, TableCell, TableHeader -->

<template>
  <div class="ue-editor__table-bubble d-flex flex-wrap align-center ga-1 pa-1">

    <!-- Hücre işlemleri -->
    <v-tooltip location="top" text="Hücreleri birleştir">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text"
          icon="mdi-table-merge-cells" :disabled="!canMerge" @click="mergeCells" />
      </template>
    </v-tooltip>

    <v-tooltip location="top" text="Hücreyi böl">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text"
          icon="mdi-table-split-cell" :disabled="!canSplit" @click="splitCell" />
      </template>
    </v-tooltip>

    <v-divider vertical class="mx-1 align-self-stretch" thickness="1" />

    <!-- Satır işlemleri -->
    <v-tooltip location="top" text="Üstüne satır ekle">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text"
          icon="mdi-table-row-plus-before" @click="addRowBefore" />
      </template>
    </v-tooltip>

    <v-tooltip location="top" text="Altına satır ekle">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text"
          icon="mdi-table-row-plus-after" @click="addRowAfter" />
      </template>
    </v-tooltip>

    <v-tooltip location="top" text="Satırı sil">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text" color="error"
          icon="mdi-table-row-remove" @click="deleteRow" />
      </template>
    </v-tooltip>

    <v-divider vertical class="mx-1 align-self-stretch" thickness="1" />

    <!-- Sütun işlemleri -->
    <v-tooltip location="top" text="Soluna sütun ekle">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text"
          icon="mdi-table-column-plus-before" @click="addColBefore" />
      </template>
    </v-tooltip>

    <v-tooltip location="top" text="Sağına sütun ekle">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text"
          icon="mdi-table-column-plus-after" @click="addColAfter" />
      </template>
    </v-tooltip>

    <v-tooltip location="top" text="Sütunu sil">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text" color="error"
          icon="mdi-table-column-remove" @click="deleteColumn" />
      </template>
    </v-tooltip>

    <v-divider vertical class="mx-1 align-self-stretch" thickness="1" />

    <!-- Header toggle -->
    <v-tooltip location="top" text="Başlık hücresi">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text"
          :color="isHeaderCell ? 'primary' : undefined"
          icon="mdi-table-headers-eye" @click="toggleHeaderCell" />
      </template>
    </v-tooltip>

    <!-- Tabloyu sil -->
    <v-tooltip location="top" text="Tabloyu sil">
      <template #activator="{ props: p }">
        <v-btn v-bind="p" size="x-small" variant="text" color="error"
          icon="mdi-table-remove" @click="deleteTable" />
      </template>
    </v-tooltip>
  </div>
</template>

<script>
export default {
  name: 'EditorTableBubble',
  props: {
    editor: { type: Object, required: true },
  },
  computed: {
    canMerge () {
      return this.editor?.can().mergeCells() ?? false
    },
    canSplit () {
      return this.editor?.can().splitCell() ?? false
    },
    isHeaderCell () {
      return this.editor?.isActive('tableHeader') ?? false
    },
  },
  methods: {
    mergeCells    () { this.editor.chain().focus().mergeCells().run() },
    splitCell     () { this.editor.chain().focus().splitCell().run() },
    addRowBefore  () { this.editor.chain().focus().addRowBefore().run() },
    addRowAfter   () { this.editor.chain().focus().addRowAfter().run() },
    deleteRow     () { this.editor.chain().focus().deleteRow().run() },
    addColBefore  () { this.editor.chain().focus().addColumnBefore().run() },
    addColAfter   () { this.editor.chain().focus().addColumnAfter().run() },
    deleteColumn  () { this.editor.chain().focus().deleteColumn().run() },
    toggleHeaderCell () { this.editor.chain().focus().toggleHeaderCell().run() },
    deleteTable   () { this.editor.chain().focus().deleteTable().run() },
  },
}
</script>

<style lang="scss">
  .ue-editor__table-bubble {
    background: rgb(var(--v-theme-surface));
    border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  }
</style>
