<template>
  <v-card @click="toggleExpand" color="#EBEFF280" variant="flat" rounded="l" class="mr-0 pr-0">
    <v-row class="d-flex align-center  pr-0 mr-0 ga-0">
      <v-col cols="11">

        <v-card-item v-if="!(headersWithKeys[iteratorOptions.headerKey].formatter.length)" class="value">{{ item[iteratorOptions.headerKey] }}</v-card-item>
        <ue-recursive-stuff
          v-else
          v-bind="handleFormatter(headersWithKeys[iteratorOptions.headerKey].formatter, item[iteratorOptions.headerKey])"
          :key="key"
          />
      </v-col>
      <v-spacer></v-spacer>
        <v-icon icon="mdi-menu-down" class="pr-3"></v-icon>

    </v-row>

    <v-expand-transition :key="item.id">
      <v-card v-if="isExpanded">
        <v-card-item v-if="!(headersWithKeys[iteratorOptions.expandedKey].formatter.length)" class="value">{{ item[iteratorOptions.expandedKey] }}</v-card-item>
        <ue-recursive-stuff
          v-else
          v-bind="handleFormatter(headersWithKeys[iteratorOptions.expandedKey].formatter, item[iteratorOptions.expandedKey])"
          :key="key"
          />
      </v-card>
    </v-expand-transition>

  </v-card>
</template>


<script>

import useIterator, { makeIteratorProps, iterableEmits } from '@/hooks/useIterator'
import { makeFormatterProps } from '@/hooks/useFormatter';

const { ignoreFormatters } = makeFormatterProps()

 export default{

  props:{
    ...makeIteratorProps(),
    ignoreFormatters
  },
  setup(props, context){
    return {
      ...useIterator(props, context)
    }
  },
  data(){
    return {
      isExpanded: false
    }
  },
  methods:{
    toggleExpand(){
      this.isExpanded = !this.isExpanded
    }
  }
 }
</script>
