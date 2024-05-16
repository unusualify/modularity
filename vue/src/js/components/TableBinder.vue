<template>
  <component :is="`ue-${componentName}`" v-bind="tableAttributes"/>
</template>

<script>
  export default {
    props: {
      componentName: {
        type: String,

      },
      tableAttributes: {
        type: Object,
        required: true,
        default: {}
      }
    },

  }



</script>
