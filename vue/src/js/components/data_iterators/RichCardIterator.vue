<template>
    <v-sheet border>
      <v-img
        :gradient="`to bottom, rgba(255, 255, 255, 0), rgba(255, 255, 255, .1), rgba(0, 0, 0, .15)`"
        :src="headersWithKeys[iteratorOptions.imgSrc] ?? 'https://ih1.redbubble.net/image.4905811447.8675/flat,750x,075,f-pad,750x1000,f8f8f8.jpg'"
        height="150"
      >
      </v-img>

      <v-list-item
        :title="item.name"
        density="comfortable"
        lines="one"
      >
        <template v-slot:title>
          <strong class="text-h6">
            {{ item.name }}
          </strong>
        </template>
      </v-list-item>

      <v-table class="data-iterator-table" density="compact">
        <tbody>
          <tr
          v-for="(header, i) in headersWithKeys"
          >
            <th>{{ header.title }}</th>
            <td v-if="header.key !== 'actions' && !header.formatter?.length">{{ item[header.key] }}</td>
            <td v-else-if="header.key !== 'actions' && header.formatter?.length">
              <!-- {{ handleFormatter(header.formatter, item[header.key]) }} -->

              <ue-recursive-stuff
                v-bind="handleFormatter(header.formatter, item[header.key])"
                :key="header.key"
              />
            </td>
            <td v-else-if="header.key ==='actions' && !header.formatter.length">
              <div class="d-flex justify-end" >
                <v-icon v-for="(action,i) in rowActions.filter(action => itemHasAction(item, action))" :ripple="true" density="compact" size="medium"  :key="i" @click="itemAction(item,action)"  small :icon="action.icon ?? `$${action.name}`"></v-icon>
              </div>
            </td>
          </tr>
        </tbody>
      </v-table>
  </v-sheet>
</template>

<script>
import useIterator, {makeIteratorProps, iterableEmits} from '@/hooks/useIterator';
import { makeFormatterProps } from '@/hooks/useFormatter';

const { ignoreFormatters } = makeFormatterProps()

export default{
  emits: iterableEmits,
  props:{
    ...makeIteratorProps(),
    ignoreFormatters
  },
  setup(props, context){
    return {
      ...useIterator(props, context)
    }
  }

}
</script>
