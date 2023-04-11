
<template>

    <v-select
        :value="value"
        @change="update($event)"
        :label="label"
        :items="attributes.items"
        :error-messages="errorMessages(attributes.name)"
        
        v-bind="props"

    ></v-select>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],

}
</script>