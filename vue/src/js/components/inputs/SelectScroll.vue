<template>
  <component
    :is="componentType"
    v-bind="$bindAttributes()"

    v-model="input"
    :class="['v-select-scroll']"
    :items="elements"
    :label="label"
    @update:search="searched"
    :no-filter="noFilter"
    @input.native="getItemsFromApi"
    :return-object="false"
  >
    <template v-slot:append-item>
      <div v-if="lastPage > 0 && lastPage > page" v-intersect="endIntersect" />
    </template>
  </component>
</template>

<script>
import { InputMixin } from '@/mixins' // for props
import { useInput, makeInputProps } from '@/hooks'

export default {
  name: 'v-custom-input-select-scroll',
  mixins: [InputMixin],
  props: {
    ...makeInputProps(),
    componentType: {
      type: String,
      default: 'v-autocomplete'
    },
    itemsPerPage: {
      type: Number,
      default: 20
    },
    endpoint: {
      type: String,
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
        return new Promise(() => {
          this.$axios.get(this.fullUrl)
            .then(response => {
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
                if(!self.elements.find((o) => o.id == this.input)){
                  searchContinue = true
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
    queryParameters() {
      let query = new URLSearchParams({
        page: this.page,
        itemsPerPage: this.itemsPerPage,
        ...(!!this.search ? {search: this.search} : {})
      });

      return query.toString()
    },
    fullUrl() {
      return `${this.endpoint}?${this.queryParameters}`
    },
  },
  created () {
    this.getItemsFromApi()
  },
  watch: {
    input: {
      deep: true,
      handler (newValue) {
        // console.log('watchedValue?', newValue)
      }
    },
  }
}
</script>

<style lang="sass">

</style>
