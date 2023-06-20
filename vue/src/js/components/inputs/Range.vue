<template>
    <v-range-slider
        v-model="input"
        :label="obj.schema.label"
        v-bind="obj.schema"
    ></v-range-slider>
</template>

<script>
import { CustomInputMixin } from '@/mixins'

export default {
    mixins: [CustomInputMixin],
    name:'ue-custom-input-range',  
    
    created() {

    },
    computed:{

    }  
}
</script>