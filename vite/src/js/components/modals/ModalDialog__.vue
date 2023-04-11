<template>
    <v-row justify="center">
        <v-btn
            color="primary"
            dark
            @click.stop="dialog = true"
            >
            Open Dialog
        </v-btn>
        <v-dialog
            v-model="dialog"
            :fullscreen="toggleFullScreen"
            :persistent="togglePersistent"
            :scrollable="toggleScrollable"
            >

            <v-card>
                <v-card-title class="text-h5">
                    Use Google's location service?
                </v-card-title>

                <v-card-text>
                    Let Google help apps determine location. This means sending anonymous location data to Google, even when no apps are running.
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>

                    <v-btn
                        color="green darken-1"
                        text
                        @click="dialog = false"
                    >
                        Disagree
                    </v-btn>

                    <v-btn
                        color="green darken-1"
                        text
                        @click="dialog = false"
                    >
                        Agree
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<script>
import { ModalMixin } from '@/mixins'

export default {
    
    mixins: [ModalMixin],

    props: {
        cancelText: {
            type: String,
            default: ''
        },
        confirmText: {
            type: String,
            default: ''
        },
        description: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            // open: false
            dialog: false,

        }
    },

    computed: {
        textCancel() {
            return this.cancelText != '' ? this.cancelText : this.$t('cancel') 
        },
        textConfirm() {
            return this.confirmText != '' ? this.confirmText : this.$t('confirm') 
        },
        textDescription: {
            get () {
                return this.description != '' ? this.description : this.$t('confirm-description') 
            },
            set (value) {
                this.$emit('input', value)
            }
        }
    },

    watch: {

    },

    methods: {

        cancelModal(callback){
            __log('parent.cancelModal')

            if (callback && typeof callback === 'function') {
                callback()
            }

            this.$emit('cancel')
        },
        confirmModal(callback){
            if (callback && typeof callback === 'function') {
                callback()
            }

            this.$emit('confirm');
        }
    }
}
</script>

<style>

</style>