<template>
    <v-switch
        :value="value"
        @change="update($event)"
        :label="`${label}: ${value.toString()}`"
        :error-messages="errorMessages(attributes.name)"
        
        v-bind="props"

    ></v-switch>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],

}
</script>