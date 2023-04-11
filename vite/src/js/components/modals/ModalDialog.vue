<template>
    <ue-modal
        v-model="show"
        v-bind="bindProps()"
        width-type="md"
        >
        <template 
            v-slot:body="{attrs, on}"
            v-bind="attrs"
            v-on="on"
            >
            <slot 
                name="body"
                >
                <v-card >
                    <v-card-title 
                        class="text-h5 text-center" 
                        style="word-break: break-word;"
                        >
                        {{ textDescription }}
                    </v-card-title>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue" text @click="cancelModal(on.closeDialog)">
                            {{ textCancel }}
                        </v-btn>
                        <v-btn color="blue" text @click="confirmModal(on.closeDialog)">
                            {{ textConfirm }}
                        </v-btn>
                        <v-spacer></v-spacer>
                    </v-card-actions>
                </v-card>
            </slot>
        </template>
    </ue-modal>
</template>

<script>

import { ModalMixin } from '@/mixins'

export default {
    mixins: [ModalMixin],
    props: {
        description: {
            type: String,
            default: 'Bu işlemi yapmak istediğinize emin misiniz?'
        }
    },
    data() {
        return {

        }
    },

    computed: {
        textDescription: {
            get () {
                return this.description != '' ? this.description : this.$t('confirm-description') 
            },
            set (value) {
                this.$emit('input', value)
            }
        }
    },

    methods: {
        // cancelCallback(){},
        confirmCallback(){},
    }
}
</script>

<style>

</style>