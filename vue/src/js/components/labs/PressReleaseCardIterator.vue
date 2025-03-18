<template>
  <ue-title type="caption" :text="item.id" weight="regular" color="grey-lighten-1" padding="x-2" margin="t-2"/>
  <ue-title :text="item.content.headline || 'PR HEADLINE ...'" padding="x-2"/>
  <ue-configurable-card
    :title="item.name"
    style="background-color: transparent;"
    elevation="0"
    hide-separator
    align-center-columns
    :items="[
      Object.entries(item.press_release_packages).reduce((acc, [key, value]) => {
        acc[value.name] = value.packageLanguages.map(v => v.name).join(', ')
        return acc;
      }, {}),
      {
        'Content': item.content.file,
        'Media': item.content.press_release_images.map(v => v.image).join(', '),
        'Date': item.content.date,
      },
      [
        item._price ?? 'N/A'
      ],
      item._status ?? 'Draft'
    ]"
    :actions="[
      {
        name: 'edit',
        icon: 'mdi-pencil',
        color: 'primary'
      }
    ]"
  >
    <template
      #[`segment.1`]="segmentScope"
      >
      <ue-property-list :data="segmentScope.data" no-padding class="ml-n2"></ue-property-list>
    </template>
    <template
      #[`segment.actions`]="segmentScope"
      >
      <slot name="actions">

      </slot>
    </template>
  </ue-configurable-card>
</template>

<script>
import { useTableIterator, makeTableIteratorProps, tableIterableEmits } from '@/hooks/table'
import { makeFormatterProps } from '@/hooks/useFormatter';

  const { ignoreFormatters } = makeFormatterProps()

  export default{
  emits: tableIterableEmits,
  props:{
    ...makeTableIteratorProps(),
    ignoreFormatters
  },
  setup(props, context){
    return {
      ...useTableIterator(props, context)
    }
  },
  created(){
    // console.log('ConfigurableCardIterator created', this.item, this.headers, this.iteratorOptions, this.rowActions)
  }

  }
</script>
