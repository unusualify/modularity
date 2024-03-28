<template>
    <v-text-field 
        :value="value"
        :label="label"
        v-mask="mask"
        hide-details 
        class="ma-0 pa-0" 
        @input="update( $event )"
        v-bind="props"

        @click="pickColor=true"
        readonly
        dense
        outlined
    >

        <template v-slot:append>
            <v-menu 
                v-model="pickColor" 
                top 
                nudge-bottom="105" 
                nudge-left="16" 
                :close-on-content-click="false"
            >

                <template v-slot:activator="{ on }">
                    <div :style="swatchStyle(value)" v-on="on" />
                </template>
                <v-card>
                    <v-card-text class="pa-0">
                        <v-color-picker 
                            flat 
                            :value="value"
                            :label="label"
                            @input="update($event)"
                            v-bind="secondaryProps"
                        />
                    </v-card-text>
                </v-card>
            </v-menu>
        </template>
    </v-text-field>
</template>

<script>
import { InputMixin } from '@/mixins'

export default {
    mixins: [InputMixin],
    data() {
        return {
            pickColor: false,
            mask: '!XNNNNNNNN',
        }
    },

    methods: {
        swatchStyle(color){
            const { pickColor } = this
            return {
                backgroundColor: color,
                cursor: 'pointer',
                height: '30px',
                width: '30px',
                borderRadius: pickColor ? '50%' : '4px',
                transition: 'border-radius 200ms ease-in-out',
                marginTop: '-3px',
            }
        },
    }
}
</script>