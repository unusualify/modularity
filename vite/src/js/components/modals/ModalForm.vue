<template>
    <ue-modal
        v-model="show"

        scrollable
        content-class="bg-primary"

        width-type="lg"
        systembar
        @screenListener="screenListener"
        >
        <template v-slot:activator="{on, attrs}">
            <slot
                name="activator"
                :attrs="{...attrs}"
                :on="{...on}"
                >
            </slot>
        </template>

        <template
            v-slot:body="{on, attrs}"
            v-bind="attrs"
            v-on="on"
            >

            <v-card >

                <v-card-title class="text-h5 grey lighten-2">

                    <span slot="title" class="text-h5" >
                        {{ formTitle }}
                    </span>
                </v-card-title>

                <v-card-text>
                    <!-- <ue-form :ref="formReference()"/> -->
                    <ue-form-base :ref="formReference()"/>

                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <!-- <v-btn
                        color="error darken-1"
                        text
                        @click="cancelModal(on.closeDialog)"
                        >
                        {{ $tc('cancel') }}
                    </v-btn> -->
                    <v-btn
                        color="error darken-1"
                        text
                        @click="cancelModal(on.closeDialog)"
                        >
                        {{ textCancel }}
                    </v-btn>
                    <v-btn
                        color="teal darken-1"
                        text
                        @click="confirmModal(on.closeDialog)"
                        >
                        {{ $tc('save') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </template>
    </ue-modal>
</template>

<script>
import UEForm from '__components/Form.vue';
import { ModalMixin } from '@/mixins'

export default {
    mixins: [ModalMixin],
    components: {
        'ue-form': UEForm
    },
    props: {
        routeName: {
            type: String,
            default: 'Item'
        }
    },
    data() {
        return {
            full: false
        }
    },

    computed: {
        formTitle () {
            return this.$t( ( this.editedIndex === -1 ? 'new-item' : 'edit-item'), {'item': this.routeName})
        },
        activatorText() {
            return this.$t('new-item', {'item': this.routeName} )
        },
    },

    methods: {
        screenListener(e){
            // __log(e.target);
            this.full = e.target.fullScreen
        },
        formReference(){
            return this.id + `-form`
        },

        confirmCallback(){
            let self = this;
            this.$refs[this.formReference()].saveForm((res) => {
                self.closeModal();
            })
        },

        save () {
            console.log(
                'save clicked',
                this.formObject
            );
        },
    }
}
</script>

<style>

</style>
