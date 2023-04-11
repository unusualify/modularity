
<template>
    <v-checkbox
        :input-value="value"
        :label="label"
        @change="update($event)"
        :error-messages="errorMessages(attributes.name)"
        
        v-bind="props"


    ></v-checkbox>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],

}
</script>