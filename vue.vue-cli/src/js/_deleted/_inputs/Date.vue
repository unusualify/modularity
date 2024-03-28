<template>
    <v-menu 
        v-model="menuActive"
        :close-on-content-click="false"
        :nudge-right="40"
        transition="scale-transition"
        offset-y
        max-width="290px"
        min-width="290px"

        >
        <template v-slot:activator="{ on }">
            <v-text-field
                    v-on="on"
                    :label="label"
                    :value="value"
                    @input="update($event)"
                    readonly
                    :error-messages="errorMessages(attributes.name)"
                    
                    v-bind="props"
                    
            ></v-text-field>
        </template>
    
        <v-date-picker 
            @input="menuActive = false"
            @change="update($event)"
            :value="value"
            :label="label"
            :locale="$i18n.locale"

            v-bind="pickerProps"
            >
        </v-date-picker>

    </v-menu>  
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],
    data() {
        return {
            menuActive: false,
        }
    },
    computed: {
        pickerProps() {
            if(!!this.attributes.picker_props){
                return this.configureProps(this.attributes.picker_props);
            }else{
                return {}
            }
        }
    }
}
</script>