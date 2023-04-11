export default {
    methods: {
        bindProps() {
            // __log(
            //     this.$options.name,
            //     // this.$props,
            //     this.$attrs,
            // )
            return { ...this.$attrs };
        }
    }
}
  