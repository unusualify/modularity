import { mapState, mapGetters } from 'vuex'

export default {
  props: {
    value: null,
    attributes: {
        type: Object,
        default() {
            return {}
        }
    }
  },
  data: function() {
    return {
      id: Math.ceil(Math.random()*1000000) + "-input",
      label: this.attributes.title
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
    ...mapState({
        errors: state => state.form.errors
    }),
    ...mapGetters([
        
    ]),

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
    configureProps(props){
      let _props = {};

      Object.keys(props).forEach( (v,i) => {
        if(parseInt(v) > -1){
          _props[props[v]] = true;
        }else{
          _props[v] = props[v];
        }
      })
      // __log(_props)
      return _props;  
    }
  }
}
