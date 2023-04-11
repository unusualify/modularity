<template>
    <v-text-field
        :value="value"
        @input="update($event)"
        :label="label"
        :error-messages="errorMessages(attributes.name)"
        
        dense
        v-bind="props"
    >
    </v-text-field>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],

}
</script>