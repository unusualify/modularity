<template>

    <v-dialog
        v-model="dialog"
        v-on="{
            toggleFullScreen: toggleFullScreen
        }"

        v-bind="bindProps()"
        :fullscreen="full"
        :width="modalWidth"
    >

        <template v-slot:activator="{ on, attrs }">
            <slot 
                name="activator"
                :attrs="{
                    ...attrs
                }"
                :on="{
                    ...on
                }"
                >
                <!-- <v-btn
                    color="primary"
                    dark
                    class="mb-2"
                    v-bind="{attrs}"
                    v-on="on"
                    >
                    {{ $tc('show') }}
                </v-btn> -->
            </slot>
        </template>

        <v-card>
            <slot
                v-if="systembar"
                name="systembar"
    
                >
                <v-system-bar
                        window
                        dark
                    >
                        <v-spacer></v-spacer>
                        
                        <v-icon 
                            @click="toggleFullScreen()" 
                            :x-small="full"
                            >
                            mdi-checkbox-blank-outline
                        </v-icon>
                        <!-- <v-icon @click="cancelModal(on.closeDialog)" >mdi-close</v-icon> -->
                        <v-icon @click="close()" >mdi-close</v-icon>
                </v-system-bar>
            </slot>
            
            <slot 
                name="body"
                :attrs="{
                }"
                :on="{
                    openDialog: this.open,
                    closeDialog: this.close,
                    confirmDialog: this.confirm
                }"
                :closeDialog="close"
                >
            
            </slot>
        </v-card>
    </v-dialog>
</template>

<script>
import htmlClasses from '@/utils/htmlClasses'

export default {
    props: {
        value: {
            type: Boolean
        },
        name: {
            type: String,
            default: "Item"
        },
        transition: {
            type: String,
            default: "bottom"
        },

        widthType: {
            type: String,
        },
        systembar: {
            type: Boolean,
            default: false
        },
        fullscreen: {
            type: Boolean,
            default: false
        }

    },
    data() {
        return {
            // dialog: this.value,
            widths: {
                sm: "300px",
                md: "500px",
                lg: "750px",
            },
            width: this.widthType,

            modalClass: htmlClasses.modal,
            firstFocusableEl: null,
            lastFocusableEl: null,
            
            full: this.fullscreen,
        }
    },

    computed: {
        dialog: {
            get () {
                return this.value
            },
            set (value) {
                __log('modal->dialog->setter', value)
                this.$emit('input', value)
            }
        },
        // full: {
        //     get () {
        //         return this.fullscreen
        //         return this.fullScreen
        //     },
        //     set (value) {
        //         // this.$emit('screenListener', this.full)
        //     }
        // },
        togglePersistent() {
            return this.persistent;
        },

        toggleScrollable() {
            return this.scrollable;
        },
        modalWidth() {
            return !!this.width ? this.widths[this.width] : null;
        },
    },

    watch: {

    },

    methods: {
        toggle() {
            this.dialog = !this.dialog
        },
        close() {
            this.dialog = false
        },
        open() {
            this.dialog = true
        },
        confirm() {
            this.dialog = false
        },

        attrs(attrs) {
            __log(attrs)
            return attrs;
        },

        toggleFullScreen() {
            __log(this.full)
            return this.full = !this.full;
        },

        screenListener(e){
            // __log(e.target);
            this.full = e.target.fullScreen
        },
    },
    beforeDestroy: function () {

    },
    created() {
        // setInterval((self) => {
        //     __log(self.dialog)
        // }, 1000, this)
    }
}
</script>

<style>

</style>