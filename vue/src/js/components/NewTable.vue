<template>
  <v-card color="#F8F8FF" :elevation=10 class="px-5 py-5">
    <v-card-title style="font-weight: bolder" class="d-flex align-center my-0 py-0 ga-2">
      {{ $t(tableTitle) }}
      <v-spacer></v-spacer>
      <v-text-field
      v-if="!hideSearchField"
      class="me-auto"
      variant ="outlined"
      append-inner-icon="mdi-magnify"
      :placeholder="searchText"
      hide-details
      density="compact"
      single-line
      style="max-width: 30%;"
      >

      </v-text-field>
      <slot name="headerBtn">
        <v-btn
        v-if="false"
      variant="outlined"
      rounded="lg"
      size="small"
      ripple="true"
      icon="mdi-filter-outline"
      width = "6%"

    ></v-btn>
      </slot>
    </v-card-title>

    <v-card-subtitle v-if=!!tableSubtitle >{{$t(tableSubtitle)}}</v-card-subtitle>


    <v-card-item>
      <v-divider v-if="!!tableSubtitle"></v-divider>
        <v-data-table-server
        :items="elements"
        :headers="headers"
        :items-length="elements.length"
        :item-title="titleKey"
        density="comfortable"
        >
        <template v-slot:headers v-if="hideHeaders">

        </template>

        <template v-slot:bottom>

        </template>

        <template
        v-for="(col, i) in formatterColumns"
        v-slot:[`item.${col.key}`]="{ item }"

        >
          <template v-if="col.formatter == 'edit' || col.formatter == 'activate'">
            <v-btn
              :key="i"
              class="pa-0 justify-start"
              variant="plain"
              :color="`primary darken-1`"
              @click="itemAction(item, ...col.formatter)"
              >
              <!-- {{ item[col.key].length > 40 ? item[col.key].substring(0,40) + '...' : item[col.key] }} -->
              {{ window.__shorten(item[col.key]) }}
            </v-btn>
          </template>
          <template v-else-if="col.formatter == 'switch'">
              <v-switch
                :key="i"
                :model-value="item[col.key]"
                color="success"
                :true-value="1"
                false-value="0"
                hide-details
                @update:modelValue="itemAction(item, 'switch', $event, col.key )"
                >
                <template v-slot:label></template>
              </v-switch>
          </template>
          <template v-else>
            <ue-recursive-stuff
              v-bind="handleFormatter(col.formatter, item[col.key])"
              :key="item[col.key]"
              />
          </template>
      </template>

      <template v-slot:header.actions="_obj">
        <v-menu
          :close-on-content-click="false"
          open-on-hover
          left
          >
          <template v-slot:activator="{ props }">
            <v-icon
              size="large"
              icon="mdi-cog-outline"
              v-bind="props"
              >
            </v-icon>
          </template>
        </v-menu>
      </template>
      <template v-slot:item.actions="{ item }">
        <!-- @click's editItem|deleteItem -->
        <!-- #actions -->
        <v-menu v-if="rowActionsType == 'dropdown' || isSmAndDown"
          :close-on-content-click="false"
          open-on-hover
          left
          offset-x
          >
          <template v-slot:activator="{ props }">
            <v-icon
              size="large"
              color="primary"
              icon="$list"
              v-bind="props"
              >
            </v-icon>
          </template>
          <v-list>
            <template v-for="(action, k) in rowActions" :key="k">
              <v-list-item
                v-if="itemHasAction(item, action)"
                @click="itemAction(item, action)"
                >
                  <v-icon small :color="action.color" left>
                    {{ action.icon ? action.icon : '$' + action.name }}
                  </v-icon>
                  {{ $t( action.label ??action.name ) }}
              </v-list-item>
            </template>
          </v-list>
        </v-menu>

        <div v-else>
          <template v-for="(action, k) in rowActions" :key="k">
            <v-tooltip
              v-if="itemHasAction(item, action)"
              :text="$t( action.label ?? action.name )"
              location="top"
              >
              <template v-slot:activator="{ props }">
                <v-icon
                  small
                  class="mr-2"
                  @click="itemAction(item, action)"
                  :color="action.color"
                  v-bind="props"
                  >
                  {{ action.icon ? action.icon : '$' + action.name }}
                </v-icon>
              </template>
            </v-tooltip>

          </template>
        </div>
      </template>



        </v-data-table-server>



    </v-card-item>
  </v-card>
</template>

<script>

// IMPORTS
import { makeFormatterProps } from '@/hooks/useFormatter'
import useTable, { makeTableProps } from '@/hooks/useTable'

import ActiveTableItem from '__components/labs/ActiveTableItem.vue'
const { ignoreFormatters } = makeFormatterProps()

export default{
  props:{
    ...makeTableProps(),
    ignoreFormatters
  },
  setup(props, context){
    return {
      ...useTable(props, context)
    }
  }
}

</script>


