<template>
    <v-range-slider
        :value="value"
        :label="label"
        @change="update($event)"
        :error-messages="errorMessages(attributes.name)"

        v-bind="props"
    ></v-range-slider>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],

}
</script>