<template>
    <v-radio-group 
        :value="value"
        @change="update($event)"
        :error-messages="errorMessages(attributes.name)"
        
        v-bind="props"
    >
        <v-radio
            v-for="(option,n) in attributes.options"
            :value="option.value"
            :key="n"
            :label="option.label"
            :id="`${id}-radio-option${n}`"

            v-bind="secondaryProps"
        ></v-radio>
    </v-radio-group>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],

    props: {
        // value: {
        //     type: Number,
        //     default: 0,
        // },
    }
}
</script>