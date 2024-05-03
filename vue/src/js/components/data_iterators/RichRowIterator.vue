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
    <v-card-subtitle >
    <v-row>
      <v-col cols="3">
        <div v-for="(key,i) in iteratorOptions.firstColumn" class="d-flex">
          <p class="header">{{ headers[key]['title'] }}</p>
          <p class="value">{{ item[key] }}</p>
        </div>
      </v-col>
      <v-col cols="3">
        <div v-for="(header,i) in headers" class="d-flex">
          <p class="header">{{ header['title'] }}</p>
          <p class="value">{{ item[header['key']] }}</p>
        </div>
      </v-col>
      <v-col cols="3" class="featured">
        <p class="featured">{{item[iteratorOptions.featured]}}</p>
      </v-col>
      <v-col cols="3" class="last-column">
        <v-row>
          <v-col cols="5" style="align-content: center;">
            <p>{{item['published'] ? 'Published' : 'Not Published'}}</p>
          </v-col>
          <v-col cols="7">
            <v-btn style="width: 100%" variant="outlined" :ripple="true">Report</v-btn>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-card-subtitle>


  </v-card>
  <v-divider/>
</template>

<script>

import useIterator, { makeIteratorProps, iterableEmits } from '@/hooks/useIterator'

export default{
  emits: iterableEmits,
  props: {
    ...makeIteratorProps()
  },
  setup(props, context){
    return {
      ...useIterator(props, context)
    }
  }
}

</script>
