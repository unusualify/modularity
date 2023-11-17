<template>
    <v-file-input
        :value="value"
        @change="update($event)"
        :label="label"
        :error-messages="errorMessages(attributes.name)"
        v-bind="props"

        small-chips
        outlined
        dense
    >
    </v-file-input>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],
    props: {
        value: []
    },
    // created(){
    //     __log(typeof this.value)
    // }
}
</script>