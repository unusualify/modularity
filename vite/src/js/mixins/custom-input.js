import { mapState, mapGetters } from 'vuex'

export default {
props: ['value', 'obj'],
  data: function() {
    return {
      id: Math.ceil(Math.random()*1000000) + "-input",
    }
  },
  created() {

  },

  watch: {
    value(val){
      // __log(
      //   'mixins/input.js value changed',
      //   val
      // )
    }
  },
  computed: {
    input:{
        get(){  return this.value},
        set(val){ this.$emit('input', val)}  // listen to @input="handler"
    },

    ...mapState({
        errors: state => state.form.errors
    }),

    props() {
      if(!!this.attributes.props){
        return this.configureProps(this.attributes.props)
      }else{
        return {}
      }
    },
    secondaryProps() {
      if(!!this.attributes.props && !!this.attributes.props.props){
        return this.configureProps(this.attributes.props.props);
      }else{
        return {}
      }
    }
  },
  methods: {
    update(value) {
      this.$emit('input', value )
    },
    errorMessages(name){
      return this.errors[name];
    },
    bindProps(props){
      let _props = {};
      if(!!props){
        Object.keys(props).forEach( (v,i) => {
          if(parseInt(v) > -1){
            _props[props[v]] = true;
          }else{
            _props[v] = props[v];
          }
        })
      }
      // __log(_props)
      return _props;  
    }
  }
}
