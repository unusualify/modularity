<template>

  <v-card class="data-iterable-rich-row" variant="flat" elevation="0">
    <v-row>
      <v-col>
        <v-card-title>{{ item[iteratorOptions['headerKey']] }}</v-card-title>
      </v-col>
      <v-col cols="2">
        <v-row>
          <v-col class="action-area">
              <v-btn v-for="(action,i) in rowActions.filter(action => itemHasAction(item, action))" :ripple="true" density="compact" size="small"  :key="i" @click="itemAction(item,action)">
                <v-icon  small :icon="action.icon ?? `$${action.name}`"></v-icon>
            </v-btn>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-card-subtitle>
    <v-row>
      <v-col cols="3" class="first-column">
        <div v-for="(key,i) in iteratorOptions.firstColumn" class="d-flex">
          <p class="header">{{ headersWithKeys[key]['title'] }}</p>
          <p v-if="!(headersWithKeys[key].formatter.length)" class="value">{{ item[key] }}</p>
          <ue-recursive-stuff
          v-else
          v-bind="handleFormatter(headersWithKeys[key].formatter, item[key])"
          :key="key"
          />
        </div>
      </v-col>
      <v-col cols="3">

        <div v-for="(key,i) in iteratorOptions.secondColumn" class="d-flex">

          <p class="header">{{ headersWithKeys[key]?.['title'] ?? key }}</p>
          <p v-if="!headersWithKeys[key]?.formatter.length" class="value">{{ item[key] ?? '' }}</p>
          <ue-recursive-stuff
          v-else
          v-bind="handleFormatter(headersWithKeys[key].formatter, item[key])"
          :key="key"
          />
        </div>
      </v-col>
      <v-col cols="3" class="featured">
        <p v-if="!headersWithKeys[iteratorOptions.featured]?.formatter.length" class="featured">{{item[iteratorOptions.featured]}}</p>
        <ue-recursive-stuff
          v-else
          v-bind="handleFormatter(headersWithKeys[iteratorOptions.featured].formatter, item[iteratorOptions.featured])"
          :key="item[iteratorOptions.featured].key"
          />
      </v-col>
      <v-col cols="3" class="last-column">

        <ue-recursive-stuff
          v-if="headersWithKeys['published'].formatter.length"
          v-bind="handleFormatter(headersWithKeys[iteratorOptions.lastColumn].formatter, item[iteratorOptions.lastColumn])"
          :key="item[iteratorOptions.lastColumn].key"

          />
            <p v-else >{{item[iteratorOptions.lastColumn] }}</p>
            <v-btn variant="outlined" :ripple="true">Report</v-btn>

      </v-col>
    </v-row>
  </v-card-subtitle>


  </v-card>
  <v-divider/>
</template>

<script>

import { useTableIterator, makeTableIteratorProps, tableIterableEmits } from '@/hooks/table'
import { makeFormatterProps } from '@/hooks/useFormatter';

const { ignoreFormatters } = makeFormatterProps()

export default{
  emits: tableIterableEmits,
  props: {
    ...makeTableIteratorProps(),
    ignoreFormatters
  },
  setup(props, context){
    return {
      ...useTableIterator(props, context)
    }
  }
}

</script>
