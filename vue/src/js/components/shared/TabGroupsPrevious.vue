<template>
  <v-sheet class="fill-height">

    <div class="py-4 d-flex justify-space-between flex-wrap ga-2">
      <div class="flex-1-0" style="max-width: 100%;">
        <ue-title :text="moduleTitle" :classes="[]" padding='a-0'></ue-title>
        <ue-title class="text-wrap" v-if="subtitle" :text="subtitle" :classes="[]" padding='a-0' weight='regular' transform='none' type='subtitle-2'></ue-title>
      </div>
      <div v-if="!noSearch" class="flex-1-1">
        <v-text-field
          v-model="searchModel"
          label="Search"
          variant="outlined"
          density="compact"
          color="primary"
          hide-details
          style="min-width: 300px;"
          @keydown.enter="enterSearch"
          >
          <template v-slot:append-inner>
            <v-icon v-if="searchable" icon="mdi-magnify" variant="text" @click="enterSearch"></v-icon>
          </template>
        </v-text-field>
      </div>
    </div>

    <v-divider></v-divider>
    <div class="text-center pa-8" v-if="loading">
      <v-progress-circular
        :size="50"
        color="primary"
        indeterminate
      ></v-progress-circular>
    </div>
    <ue-tabs-previous v-else :items="groupedItems" v-model="activeTab">
      <template v-for="(_, name) in $slots" v-slot:[name]="slotData"><slot :name="name" v-bind="slotData"></slot></template>
    </ue-tabs-previous>
  </v-sheet>
</template>
<script>
  import { get } from 'lodash-es'
  import {
    makeModuleProps,
    useModule,
  } from '@/hooks'

  import api from '@/store/api/datatable'

  import { replaceState, getURLWithoutQuery, getParameters, addParametersToUrl} from '@/utils/pushState.js'

  export default {
    name: 'ue-tab-groups',
    props: {
      ...makeModuleProps(),
      subtitle: {
        type: String,
      },
      groupKey: {
        type: String,
        required: true
      },
      noAllGroup: {
        type: Boolean,
        default: false
      },
      items: {
        type: Array,
        default: []
      },
      groupedTestItems: {
        type: Object,
        default: () => {
          return {
            'Hepsi': [
              {id: 1, name: 'Deneme', description: 'Description 1'},
              {id: 2, name: 'Deneme 2', description: 'Description 2'},
              {id: 3, name: 'Yayın 3', description: 'Description 3'},
              {id: 4, name: 'Yayın 4', description: 'Description 4'},
            ],
            'Deneme': [
              {id: 2, name: 'Deneme 2', description: 'Description 2'},
            ],
            'Yayın': [
              {id: 3, name: 'Yayın 3', description: 'Description 3'},
              {id: 4, name: 'Yayın 4', description: 'Description 4'},
            ]
          }
        }
      },
      search: {
        type: String,
        default: ''
      },
      noSearch: {
        type: Boolean,
        default: false
      },
      queryParameters: {
        type: Object,
        default: () => {
          return {}
        }
      },
      responseResourceKey: {
        type: String,
        default: 'resource.data'
      }
    },
    setup (props, context) {

      return {
        ...useModule(props, context),
      }
    },
    data () {
      return {
        loading: false,
        searchInput: this.search,
        activeTab: Object.keys(this.groupedItems ?? {})[0] ?? 0,
        elements: []
      }
    },
    computed: {
      groupedItems() {
        let allKey = this.$t('All')
        let grouped = {}

        if(!this.noAllGroup)
          grouped[allKey] = []

        return this.elements.reduce((acc, item, key) => {
          if(!this.noAllGroup)
            acc[allKey].push(item)

          const groupKeys = __data_get(item, this.groupKey)

          if(Array.isArray(groupKeys)){
            for(const groupKey of groupKeys){
              acc[groupKey] ??= []
              acc[groupKey].push(item)
            }
          }else if(__isString(groupKeys)){
            acc[groupKeys] ??= []
            acc[groupKeys].push(item)
          }

          return acc
        }, grouped)
      },
      searchable(){
        return this.searchModel !== this.searchInput
      },
    },
    watch: {

    },
    methods: {
      loadItems(options = {}){
        let self = this
        this.loading = true
        options = {
          ...(this.searchModel !== '' ? {  search: this.searchModel } : {}),
          ...options,
          ...this.queryParameters
        }

        this.searchInput = this.searchModel

        api.get(this.endpoints.index, options, function(response, _raw){
          const parameters = getParameters(_raw.request.responseURL)
          self.elements = get(response, self.responseResourceKey)

          self.activeTab = Object.keys(self.groupedItems ?? {})?.[0] ?? 0
          self.loading = false
          // replaceState(addParametersToUrl(getURLWithoutQuery(), parameters))
        }, function(error){
          self.loading = false
        })
      },

      enterSearch() {
        if(this.searchModel !== this.searchInput){
          this.loadItems({replaceUrl: false})
        }
      }
    },
    created(){
      this.searchModel = this.searchInput
      this.loadItems({replaceUrl: false})
    }
  }
</script>

<style lang="sass">

</style>
