<template>
    <v-menu 
        :ref="`${id}-menu`"
        v-model="menuActive"
        :close-on-content-click="false"
        :nudge-right="40"
        :return-value.sync="value"
        transition="scale-transition"
        offset-y
        max-width="600px"
        min-width="290px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
            v-model="input"
            v-bind="{
                ...attrs,
                ...obj.schema
            }"
            v-on="on"
            readonly
          ></v-text-field>
        </template>
        <v-time-picker
          v-if="menuActive"
          v-model="input"
          @click:minute="$refs[`${id}-menu`].save(value)"
          v-bind="bindProps(obj.schema.picker_props)"
        ></v-time-picker>
    </v-menu>
</template>

<script>

import { CustomInputMixin } from '@/mixins'

export default {
    mixins: [CustomInputMixin],
    name:'ue-custom-input-color',  
    data() {
        return {
            menuActive: false,
        }
    },
    computed: {
        input:{
            get(){  return this.value},
            set(val){ this.$emit('input', val)}  // listen to @input="handler"
        }
    }
}
</script>