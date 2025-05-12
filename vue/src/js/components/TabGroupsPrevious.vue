<template>
  <v-sheet class="fill-height">
    <div class="py-4">
      <ue-title :text="moduleTitle" :classes="[]" padding='a-0'></ue-title>
      <ue-title v-if="subtitle" :text="subtitle" :classes="[]" padding='a-0' weight='light' transform='none' type='subtitle-2'></ue-title>
    </div>

    <v-divider class=""></v-divider>
    <ue-tabs-previous :items="groupedItems" v-model="activeTab">
      <template v-for="(_, name) in $slots" v-slot:[name]="slotData"><slot :name="name" v-bind="slotData"></slot></template>
    </ue-tabs-previous>
  </v-sheet>
</template>
<script>
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
      }
    },
    setup (props, context) {

      return {
        ...useModule(props, context),
      }
    },
    data () {
      return {
        searchInput: this.search,
        activeTab: Object.keys(this.groupedItems ?? {})[0] ?? 0
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
      // search: {
      //   get () {
      //     __log('getter')
      //     return ''
      //   },
      //   set (val) {
      //     __log('setter', val)
      //     this.loadItems({replaceUrl: false, search: val})
      //     // store.commit(DATATABLE.UPDATE_DATATABLE_SEARCH, val)
      //     // store.dispatch(ACTIONS.GET_DATATABLE)
      //   }
      // },
    },
    watch: {

    },
    methods: {
      loadItems(options = {}){
        let self = this
        // state.loading = true
        options = {
          ...(this.searchModel !== '' ? {  search: this.searchModel } : {}),
          ...options
        }

        api.get(this.endpoints.index, options, function(response, _raw){
          const parameters = getParameters(_raw.request.responseURL)
          self.elements = response.resource.data
          replaceState(addParametersToUrl(getURLWithoutQuery(), parameters))
        })
      },
      enterSearch() {
        if(this.searchModel !== this.searchInput){
          this.searchModel = this.searchInput
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
