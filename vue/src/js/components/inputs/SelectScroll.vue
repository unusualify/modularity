<template>
  <component
    :is="componentType"
    v-bind="$bindAttributes()"

    v-model="input"
    :class="['v-input-select-scroll']"
    :items="elements"
    :label="label"
    @update:search="searched"
    :no-filter="noFilter"
    @input.native="getItemsFromApi"
    :multiple="multiple"
    :return-object="$attrs.returnObject ?? returnObject ?? false"
    :item-value="itemValue"
    :item-title="itemTitle"

    :loading="loading"
    :readonly="$attrs.readonly || readonly || loading"
  >
    <template v-slot:append-item>
      <div v-if="lastPage > 0 && lastPage > page" v-intersect="endIntersect" />
    </template>
  </component>
</template>

<script>
import { useInput, makeInputProps, makeInputEmits } from '@/hooks'
import { getParameters, getURLWithoutQuery, getOrigin, getPath } from '@/utils/pushState'

export default {
  name: 'v-input-select-scroll',
  emits: [...makeInputEmits],

  props: {
    ...makeInputProps(),
    componentType: {
      type: String,
      default: 'v-autocomplete'
    },
    itemValue: {
      type: String,
      default: 'id'
    },
    itemTitle: {
      type: String,
      default: 'name'
    },
    itemsPerPage: {
      type: Number,
      default: 20
    },
    endpoint: {
      type: String,
    },
    multiple: {
      type: Boolean,
      default: false
    },
    items: {
      type: Array,
      default: () => []
    }
  },
  setup (props, context) {
    const inputHook = useInput(props, context)
    return {
      ...inputHook
    }
  },
  data () {
    return {
      loading: false,
      page: 1,
      limit: 100,
      lastPage: -1,
      selectedVendorId: null,
      elements: [],
      loading: true,
      search: '',
      noFilter: this.componentType == 'v-autocomplete' ? true : null
    }
  },
  methods: {
    endIntersect(entries, observer, isIntersecting) {
      if (isIntersecting) {
        this.getItemsFromApi()
      }
    },
    getItemsFromApi (event) {

      if( !(this.page > this.lastPage) || this.lastPage < 0){
        this.loading = true;
        return new Promise(() => {
          this.$axios.get(this.fullUrl)
            .then(response => {
              this.loading = false;

              if(this.lastPage < 0)
                this.lastPage = response.data.resource.last_page

              if(this.search == ''){
                this.elements = this.elements.concat(response.data.resource.data ?? []);
              }else{
                this.elements = response.data.resource.data ?? []
              }

              this.page++;

              if(!!this.input){
                let searchContinue = false;
                let self = this


                if(this.input){

                  if(this.isMultiple){
                    this.input.forEach(function(id){
                      if(!self.elements.find((o) => o[self.itemValue] == id)){
                        searchContinue = true
                        return false
                      }
                    })
                  }else {
                    __log('getItemsFromApi', this.itemValue, this.input, this.elements, this.elements.find((o) => o[this.itemValue] == this.input))
                    searchContinue = !this.elements.find((o) => o[this.itemValue] == this.input)
                  }
                }

                if(searchContinue)
                  this.getItemsFromApi()
              }
              // if(this.input.length > 0){
              //   let searchContinue = false;
              //   let self = this
              //   this.input.forEach(function(id){
              //     if(!self.elements.find((o) => o.id == id)){
              //       searchContinue = true
              //     }
              //     return
              //   })

              //   if(searchContinue)
              //     this.getItemsFromApi()
              // }
            })
        })
      }
    },
    searched(val) {

      // if( this.input.length > 0 ){
      if( !!this.input ){
        if(this.search = ''){
          this.elements = []
          this.getItemsFromApi()
        }else{
          this.search = ''
        }

        return
      }

      this.search = val
      this.page = 1
      this.lastPage = -1

      if(this.search == ''){
        this.elements = []
      }

      // if(this.input.length > 0 ){
      //   __log('searched', val, this.input)
      // }

      this.getItemsFromApi()

    },
    makeReference (key) {
      return `${key}-${states.id}`
    },

  },
  computed: {
    input: {
      get() {
        return this.modelValue ?? null
      },
      set(val, old) {
        this.updateModelValue(val)
      }
    },
    isMultiple() {
      return this.multiple ?? false
    },
    rawEndpoint() {
      return getURLWithoutQuery(this.endpoint)
    },
    defaultQueryParameters() {
      return getParameters(this.endpoint)
    },
    queryParameters() {
      let query = new URLSearchParams({
        ...this.defaultQueryParameters,
        page: this.page,
        itemsPerPage: this.itemsPerPage,
        ...(!!this.search ? {search: this.search} : {})
      });

      return query.toString()
    },
    fullUrl() {
      return `${this.rawEndpoint}?${this.queryParameters}`
    },
  },
  created () {
    this.getItemsFromApi()
  },
  watch: {

  }
}
</script>

<style lang="sass">

</style>
